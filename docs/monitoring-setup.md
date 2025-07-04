# Monitoring Setup

This document covers the monitoring infrastructure using Prometheus, Grafana, and custom metrics for the GlasgowPHP CQRS application.

## 📊 Monitoring Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Application   │    │   Prometheus    │    │     Grafana     │
│   (Metrics)     │───▶│   (Collector)   │───▶│  (Dashboard)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  PHP-FPM Exporter│    │  Custom Metrics │    │  Opcache Dash   │
│  (Port 9253)    │    │  (Port 8088)    │    │  (Port 42042)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🎯 Prometheus Configuration

### Configuration File (`prometheus/prometheus.yml`)

```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  # - "first_rules.yml"
  # - "second_rules.yml"

scrape_configs:
  # Prometheus itself
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  # PHP-FPM Exporter
  - job_name: 'php-fpm'
    static_configs:
      - targets: ['php-fpm-exporter:9253']
    scrape_interval: 10s

  # FrankenPHP Metrics
  - job_name: 'franken'
    static_configs:
      - targets: ['franken:2019']
    metrics_path: '/metrics'
    scrape_interval: 10s

  # Franken Worker Metrics
  - job_name: 'franken-worker'
    static_configs:
      - targets: ['franken-worker:2019']
    metrics_path: '/metrics'
    scrape_interval: 10s

  # Custom Application Metrics
  - job_name: 'app-metrics'
    static_configs:
      - targets: ['app:80']
    metrics_path: '/metrics'
    scrape_interval: 10s
```

### Key Metrics Collected

1. **PHP-FPM Metrics**
   - Active processes
   - Idle processes
   - Max children reached
   - Slow requests
   - Request duration

2. **FrankenPHP Metrics**
   - Request rate
   - Response time
   - Memory usage
   - Goroutine count

3. **Custom Application Metrics**
   - OPcache statistics
   - Redis connection status
   - Database query performance
   - CQRS command/query counts

## 📈 Grafana Dashboards

### Dashboard Configuration (`grafana/provisioning/dashboards/dashboard.yml`)

```yaml
apiVersion: 1

providers:
  - name: 'default'
    orgId: 1
    folder: ''
    type: file
    disableDeletion: false
    updateIntervalSeconds: 10
    allowUiUpdates: true
    options:
      path: /etc/grafana/provisioning/dashboards
```

### Available Dashboards

1. **PHP-FPM Dashboard** (`grafana/provisioning/dashboards/fpm-dashboard.json`)
   - Process pool status
   - Request rate and duration
   - Memory usage
   - Queue length

2. **Caddy Dashboard** (`grafana/provisioning/dashboards/caddy-dashboard.json`)
   - HTTP request metrics
   - Response codes
   - Bandwidth usage
   - TLS certificate status

3. **Custom Application Dashboard**
   - OPcache performance
   - Redis operations
   - Database performance
   - CQRS metrics

### Dashboard Features

- **Real-time Updates**: 10-second refresh intervals
- **Color-coded Alerts**: Green/Yellow/Red status indicators
- **Performance Trends**: Historical data visualization
- **Custom Queries**: PromQL for advanced metrics

## 🔍 Metrics Endpoints

### PHP-FPM Status (`/fpm-status`)

```bash
# Access FPM status
curl http://localhost:8088/fpm-status

# Example response:
pool:                 www
process manager:      dynamic
start time:           01/Jan/2024:12:00:00 +0000
start since:          3600
accepted conn:        1234
listen queue:         0
max listen queue:     0
listen queue len:     0
idle processes:       5
active processes:     1
total processes:      6
max active processes: 10
max children reached: 0
slow requests:        0
```

### Custom Metrics (`/metrics`)

```bash
# Access application metrics
curl http://localhost:8088/metrics

# Example metrics:
# HELP php_opcache_enabled OPcache enabled status
# TYPE php_opcache_enabled gauge
php_opcache_enabled 1

# HELP php_opcache_memory_usage_bytes OPcache memory usage in bytes
# TYPE php_opcache_memory_usage_bytes gauge
php_opcache_memory_usage_bytes 67108864

# HELP php_opcache_hit_rate OPcache hit rate percentage
# TYPE php_opcache_hit_rate gauge
php_opcache_hit_rate 99.73

# HELP redis_connected Redis connection status
# TYPE redis_connected gauge
redis_connected 1

# HELP cqrs_commands_total Total number of CQRS commands processed
# TYPE cqrs_commands_total counter
cqrs_commands_total{command="AddProduct"} 123
cqrs_commands_total{command="UpdateProduct"} 45
```

### FrankenPHP Metrics

```bash
# FrankenPHP metrics
curl https://localhost:2019/metrics

# Franken Worker metrics
curl https://localhost:2020/metrics

# Example metrics:
# HELP caddy_http_requests_total Counter of HTTP requests made
# TYPE caddy_http_requests_total counter
caddy_http_requests_total{handler="php",method="GET",server="localhost"} 1234

# HELP caddy_http_request_duration_seconds Histogram of HTTP request durations
# TYPE caddy_http_request_duration_seconds histogram
caddy_http_request_duration_seconds_bucket{handler="php",le="0.1"} 1000
caddy_http_request_duration_seconds_bucket{handler="php",le="0.5"} 1200
```

## 🚨 Alerting Configuration

### Prometheus Alert Rules

```yaml
groups:
  - name: php-fpm
    rules:
      - alert: PHPFPMHighQueue
        expr: php_fpm_processes_listen_queue > 10
        for: 1m
        labels:
          severity: warning
        annotations:
          summary: "PHP-FPM queue is high"
          description: "Queue length is {{ $value }}"

      - alert: PHPFPMHighMemory
        expr: php_fpm_processes_memory_usage_bytes > 100000000
        for: 1m
        labels:
          severity: warning
        annotations:
          summary: "PHP-FPM memory usage is high"
          description: "Memory usage is {{ $value }} bytes"

  - name: opcache
    rules:
      - alert: OPcacheLowHitRate
        expr: php_opcache_hit_rate < 90
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "OPcache hit rate is low"
          description: "Hit rate is {{ $value }}%"
```

### Grafana Alerting

Configure alerts in Grafana dashboards:

1. **Low OPcache Hit Rate**
   - Condition: `php_opcache_hit_rate < 90`
   - Duration: 5 minutes
   - Notification: Email/Slack

2. **High Memory Usage**
   - Condition: `php_opcache_memory_usage_bytes > 200MB`
   - Duration: 1 minute
   - Notification: Email/Slack

3. **Redis Connection Issues**
   - Condition: `redis_connected == 0`
   - Duration: 30 seconds
   - Notification: Email/Slack

## 📊 Performance Monitoring

### Key Performance Indicators (KPIs)

1. **Response Time**
   ```promql
   # Average response time
   rate(caddy_http_request_duration_seconds_sum[5m]) / rate(caddy_http_request_duration_seconds_count[5m])
   ```

2. **Request Rate**
   ```promql
   # Requests per second
   rate(caddy_http_requests_total[5m])
   ```

3. **Error Rate**
   ```promql
   # Error percentage
   rate(caddy_http_requests_total{status_code=~"5.."}[5m]) / rate(caddy_http_requests_total[5m]) * 100
   ```

4. **OPcache Performance**
   ```promql
   # Hit rate
   php_opcache_hit_rate
   
   # Memory usage
   php_opcache_memory_usage_bytes
   ```

### Custom Queries

1. **CQRS Performance**
   ```promql
   # Commands per second
   rate(cqrs_commands_total[5m])
   
   # Average command processing time
   rate(cqrs_command_duration_seconds_sum[5m]) / rate(cqrs_command_duration_seconds_count[5m])
   ```

2. **Redis Performance**
   ```promql
   # Redis operations per second
   rate(redis_operations_total[5m])
   
   # Redis memory usage
   redis_memory_usage_bytes
   ```

## 🛠️ Setup and Configuration

### Starting Monitoring Stack

```bash
# Start all monitoring services
make up-grafana

# Start individual services
make up-exporter
make up-opcache-dashboard
```

### Accessing Dashboards

1. **Grafana**: http://localhost:3000 (admin/admin)
2. **Prometheus**: http://localhost:9090
3. **Opcache Dashboard**: http://localhost:42042

### Configuration Files

- **Prometheus**: `prometheus/prometheus.yml`
- **Grafana Dashboards**: `grafana/provisioning/dashboards/`
- **Grafana Datasources**: `grafana/provisioning/datasources/`

## 🔧 Troubleshooting

### Common Issues

1. **Prometheus Not Scraping**
   ```bash
   # Check targets
   curl http://localhost:9090/api/v1/targets
   
   # Check configuration
   curl http://localhost:9090/api/v1/status/config
   ```

2. **Grafana No Data**
   ```bash
   # Check datasource
   curl http://localhost:3000/api/datasources
   
   # Test Prometheus connection
   curl http://localhost:3000/api/datasources/proxy/1/api/v1/query?query=up
   ```

3. **Metrics Not Available**
   ```bash
   # Check application metrics
   curl http://localhost:8088/metrics
   
   # Check FPM exporter
   curl http://localhost:9253/metrics
   ```

### Performance Tuning

1. **Scrape Intervals**
   ```yaml
   # Reduce for more frequent updates
   scrape_interval: 5s
   
   # Increase for better performance
   scrape_interval: 30s
   ```

2. **Retention Period**
   ```yaml
   # Keep data longer
   global:
     scrape_interval: 15s
     evaluation_interval: 15s
   storage:
     tsdb:
       retention.time: 30d
   ```

3. **Resource Limits**
   ```yaml
   # Add to docker-compose.yml
   prometheus:
     deploy:
       resources:
         limits:
           memory: 2G
           cpus: '1.0'
   ```

## 📚 Best Practices

### Monitoring Strategy

1. **Start Simple**: Begin with basic metrics (CPU, memory, response time)
2. **Add Business Metrics**: Include CQRS-specific metrics
3. **Set Up Alerts**: Configure meaningful alert thresholds
4. **Regular Reviews**: Review and adjust metrics monthly

### Performance Considerations

1. **Scrape Frequency**: Balance detail vs. performance
2. **Retention**: Keep data long enough for trend analysis
3. **Resource Usage**: Monitor Prometheus/Grafana resource consumption
4. **Network Impact**: Consider network overhead of metrics collection

### Security

1. **Access Control**: Restrict access to monitoring endpoints
2. **Authentication**: Use proper authentication for Grafana
3. **Network Isolation**: Keep monitoring traffic separate
4. **Data Privacy**: Ensure no sensitive data in metrics

## 🔗 Related Documentation

- [Docker Compose Configuration](docker-compose.md)
- [PHP Configuration](php-configuration.md)
- [Performance Testing](performance-testing.md) 