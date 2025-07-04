# GlasgowPHP Scaling Demo

This project demonstrates a scalable Symfony CQRS application with Redis and DB projections, Docker Compose, and k6 load testing. All common tasks are managed via the Makefile.

## Quick Start

1. **Build and start all services:**
   ```bash
   make up
   ```

2. **Install dependencies:**
   ```bash
   docker-compose exec app composer install
   ```

3. **Set up the database and seed data:**
   ```bash
   make setup
   # (runs migrations and seeds the database)
   ```

4. **Rebuild projections (Redis):**
   ```bash
   make rebuild-projections
   ```

5. **Access the app:**
   - Web: http://localhost:8088/en
   - API: http://localhost:8088/en/products

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

- **Development** (`docker/symfony.ini`): `opcache.enable=0` - OPcache disabled for immediate code changes
- **Production** (`docker/symfony.prod.ini`): OPcache enabled by default for performance

**⚠️ Important Warning**: When OPcache is enabled (`opcache.enable=1`), code changes will not be reflected immediately. You must either:
- Restart the PHP-FPM container: `docker-compose restart app`
- Clear OPcache via the metrics endpoint: `curl http://localhost:8088/metrics/opcache/clear`
- Or disable OPcache for development by setting `opcache.enable=0` in your PHP configuration

The current docker-compose setup binds the development config (`symfony.ini`) to override the production config, ensuring OPcache is disabled for development.

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

- If `watch` is present, FrankenPHP will monitor your PHP files and reload the worker process on changes.
- This is especially useful for development, as you do not need to restart the container to see code changes.
- Both the main FrankenPHP and franken-worker services support this if configured.

**Note:** In production, you may want to remove the `watch` directive for performance and stability.

## Caddy Configuration

Caddy configuration can be extended via environment variables in the docker-compose.yml:

```yaml
environment:
  CADDY_GLOBAL_OPTIONS: "admin 0.0.0.0:2019\nmetrics"
  SERVER_NAME: ":8080 https://localhost:443"
```

- `CADDY_GLOBAL_OPTIONS`: Appends global Caddy configuration (enables admin API and metrics)
- `SERVER_NAME`: Defines server names for the Caddy instance
- These values are appended to the base Caddyfile configuration

This allows for flexible configuration without modifying the Caddyfile directly.
