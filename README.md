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

## FrankenPHP Configuration

This project uses FrankenPHP (a modern PHP runtime built on Caddy) with two different configurations for performance comparison and monitoring.

**See [`frankenphp.md`](frankenphp.md) for complete FrankenPHP documentation including:**
- Service configuration and differences
- Auto-reload (file watching) setup
- Caddy configuration and environment variables
- Performance testing and monitoring
- Troubleshooting guide
- Resource optimization guidelines