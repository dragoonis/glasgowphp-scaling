# PHP Configuration

This document covers PHP configuration, OPcache settings, and runtime optimization for the GlasgowPHP CQRS application.

## 🔧 PHP Configuration Files

### Development Configuration (`docker/symfony.ini`)

```ini
; Development settings - OPcache disabled for immediate code changes
opcache.enable=0
opcache.enable_cli=0

; Memory and execution settings
memory_limit = 512M
max_execution_time = 30
max_input_time = 60

; Error reporting for development
display_errors = On
display_startup_errors = On
log_errors = On
error_reporting = E_ALL

; Session settings
session.gc_maxlifetime = 3600
session.cookie_lifetime = 0

; File upload settings
upload_max_filesize = 10M
post_max_size = 10M
```

### Production Configuration (`docker/symfony.prod.ini`)

```ini
; Production settings - OPcache enabled for performance
opcache.enable=1
opcache.enable_cli=1

; OPcache optimization
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.interned_strings_buffer=16
opcache.fast_shutdown=1

; Memory and execution settings
memory_limit = 512M
max_execution_time = 30
max_input_time = 60

; Error reporting for production
display_errors = Off
display_startup_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Session settings
session.gc_maxlifetime = 3600
session.cookie_lifetime = 0

; File upload settings
upload_max_filesize = 10M
post_max_size = 10M
```

## ⚡ OPcache Configuration

### What is OPcache?

OPcache is a PHP extension that improves performance by storing precompiled script bytecode in shared memory, eliminating the need for PHP to load and parse scripts on each request.

### Key OPcache Settings

| Setting                           | Development | Production | Description                          |
|-----------------------------------|-------------|------------|--------------------------------------|
| `opcache.enable`                  | 0           | 1          | Enable/disable OPcache               |
| `opcache.memory_consumption`      | 128         | 256        | Memory for storing bytecode (MB)     |
| `opcache.max_accelerated_files`   | 4000        | 20000      | Maximum number of files to cache     |
| `opcache.validate_timestamps`     | 1           | 0          | Check file timestamps for changes    |
| `opcache.revalidate_freq`         | 2           | 0          | Seconds between timestamp checks     |
| `opcache.interned_strings_buffer` | 8           | 16         | Memory for interned strings (MB)     |
| `opcache.fast_shutdown`           | 0           | 1          | Fast shutdown for better performance |

### OPcache Management

#### Development Mode

```bash
# OPcache is disabled for immediate code changes
# No need to clear cache after code changes
```

#### Production Mode

```bash
# Clear OPcache after code changes
curl http://localhost:8088/metrics/opcache/clear

# View OPcache status
curl http://localhost:8088/metrics/opcache/status

# Restart PHP container
docker-compose restart app
```

### OPcache Dashboard

Access the OPcache dashboard at http://localhost:42042 to monitor:

- **Hit Ratio**: Percentage of requests served from cache
- **Memory Usage**: How much memory OPcache is using
- **Cached Files**: Number of files in cache
- **Cache Hits/Misses**: Performance statistics

## 🚀 Performance Optimization

### Memory Settings

```ini
; Increase memory for complex operations
memory_limit = 512M

; For very large applications
memory_limit = 1G
```

### Execution Time

```ini
; Increase for long-running operations
max_execution_time = 60

; For background jobs
max_execution_time = 300
```

### File Upload Limits

```ini
; Increase for file uploads
upload_max_filesize = 50M
post_max_size = 50M

; For large file uploads
upload_max_filesize = 100M
post_max_size = 100M
```

## 🔍 Monitoring OPcache

### Metrics Endpoint

```bash
# Get OPcache statistics
curl http://localhost:8088/metrics/opcache/status

# Response example:
{
  "opcache_enabled": true,
  "cache_full": false,
  "restart_pending": false,
  "restart_in_progress": false,
  "memory_usage": {
    "used_memory": 67108864,
    "free_memory": 201326592,
    "wasted_memory": 0,
    "current_wasted_percentage": 0
  },
  "interned_strings_usage": {
    "buffer_size": 16777216,
    "used_memory": 0,
    "free_memory": 16777216,
    "number_of_strings": 0
  },
  "opcache_statistics": {
    "num_cached_scripts": 1234,
    "num_cached_keys": 1234,
    "max_cached_keys": 20000,
    "hits": 45678,
    "start_time": 1234567890,
    "last_restart_time": 0,
    "oom_restarts": 0,
    "hash_restarts": 0,
    "manual_restarts": 0,
    "misses": 123,
    "blacklist_misses": 0,
    "blacklist_miss_ratio": 0,
    "opcache_hit_rate": 99.73
  }
}
```

### Prometheus Metrics

The application exports OPcache metrics to Prometheus:

```bash
# View metrics
curl http://localhost:8088/metrics

# Example metrics:
php_opcache_enabled 1
php_opcache_memory_usage_bytes 67108864
php_opcache_cached_scripts 1234
php_opcache_hit_rate 99.73
```

## 🛠️ Troubleshooting

### Common Issues

1. **OPcache Not Working**
   ```bash
   # Check if OPcache is enabled
   php -m | grep opcache
   
   # Check PHP configuration
   php --ini
   ```

2. **Memory Issues**
   ```bash
   # Check memory usage
   curl http://localhost:8088/metrics/opcache/status | jq '.memory_usage'
   
   # Increase memory if needed
   # Edit docker/symfony.prod.ini
   opcache.memory_consumption=512
   ```

3. **Cache Not Updating**
   ```bash
   # Clear OPcache
   curl http://localhost:8088/metrics/opcache/clear
   
   # Restart container
   docker-compose restart app
   ```

### Performance Tuning

1. **Optimize Memory Usage**
   ```ini
   ; For large applications
   opcache.memory_consumption=512
   opcache.max_accelerated_files=50000
   ```

2. **Enable JIT (PHP 8.0+)**
   ```ini
   opcache.jit_buffer_size=100M
   opcache.jit=1235
   ```

3. **Optimize for Development**
   ```ini
   ; Enable timestamp validation
   opcache.validate_timestamps=1
   opcache.revalidate_freq=2
   ```

## 📊 Best Practices

### Development

- Disable OPcache for immediate code changes
- Use timestamp validation for automatic cache invalidation
- Monitor memory usage during development

### Production

- Enable OPcache for maximum performance
- Disable timestamp validation for better performance
- Set appropriate memory limits
- Monitor hit ratio and memory usage
- Clear cache after deployments

### Monitoring

- Set up alerts for low hit ratios
- Monitor memory usage trends
- Track cache misses and restarts
- Use Grafana dashboards for visualization

## 🔗 Related Documentation

- [Docker Compose Configuration](docker-compose.md)
- [Monitoring Setup](monitoring-setup.md)
- [Performance Testing](performance-testing.md) 