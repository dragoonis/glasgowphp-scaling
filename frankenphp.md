# FrankenPHP Configuration & Performance Testing

## Overview

This project uses FrankenPHP (a modern PHP runtime built on Caddy) with two different configurations for performance comparison and monitoring.

## Service Configuration

### FrankenPHP Services

| Service               | Port | Mode    | Caddyfile           | Purpose                      |
|-----------------------|------|---------|---------------------|------------------------------|
| **FrankenPHP**        | 443  | Regular | `Caddyfile.regular` | Traditional PHP server mode  |
| **FrankenPHP Worker** | 444  | Worker  | `Caddyfile`         | High-performance worker mode |

### Configuration Differences

**Regular Mode (`Caddyfile.regular`):**
- Uses traditional PHP server mode
- Good for development and simple applications
- Auto-reloads on file changes

**Worker Mode (`Caddyfile`):**
- Uses FrankenPHP worker mode for better performance
- Handles multiple requests concurrently
- Auto-reloads on file changes
- Better suited for production workloads

## Auto-Reload (File Watching)

Both FrankenPHP services can automatically reload PHP workers when your code changes, thanks to the `watch` directive in the Caddyfile:

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

## Performance Testing & Monitoring

### Load Testing FrankenPHP Services

**Test FrankenPHP (Regular Mode):**
```bash
# Test regular FrankenPHP on port 443
k6 run k6/list_products.js --env BASE_URL=https://localhost:443/en
```

**Test FrankenPHP Worker Mode:**
```bash
# Test worker mode on port 444
k6 run k6/list_products.js --env BASE_URL=https://localhost:444/en
```

### Monitoring FrankenPHP Performance

**Real-time Metrics:**
- **FrankenPHP Metrics**: http://localhost:2019/metrics
- **Worker Metrics**: http://localhost:2020/metrics

**Key Metrics to Monitor:**
```bash
# FrankenPHP regular mode metrics
curl http://localhost:2019/metrics | grep frankenphp

# Worker mode metrics  
curl http://localhost:2020/metrics | grep frankenphp
```

**Important FrankenPHP Metrics:**
- `frankenphp_total_workers` - Number of active workers
- `frankenphp_requests_total` - Total requests processed
- `frankenphp_request_duration_seconds` - Request processing time
- `frankenphp_memory_usage_bytes` - Memory consumption

### Grafana Dashboard for FrankenPHP

**Access Grafana Dashboard:**
- URL: http://localhost:3000
- Username: `croatia`
- Password: `croatia`

**FrankenPHP Monitoring Panels:**
1. **Worker Count**: Shows active FrankenPHP workers
2. **Request Rate**: Requests per second for each service
3. **Response Time**: Average response times
4. **Memory Usage**: Memory consumption per worker
5. **Error Rate**: Failed requests percentage

### Performance Comparison Testing

**Step 1: Baseline Testing**
```bash
# Test regular FrankenPHP
make k6-franken-products
# Check results in k6/report-franken-*.html

# Test worker mode
make k6-franken-worker-products  
# Check results in k6/report-worker-*.html
```

**Step 2: Monitor in Grafana**
- Compare request rates between services
- Monitor memory usage differences
- Check response time variations
- Observe worker scaling behavior

**Step 3: Analyze Results**
- Worker mode typically shows better concurrency
- Regular mode may have lower memory overhead
- Response times vary based on load patterns

### FrankenPHP Configuration Testing

**Test Auto-Reload (Development):**
```bash
# Make a code change and watch workers reload
echo "// Test change" >> src/Controller/ProductController.php

# Check metrics for worker restart
curl http://localhost:2019/metrics | grep frankenphp_total_workers
```

**Test Worker Scaling:**
```bash
# Monitor worker count during load
watch -n 1 'curl -s http://localhost:2019/metrics | grep frankenphp_total_workers'

# Run load test in another terminal
k6 run k6/list_products.js --env BASE_URL=https://localhost:443/en
```

## Troubleshooting FrankenPHP

### Common Issues:
1. **Workers not starting**: Check Caddyfile syntax
2. **High memory usage**: Adjust `num_threads` and `memory_limit`
3. **Slow response times**: Monitor worker count and scaling
4. **Auto-reload not working**: Verify `watch` directive in Caddyfile

### Debug Commands:
```bash
# Check FrankenPHP logs
docker-compose logs franken

# Check worker mode logs
docker-compose logs franken-worker

# Verify configuration
docker-compose exec franken cat /etc/frankenphp/Caddyfile
docker-compose exec franken-worker cat /etc/frankenphp/Caddyfile
```

## Performance Optimization

### Resource Configuration Guidelines

The appropriate values depend heavily on how your application is written, what it does and your hardware.
It is recommended to have `num_threads` × `memory_limit` < `available_memory`.

https://frankenphp.dev/docs/performance/#number-of-threads-and-workers

### Calculating Optimal Configuration

```caddyfile
{
    frankenphp {
        num_threads 16      # Number of PHP threads
        worker {
            num 8           # Number of worker processes
        }
        php_ini memory_limit 256M
    }
}
```

#### Memory Calculation Example
- **Available Memory**: 8GB (8192MB)
- **PHP memory_limit**: 256MB
- **num_threads**: 16
- **Total PHP Memory**: 16 × 256MB = 4096MB (4GB)
- **Remaining for OS/Caddy**: 4GB ✓

### Performance Optimization Tips

**For Development:**
- Use `watch` directive for auto-reload
- Keep `num_threads` low (2-4) for faster startup
- Monitor memory usage with `memory_limit`

**For Production:**
- Disable `watch` directive
- Increase `num_threads` based on CPU cores
- Set appropriate `memory_limit` per thread
- Use worker mode for better concurrency

**Memory Calculation Formula:**
```
Total Memory = num_threads × memory_limit + OS_overhead
Recommended: Total Memory < Available System Memory × 0.8
```