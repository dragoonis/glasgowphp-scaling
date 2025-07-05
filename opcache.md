# PHP Configuration Documentation

This document explains each configuration setting in our PHP setup, following the implementation in `./docker/symfony.prod.ini`.

## Memory Management

### Memory Limit
```
memory_limit=512M
```
**Purpose:** Sets the maximum amount of memory a PHP script can consume.
**How It Works:**
1. PHP process starts
2. Script begins execution
3. Memory usage monitored continuously
4. If limit exceeded → Fatal error: "Allowed memory size exhausted"
5. Process terminates to protect server

**Resource Protection:** Prevents runaway scripts from consuming all available RAM on 16GB server.

### Upload Configuration
```
upload_max_filesize=50M
post_max_size=76M
```
**Purpose:** Controls file upload limits and POST data size.
**How It Works:**
- `upload_max_filesize`: Maximum size for individual uploaded files
- `post_max_size`: Maximum size for entire POST request (must be larger)

**Upload Flow:**
1. User selects 50MB file
2. Browser sends POST request with file + form data
3. PHP checks `post_max_size` first (76MB) → ✓ Allow
4. PHP checks `upload_max_filesize` (50MB) → ✓ Allow
5. File processed successfully

## Execution Control

### Execution Time
```
max_execution_time=300
```
**Purpose:** Prevents scripts from running indefinitely.
**How It Works:**
1. Script execution begins
2. Timer starts (300 seconds = 5 minutes)
3. If timer expires → Fatal error: "Maximum execution time exceeded"
4. Process terminates

**Use Cases:** Data imports/exports, complex calculations, API synchronization, report generation

## Security Configuration

### PHP Information Disclosure
```
expose_php=0
```
**Purpose:** Hides PHP version from HTTP headers.
**How It Works:**
- With `expose_php=1`: HTTP response includes `X-Powered-By: PHP/8.2.0`
- With `expose_php=0`: No PHP version header sent

**Security Benefit:** Attackers cannot easily identify PHP version vulnerabilities

### Code Compatibility
```
short_open_tag=Off
```
**Purpose:** Enforces full PHP opening tags.
**How It Works:**
- **Disabled:** Only `<?php` tags work
- **Enabled:** Both `<?php` and `<?` tags work
- **XML Conflict Prevention:** `<?xml version="1.0"?>` won't be mistaken for PHP code

## Internationalization

### Timezone Configuration
```
date.timezone="US/Eastern"
```
**Purpose:** Sets default timezone for all date/time functions.
**How It Works:**
1. PHP function called: `date('Y-m-d H:i:s')`
2. No timezone specified → Uses `date.timezone` setting
3. Returns time in US/Eastern timezone
4. Prevents "It is not safe to rely on the system's timezone settings" warnings

### Locale Settings
```
intl.default_locale="en"
```
**Purpose:** Default locale for internationalization functions.
**How It Works:**
- Number formatting: `1234.56` vs `1.234,56`
- Currency display: `$1,234.56` vs `€1.234,56`
- Text collation: Sorting order for strings

### Unicode Detection
```
zend.detect_unicode=0
```
**Purpose:** Disables automatic Unicode detection for performance.
**How It Works:**
- **Enabled:** PHP checks each file for Unicode BOM (Byte Order Mark)
- **Disabled:** Skips Unicode detection → Faster file processing

**Trade-off:** Manual Unicode handling required but performance gained

## Session Management

### Session Lifetime
```
session.gc_maxlifetime=7200
```
**Purpose:** Controls how long session data survives.
**How It Works:**
1. User logs in → Session created with timestamp
2. User inactive for 7200 seconds (2 hours)
3. Garbage collector runs → Removes expired session
4. Next request → Session not found, user must re-authenticate

### Session Startup
```
session.auto_start=Off
```
**Purpose:** Prevents automatic session initialization.
**How It Works:**
- **Auto-start Off:** Sessions created only when `session_start()` called
- **Auto-start On:** Session created for every request

**Performance Benefit:** Saves resources on pages that don't need sessions

## PHP-FPM Process Manager

**Server Specs:** 16GB RAM, 12GB reserved for applications
**Process Calculation:** Using [PM Calculator](https://spot13.com/pmcalculator/)

### Process Manager Type
```
pm = dynamic
```
**Purpose:** Dynamic process management based on demand.
**How It Works:**
1. Low traffic → Few processes running
2. Traffic increases → More processes spawned
3. Traffic decreases → Excess processes killed
4. Balances resource usage with performance

### Process Limits
```
pm.max_children = 14
pm.start_servers = 3
pm.min_spare_servers = 3
pm.max_spare_servers = 10
```
**Calculation Breakdown:**
- **Total RAM:** 16GB
- **Reserved RAM:** 12GB
- **RAM Buffer:** 10%
- **Process Size:** 255MB
- **Formula:** `(12GB × 0.9) ÷ 255MB ≈ 14 processes`

**Process Lifecycle:**
1. **Startup:** 3 processes created immediately
2. **Minimum Spare:** Always keep 3 idle processes ready
3. **Maximum Spare:** Kill processes if more than 10 are idle
4. **Maximum Total:** Never exceed 14 processes

## Security Functions

### Disabled Functions
```
disable_functions=apache_child_terminate,apache_get_modules,apache_note,apache_setenv,define_syslog_variables,disk_total_space,diskfreespace,dl,escapeshellcmd,eval,get_current_user,getlastmo,getmygid,getmyinode,getmypid,getmyuid,ini_restore,pcntl_alarm,pcntl_exec,pcntl_fork,pcntl_get_last_error,pcntl_getpriority,pcntl_setpriority,pcntl_signal,pcntl_signal_dispatch,pcntl_sigprocmask,pcntl_sigtimedwait,pcntl_sigwaitinfo,pcntl_strerrorp,pcntl_wait,pcntl_waitpid,pcntl_wexitstatus,pcntl_wifexited,pcntl_wifsignaled,pcntl_wifstopped,pcntl_wstopsig,pcntl_wtermsig,posix_getlogin,posix_kill,posix_mkfifo,posix_setpgid,posix_setsid,posix_setuid,posix_ttyname,posix_uname,posixc,proc_nice,proc_terminate,ps_aux,runkit_function_rename,show_source,syslog,system
```
**Purpose:** Disables potentially dangerous PHP functions.
**Security Categories:**
- **Process Control:** `pcntl_*` functions - Prevents process manipulation
- **System Access:** `system`, `exec` - Blocks command execution
- **File System:** `show_source`, `disk_total_space` - Limits file access
- **Apache Functions:** `apache_*` - Prevents Apache configuration access
- **Code Injection:** `eval` - Blocks dynamic code execution

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

### Core OPcache Settings
```
opcache.enable=1
opcache.enable_cli=1
```
**Purpose:** Enables OPcache for web requests and CLI scripts.
**How It Works:**
1. PHP file requested
2. OPcache checks if bytecode exists in memory
3. If exists → Use cached bytecode
4. If not → Compile and cache bytecode
5. Serve request

**Performance Gain:** Can improve performance by 2-3x by eliminating compilation overhead

### Validation Settings
```
opcache.revalidate_freq=0
opcache.validate_timestamps=1
```
**Purpose:** Controls how often OPcache checks for file changes.
**How It Works:**
- `revalidate_freq=0`: Check every request (when validate_timestamps=1)
- `validate_timestamps=1`: Enable timestamp checking (development setting)

**Note:** Production uses `validate_timestamps=0` for maximum performance

### Memory Configuration
```
opcache.max_accelerated_files=16087
opcache.memory_consumption=192
opcache.interned_strings_buffer=16
```
**File Count Calculation:**
```bash
find . -type f -name "*.php" | wc -l
```
**Purpose:** Ensures OPcache can handle all project files.
**How It Works:**
- **max_accelerated_files:** 16,087 files can be cached
- **memory_consumption:** 192MB allocated for bytecode storage
- **interned_strings_buffer:** 16MB for storing duplicate strings once

**Notes on opcache.max_accelerated_files**

`opcache.max_accelerated_files` **has to be a prime number**.

If you set a value that is not prime, PHP will automatically calculate and use the next prime number above your value. 
The value **must be higher than the number of files in your application**; otherwise, the setting is useless and may cause issues.
See the official documentation: [php.net: opcache.max_accelerated_files](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.max-accelerated-files)


### Performance Optimizations
```
opcache.fast_shutdown=1
opcache.enable_file_override=1
```
**Purpose:** Additional performance improvements.
**How It Works:**
- **fast_shutdown:** Faster process termination
- **enable_file_override:** Caches `file_exists()`, `is_file()` results

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

## Performance Optimization

### Realpath Cache
```
realpath_cache_size=4096K
realpath_cache_ttl=600
```
**Purpose:** Caches resolved file paths to reduce filesystem lookups.
**How It Works:**
1. PHP requests file: `src/Controller/ProductController.php`
2. System resolves symlinks and relative paths
3. Result cached for 600 seconds (10 minutes)
4. Subsequent requests use cached path
5. Reduces I/O operations significantly

**Performance Reference:** [Symfony Performance Guide](https://symfony.com/doc/current/performance.html)