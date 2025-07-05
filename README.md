# GlasgowPHP Scaling Demo

This project demonstrates a scalable Symfony CQRS application with Redis and DB projections, Docker Compose, and k6 load
testing. All common tasks are managed via the Makefile.

## Quick Start

1. **Build and start all services:**
   ```bash
   make up

   ```

2. **Set up the database and seed data:**
   ```bash
   make migrate
   make seed
   ```

### opcache introduction

https://www.php.net/manual/en/opcache.configuration.php#:~:text=on%20all%20architectures.-,opcache.max_accelerated_files,-int


```
find . -type f -name "*.php" | wc -l

opcache.max_accelerated_files=16087

```

### show fpm and opcache dashboard GUI

show fpm status page - http://localhost:8088/fpm-status

fpm.conf - `pm.status_path = /fpm-status`
aa-nginx.conf - `location ~ ^/fpm-status$ {`

make up-exporter
make ps | grep exporter

go to http://localhost:9253/metrics

make up-prometheus
make ps | grep prom

go to http://localhost:9090/targets?search=

make up-grafana
make ps | grep grafana

# show target prom sources

http://localhost:9090/targets?search=

# view grafana dashboard

open http://localhost:3000
username: croatia
password: croatia

4.2 show grafana fpm/opcache dashboard

make k6-fpm-products-db
see k6/report-UTC-xxxxxxx.html
i.e: k6/report-products-db-2025-07-04T16-59-09.331Z.html

check grafana output - http://localhost:3000

see fpm active processes. change to 1m (on left side)
http://localhost:9090/graph?g0.expr=phpfpm_active_processes&g0.tab=0&g0.display_mode=lines&g0.show_exemplars=0&g0.range_input=1m

**Normal Data Fetch (mysql)**

show FPM file
show fpm metrics

- http://localhost:8088/fpm-status
- show FPM calculation logic (in slides) - todo add graphic to this readme.
- show FPM calculator website - https://spot13.com/pmcalculator/
- find special command to calculate "Stuff", i think cpu thread count, and stuff, - it's inside slides and I think on
- matheus blog

### run this inside the container, during k6

```bash
ps --no-headers -o "rss,cmd" -C php-fpm | awk '{ sum+=$1} END { print sum/NR/1024 }'
```

```
   /products/db
   /customers/db
```

**Rebuild projections (Redis):**

   ```bash
   make rebuild-projections
   /products/projection
   /customers/projection
   ```

PHP-FPM uses a process manager to handle incoming requests efficiently. The configuration directly affects what you see
in system monitoring tools like `htop`.

**Key Configuration Settings:**

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

**Important:** `pm.start_servers = 5` means you will see **5 child processes** in `htop` when the container starts, plus
1 master process (total 6 PHP-FPM processes).

### Process Hierarchy in htop

When you run `htop` or `ps aux | grep php-fpm`, you'll see:

```
1 × php-fpm: master process
5 × php-fpm: pool www (child processes)
```

**Process Behavior:**

- **Master Process**: Manages child processes, doesn't handle requests
- **Child Processes**: Handle actual HTTP requests
- **Dynamic Scaling**: Child processes spawn/die based on load (between min_spare_servers and max_spare_servers)

### Monitoring PHP-FPM Processes

**View current processes in container:**

```bash
# Inside container
ps aux | grep php-fpm

# Or count active processes
ps --no-headers -o "rss,cmd" -C php-fpm | wc -l
```

**Calculate average memory usage per process:**

```bash
# Run this inside the container during k6 testing
ps --no-headers -o "rss,cmd" -C php-fpm | awk '{ sum+=$1} END { print sum/NR/1024 }'
```

### Composer Autoload Optimization

For optimal performance, this project uses Composer autoload optimizations configured in `composer.json`:

```json
{
  "config": {
    "optimize-autoloader": true,
    "classmap-authoritative": true
  }
}
```

**Performance Impact:**

- `optimize-autoloader`: ~10-15% faster autoloading (converts PSR-0/PSR-4 to classmap)
- `apcu-autoloader`: ~50-70% faster (requires APCu extension)
- `classmap-authoritative`: Set to `false` for development, `true` for production only

**Reference:** See the
official [Symfony Performance Documentation](https://symfony.com/doc/current/performance.html#optimize-composer-autoloader)
for detailed autoloader optimization guidelines and best practices.

### Opcache Configuration

// ./docker/symfony.prod.ini

```
opcache.validate_timestamps=0
```

Purpose: Controls whether OPcache checks if PHP files have been modified since they were cached.
How It Works:

With opcache.validate_timestamps=1 (default):

1. PHP file requested
2. OPcache checks: "Has this file been modified since I cached it?"
3. If modified → Recompile and cache new version
4. If unchanged → Use cached bytecode
5. Serve request

With opcache.validate_timestamps=0:

1. PHP file requested
2. OPcache: "I have this cached, use it" (no timestamp check)
3. Serve request immediately

## Web Interfaces & Dashboards

| Service               | URL                           | Description                           |
|-----------------------|-------------------------------|---------------------------------------|
| FPM App               | http://localhost:8088         | Main Symfony app (FPM)                |
| Franken               | https://localhost:443         | FrankenPHP (HTTPS, alt runtime)       |
| Franken Worker        | https://localhost:444         | FrankenPHP Worker (HTTPS)             |
| Grafana               | http://localhost:3000         | Metrics dashboard (admin/admin)       |
| Prometheus            | http://localhost:9090         | Prometheus metrics                    |
| Opcache Dashboard     | http://localhost:42042        | PHP Opcache dashboard                 |
| Opcache Metrics (FPM) | http://localhost:8088/metrics | PHP Opcache metrics via FPM app       |
| Franken Metrics       | http://localhost:2019/metrics | Caddy/FrankenPHP metrics (non-worker) |
| Worker Metrics        | http://localhost:2020/metrics | Caddy/FrankenPHP metrics (worker)     |

## Grafana Dashboard

A detailed PHP-FPM and OPcache monitoring dashboard is available in Grafana. It includes:

- PHP-FPM health, queue, and process metrics
- Request rate, duration, and memory usage
- OPcache hit ratio, memory, and script cache stats
- JIT and interned strings monitoring
- Alerts and color-coded panels for quick health checks

**See [`grafana-dashboard.md`](grafana-dashboard.md) for a full description of all panels and dashboard features.**

## OPcache Configuration

This project uses different OPcache configurations for development and production:

## FrankenPHP Auto-Reload (File Watching)

FrankenPHP and franken-worker can automatically reload PHP workers when your code changes, thanks to the `watch` directive in the Caddyfile:

```caddyfile
frankenphp {
    worker {
        file ./public/index.php
        watch
    }
    php_ini memory_limit 512M
}
```

### How It Works
- The `watch` directive monitors your PHP source files for changes (saves, creates, deletes)
- When changes are detected, workers gracefully restart to load the new code
- No manual intervention or container restarts needed

### Watch Specific Paths
You can watch specific directories instead of all files:

```caddyfile
frankenphp {
    worker {
        file ./public/index.php
        watch ./app        # Watch app directory
        watch ./config     # Watch config directory
        watch ./routes     # Watch routes directory
    }
}
```

### Benefits
- **Development Speed**: See changes instantly without restarting containers
- **Zero Downtime**: Workers reload gracefully, maintaining active connections
- **Selective Watching**: Monitor only relevant directories to reduce overhead

### Important Considerations
- **Performance Impact**: File watching uses system resources (inotify on Linux)
- **Production Warning**: Always disable `watch` in production for stability
- **Large Projects**: Watching many files can impact performance

**Production Best Practice:**
```caddyfile
frankenphp {
    worker {
        file ./public/index.php
        # watch  # Commented out for production
        num 8   # Increase workers instead
    }
}
```

### Worker Metrics
When `metrics` is enabled, worker information is exposed at the metrics endpoint:

```
# With num 1
frankenphp_total_workers{worker="/var/www/html/public/index.php"} 1

# With num 8
frankenphp_total_workers{worker="/var/www/html/public/index.php"} 8
```

- Access metrics at `http://localhost:2019/metrics` (or your configured admin port)
- The `frankenphp_total_workers` metric shows active workers per script
- Worker path reflects the `file` directive in your configuration
- Updates in real-time when workers are added/removed

## Caddy Configuration

Caddy configuration can be extended via environment variables in the docker-compose.yml:

```yaml
environment:
  CADDY_GLOBAL_OPTIONS: "admin 0.0.0.0:2019\nmetrics"
  SERVER_NAME: ":8080 https://localhost:443"
```

### Environment Variables Explained

#### `CADDY_GLOBAL_OPTIONS`
Injects global Caddy directives at the top of your configuration:
- `admin 0.0.0.0:2019` - Enables admin API on all interfaces
    - Access metrics at `http://localhost:2019/metrics`
    - Runtime config changes via API
    - **Security**: Use `admin localhost:2019` in production
- `metrics` - Enables Prometheus-compatible metrics endpoint
- Use `\n` for line breaks between multiple directives

#### `SERVER_NAME`
Defines which addresses/domains Caddy will serve:
- `:8080` - HTTP on port 8080 (all interfaces)
- `https://localhost:443` - HTTPS on localhost
- Can specify multiple: `example.com www.example.com`
- Caddy auto-provisions SSL certificates for domains

### Advanced Examples

#### Development Configuration
```caddyfile
{
    admin localhost:2019
    metrics
    debug
}

localhost:8080 {
    root public/
    encode gzip
    php_server
}
```

#### Production Configuration
```caddyfile
{
    admin off  # Disable admin API
}

example.com www.example.com {
    root /var/www/html
    encode zstd br gzip
    
    header {
        X-Frame-Options DENY
        X-Content-Type-Options nosniff
    }
    
    handle_errors {
        rewrite * /error.php
        php_server
    }
    
    php_server
}
```

#### Multi-site Configuration
```caddyfile
site1.com {
    root /var/www/site1/public
    php_server
}

site2.com {
    root /var/www/site2/public
    php_server
}

:8080 {
    root /var/www/default/public
    php_server
}
```

This approach keeps your Caddyfile clean while allowing per-deployment customization.