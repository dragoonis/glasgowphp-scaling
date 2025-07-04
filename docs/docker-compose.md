# Docker Compose Configuration

This document explains the complete Docker Compose setup for the GlasgowPHP CQRS application.

## 🏗️ Architecture Overview

The application runs multiple services to provide a complete development and monitoring environment:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   PHP-FPM App   │    │   FrankenPHP    │    │  Franken Worker │
│   (Port 8088)   │    │   (Port 443)    │    │  (Port 444)     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │        Redis            │
                    │      (Port 6379)        │
                    └────────────┬────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
┌────────▼────────┐    ┌────────▼────────┐    ┌────────▼────────┐
│   Prometheus    │    │     Grafana     │    │  Opcache Dash   │
│   (Port 9090)   │    │   (Port 3000)   │    │  (Port 42042)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🐳 Services Configuration

### 1. PHP-FPM Application (`app`)

**Purpose**: Main Symfony application running on traditional PHP-FPM

```yaml
app:
  build:
    context: .
    dockerfile: ./docker/Dockerfile
  tty: true
  ports:
    - "8088:80"  # HTTP access
  volumes:
    - .:/var/www/html  # Mount source code
    - ./docker/symfony.ini:/usr/local/etc/php/conf.d/symfony.prod.ini  # PHP config
  networks:
    - app-net
```

**Key Features**:
- **Port**: 8088 (HTTP)
- **Runtime**: PHP-FPM with Nginx
- **Development**: Hot-reload with volume mounting
- **Configuration**: Custom PHP settings via symfony.ini

**Usage**:
```bash
# Start only FPM
make up

# Access application
curl http://localhost:8088/en/products
```

### 2. FrankenPHP (`franken`)

**Purpose**: Modern Go-based PHP runtime for better performance

```yaml
franken:
  build:
    context: .
    dockerfile: ./docker/Dockerfile.franken
  ports:
    - "443:443"      # HTTPS access
    - "443:443/udp"  # QUIC support
    - "2019:2019"    # Admin API
  volumes:
    - .:/var/www/html
    - ./docker/Caddyfile.regular:/etc/caddy/Caddyfile
    - ./docker/symfony.ini:/usr/local/etc/php/conf.d/symfony.prod.ini
  environment:
    CADDY_GLOBAL_OPTIONS: "admin 0.0.0.0:2019\nmetrics"
    SERVER_NAME: ":8080 https://localhost:443"
  networks:
    - app-net
```

**Key Features**:
- **Port**: 443 (HTTPS)
- **Runtime**: FrankenPHP (Go-based)
- **Features**: HTTP/3, automatic HTTPS, metrics
- **Admin API**: Port 2019 for configuration

**Usage**:
```bash
# Start FrankenPHP
make up-franken

# Access application
curl -k https://localhost:443/en/products

# View metrics
curl https://localhost:2019/metrics
```

### 3. FrankenPHP Worker (`franken-worker`)

**Purpose**: FrankenPHP in worker mode for high-performance scenarios

```yaml
franken-worker:
  build:
    context: .
    dockerfile: ./docker/Dockerfile.franken
  ports:
    - "444:443"      # HTTPS access
    - "444:443/udp"  # QUIC support
    - "2020:2019"    # Admin API
  volumes:
    - .:/var/www/html
    - caddy_data:/data
    - caddy_config:/config
    - ./docker/Caddyfile:/etc/caddy/Caddyfile
    - ./docker/symfony.ini:/usr/local/etc/php/conf.d/symfony.prod.ini
  environment:
    CADDY_GLOBAL_OPTIONS: "admin 0.0.0.0:2019\nmetrics"
    FRANKENPHP_CONFIG: "worker ./public/index.php"
    APP_RUNTIME: "Runtime\\FrankenPhpSymfony\\Runtime"
    SERVER_NAME: ":8080 https://localhost:443"
  networks:
    - app-net
```

**Key Features**:
- **Port**: 444 (HTTPS)
- **Mode**: Worker mode for better concurrency
- **Runtime**: FrankenPHP with Symfony Runtime
- **Persistence**: Caddy data and config volumes

**Usage**:
```bash
# Start Franken Worker
make up-worker

# Access application
curl -k https://localhost:444/en/products

# View metrics
curl https://localhost:2020/metrics
```

### 4. Redis Cache (`redis`)

**Purpose**: In-memory data store for projections and caching

```yaml
redis:
  image: redis:7-alpine
  ports:
    - "6379:6379"
  restart: unless-stopped
  networks:
    - app-net
```

**Key Features**:
- **Port**: 6379
- **Version**: Redis 7 Alpine (lightweight)
- **Persistence**: Automatic restart policy
- **Usage**: Projections, session storage, caching

**Usage**:
```bash
# Access Redis CLI
docker-compose exec redis redis-cli

# Check keys
redis-cli keys "*"

# Monitor operations
redis-cli monitor
```

### 5. Prometheus (`prometheus`)

**Purpose**: Metrics collection and storage

```yaml
prometheus:
  image: prom/prometheus:v2.53.4
  container_name: prometheus
  volumes:
    - ./prometheus/prometheus.yml:/etc/prometheus/prometheus.yml:ro
  command:
    - "--config.file=/etc/prometheus/prometheus.yml"
  ports:
    - "9090:9090"
  networks:
    - app-net
```

**Key Features**:
- **Port**: 9090
- **Version**: Prometheus 2.53.4
- **Configuration**: External prometheus.yml
- **Targets**: PHP-FPM, FrankenPHP, custom metrics

**Usage**:
```bash
# Start monitoring stack
make up-grafana

# Access Prometheus
open http://localhost:9090

# View targets
curl http://localhost:9090/api/v1/targets
```

### 6. Grafana (`grafana`)

**Purpose**: Metrics visualization and dashboards

```yaml
grafana:
  image: grafana/grafana:latest
  container_name: grafana
  depends_on:
    - prometheus
  ports:
    - "3000:3000"
  environment:
    GF_SECURITY_ADMIN_USER: admin
    GF_SECURITY_ADMIN_PASSWORD: admin
  volumes:
    - ./grafana/provisioning:/etc/grafana/provisioning:ro
  networks:
    - app-net
```

**Key Features**:
- **Port**: 3000
- **Credentials**: admin/admin
- **Provisioning**: Auto-configured dashboards
- **Data Source**: Prometheus integration

**Usage**:
```bash
# Access Grafana
open http://localhost:3000

# Login: admin/admin
# View PHP-FPM dashboard
# Monitor application performance
```

### 7. PHP-FPM Exporter (`php-fpm-exporter`)

**Purpose**: Exports PHP-FPM metrics to Prometheus

```yaml
php-fpm-exporter:
  image: hipages/php-fpm_exporter:latest
  depends_on:
    - app
  environment:
    PHP_FPM_SCRAPE_URI: "tcp://app:9000/fpm-status"
  ports:
    - "9253:9253"
  networks:
    - app-net
```

**Key Features**:
- **Port**: 9253
- **Target**: PHP-FPM status endpoint
- **Metrics**: Process count, queue length, request rate
- **Integration**: Prometheus scraping

**Usage**:
```bash
# View PHP-FPM metrics
curl http://localhost:9253/metrics

# Check FPM status
curl http://localhost:8088/fpm-status
```

### 8. Opcache Dashboard (`opcache-dashboard`)

**Purpose**: Real-time PHP Opcache monitoring

```yaml
opcache-dashboard:
  build:
    context: ./opcache
    dockerfile: ./Dockerfile
    args:
      VERSION: 0.6.0
  ports:
    - "42042:42042"
  volumes:
    - ./opcache/config.yaml:/config.yaml:ro
  command:
    - --config=/config.yaml
  networks:
    - app-net
```

**Key Features**:
- **Port**: 42042
- **Version**: 0.6.0
- **Configuration**: External config.yaml
- **Monitoring**: Opcache hit ratio, memory usage

**Usage**:
```bash
# Start Opcache dashboard
make up-opcache-dashboard

# Access dashboard
open http://localhost:42042

# View Opcache status
curl http://localhost:8088/metrics/opcache/status
```

## 🔧 Volumes

### Persistent Data

```yaml
volumes:
  caddy_data:    # FrankenPHP certificate storage
  caddy_config:  # FrankenPHP configuration
```

### Volume Mounts

- **Source Code**: `.:/var/www/html` - Development hot-reload
- **PHP Config**: `./docker/symfony.ini:/usr/local/etc/php/conf.d/symfony.prod.ini`
- **Caddy Config**: `./docker/Caddyfile:/etc/caddy/Caddyfile`
- **Prometheus Config**: `./prometheus/prometheus.yml:/etc/prometheus/prometheus.yml:ro`
- **Grafana Provisioning**: `./grafana/provisioning:/etc/grafana/provisioning:ro`
- **Opcache Config**: `./opcache/config.yaml:/config.yaml:ro`

## 🌐 Networks

```yaml
networks:
  app-net:
    driver: bridge
```

**Features**:
- **Isolation**: All services on dedicated network
- **Communication**: Internal service discovery
- **Security**: No external network exposure
- **Performance**: Optimized inter-service communication

## 🚀 Environment Variables

### FrankenPHP Configuration

```bash
CADDY_GLOBAL_OPTIONS="admin 0.0.0.0:2019\nmetrics"
SERVER_NAME=":8080 https://localhost:443"
FRANKENPHP_CONFIG="worker ./public/index.php"
APP_RUNTIME="Runtime\\FrankenPhpSymfony\\Runtime"
```

### PHP-FPM Exporter

```bash
PHP_FPM_SCRAPE_URI="tcp://app:9000/fpm-status"
```

### Grafana

```bash
GF_SECURITY_ADMIN_USER=admin
GF_SECURITY_ADMIN_PASSWORD=admin
```

## 📊 Service Dependencies

```
grafana → prometheus
php-fpm-exporter → app
franken-worker → redis (implicit)
```

## 🔍 Troubleshooting

### Common Issues

1. **Port Conflicts**
   ```bash
   # Check port usage
   lsof -i :8088
   lsof -i :443
   lsof -i :3000
   ```

2. **Service Health**
   ```bash
   # Check all services
   docker-compose ps
   
   # View logs
   docker-compose logs app
   docker-compose logs franken
   ```

3. **Network Issues**
   ```bash
   # Test inter-service communication
   docker-compose exec app ping redis
   docker-compose exec app ping prometheus
   ```

4. **Configuration Issues**
   ```bash
   # Validate compose file
   docker-compose config
   
   # Rebuild services
   docker-compose build --no-cache
   ```

### Performance Tuning

1. **Resource Limits**
   ```yaml
   # Add to services for production
   deploy:
     resources:
       limits:
         memory: 512M
         cpus: '0.5'
   ```

2. **Volume Optimization**
   ```yaml
   # Use delegated mode for better performance
   volumes:
     - .:/var/www/html:delegated
   ```

3. **Network Optimization**
   ```yaml
   # Use host networking for maximum performance
   network_mode: host
   ```

## 📚 Related Documentation

- [PHP Configuration](php-configuration.md)
- [FrankenPHP Setup](frankenphp-setup.md)
- [Monitoring Setup](monitoring-setup.md)
- [Performance Testing](performance-testing.md) 