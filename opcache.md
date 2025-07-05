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


#### Preloading

```
opcache.preload_user=www-data
opcache.preload=/var/www/html/config/preload.php
```

**one-time load for composer**

`$composer->findFileWithExtension()`

it transforms class name, into a file name, and then looks it up

expensive lookup time

preload loads it all into memory in 1 go, keeps it in memory