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
