# PHP-FPM Performance Dashboard

## Dashboard Panels Description

The dashboard is organized into two main sections: **PHP-FPM Metrics** and **OPcache Metrics**, each in collapsible rows for better organization.

---

## PHP-FPM Metrics Section

### Top Row - Critical Health Indicators

#### Max Children Reached
- **Type:** Stat panel with threshold colors
- **Purpose:** Shows how many times PHP-FPM hit the maximum process limit
- **Alert Levels:** Green (0), Yellow (≥1), Red (≥5)
- **Why Important:** Indicates if your pool size is too small for the workload

#### Scrape Failures
- **Type:** Stat panel with threshold colors
- **Purpose:** Displays monitoring system failures when collecting PHP-FPM metrics
- **Alert Levels:** Green (0), Yellow (≥1), Red (≥5)
- **Why Important:** High failures mean unreliable monitoring data

#### Slow Requests
- **Type:** Stat panel with threshold colors
- **Purpose:** Count of requests that exceeded the configured slow request threshold
- **Alert Levels:** Green (0), Yellow (≥1), Red (≥10)
- **Why Important:** Identifies performance bottlenecks in your application

#### Process Utilization
- **Type:** Stat panel with percentage gauge
- **Purpose:** Shows what percentage of available processes are currently active
- **Alert Levels:** Green (<70%), Yellow (70-90%), Red (>90%)
- **Why Important:** High utilization indicates need for more processes or optimization

#### Uptime
- **Type:** Stat panel in seconds
- **Purpose:** Shows how long the PHP-FPM pool has been running since last restart
- **Why Important:** Tracks stability and restart frequency

#### Queue Depth
- **Type:** Stat panel with area graph
- **Purpose:** Number of pending connections waiting for available processes
- **Alert Levels:** Green (0), Yellow (≥1), Red (≥10)
- **Why Important:** Queue buildup indicates insufficient process capacity

### Middle Rows - Performance Trends

#### Request Rate
- **Type:** Time series line chart
- **Purpose:** Visualizes requests per second over time using rate() function
- **Metric:** `rate(phpfpm_accepted_connections[5m])`
- **Why Important:** Shows traffic patterns and helps identify peak usage periods

#### Request Duration
- **Type:** Time series with dual lines
- **Purpose:** Tracks both average and maximum request processing times
- **Metrics:** Average and max of `phpfpm_process_request_duration`
- **Why Important:** Identifies performance degradation and response time spikes

#### Process States
- **Type:** Stacked time series chart
- **Purpose:** Shows the distribution of active vs idle processes over time
- **Visualization:** Stacked areas showing process state breakdown
- **Why Important:** Helps understand process utilization patterns and capacity planning

#### Process Details
- **Type:** Data table with color coding
- **Purpose:** Lists individual processes with their current state (Idle/Running)
- **Features:** Color-coded states (Green=Idle, Blue=Running)
- **Why Important:** Provides granular view of process-level activity

### Bottom Row - Resource Monitoring

#### Memory Usage per Request
- **Type:** Time series with dual metrics
- **Purpose:** Tracks memory consumption patterns for processed requests
- **Metrics:** Average and maximum memory usage per request
- **Why Important:** Identifies memory leaks and helps with capacity planning

---

## OPcache Metrics Section

### Top Row - Cache Health Indicators

#### OPcache Hit Ratio
- **Type:** Stat panel with percentage gauge
- **Purpose:** Shows the cache hit ratio as a percentage
- **Alert Levels:** Red (<80%), Yellow (80-95%), Green (>95%)
- **Metric:** `opcache_hit_ratio`
- **Why Important:** Values above 95% indicate good cache performance; low hit rates suggest frequent code changes or insufficient cache size

#### OPcache Memory Usage
- **Type:** Stat panel with percentage gauge
- **Purpose:** Shows memory utilization percentage of OPcache
- **Alert Levels:** Green (<70%), Yellow (70-85%), Red (>85%)
- **Metric:** `opcache_memory_used_bytes / (opcache_memory_used_bytes + opcache_memory_free_bytes)`
- **Why Important:** High usage may indicate need for more memory allocation or cache optimization

#### Script Cache Usage
- **Type:** Stat panel with percentage gauge
- **Purpose:** Shows ratio of cached scripts vs maximum capacity
- **Alert Levels:** Green (<80%), Yellow (80-90%), Red (>90%)
- **Metric:** `opcache_num_cached_scripts / opcache_max_cached_keys`
- **Why Important:** Indicates how close you are to the script limit

#### OPcache Status
- **Type:** Stat panel with color-coded status
- **Purpose:** Shows if OPcache is enabled or disabled
- **Status Mapping:** Red (Disabled), Green (Enabled)
- **Metric:** `opcache_enabled`
- **Why Important:** Confirms OPcache is operational

### Middle Rows - Cache Performance

#### Cache Hit/Miss Rate
- **Type:** Time series with dual lines
- **Purpose:** Shows rate of cache hits and misses over time
- **Metrics:** `rate(opcache_hits_total[5m])` and `rate(opcache_misses_total[5m])`
- **Color Coding:** Green (Hits), Red (Misses)
- **Why Important:** Consistent high hit rates indicate good cache performance

#### Memory Usage Breakdown
- **Type:** Stacked time series chart
- **Purpose:** Shows detailed memory allocation within OPcache
- **Metrics:** Used, Free, and Wasted memory in bytes
- **Color Coding:** Blue (Used), Green (Free), Orange (Wasted)
- **Why Important:** Helps optimize memory allocation and identify wasted space

#### Interned Strings
- **Type:** Time series with dual axes
- **Purpose:** Tracks interned strings memory usage and count
- **Metrics:** `opcache_interned_strings_used_memory_bytes` and `opcache_interned_strings_count`
- **Why Important:** Shows efficiency of string deduplication in PHP

#### JIT Status
- **Type:** Data table with color coding
- **Purpose:** Displays JIT compilation status and configuration
- **Metrics:** JIT Enabled, JIT On, Buffer Size, Optimization Level
- **Features:** Color-coded enabled/disabled states
- **Why Important:** Shows if JIT is enabled and how much buffer is being used

### Bottom Row - Cache Management

#### Cache Restarts
- **Type:** Stacked time series chart
- **Purpose:** Shows different types of cache restarts over time
- **Metrics:**
    - `opcache_oom_restarts_total` (Out of Memory)
    - `opcache_hash_restarts_total` (Hash table full)
    - `opcache_manual_restarts_total` (Manual restarts)
- **Why Important:** Frequent restarts may indicate configuration issues or insufficient cache size

---

## Dashboard Features

### Variables
- **Datasource:** Prometheus datasource selector
- **Pool:** Dynamic PHP-FPM pool selector based on available pools

### Refresh Settings
- **Auto-refresh:** Every 5 seconds
- **Time Range:** Last 1 hour (configurable)

### Tags
- php-fpm
- performance
- monitoring
- opcache

---

## Monitoring Best Practices

### PHP-FPM Optimization
1. **Monitor Process Utilization:** Keep below 80% during normal operations
2. **Watch Queue Depth:** Should remain at 0 under normal load
3. **Track Slow Requests:** Investigate any requests exceeding thresholds
4. **Memory Monitoring:** Watch for memory leaks in long-running processes

### OPcache Optimization
1. **Maintain High Hit Ratio:** Target >95% for optimal performance
2. **Monitor Memory Usage:** Keep below 85% to avoid performance issues
3. **Watch Cache Restarts:** Frequent restarts indicate configuration problems
4. **JIT Configuration:** Ensure JIT is properly configured if using PHP 8.0+

### Alert Recommendations
- Set up alerts for PHP-FPM max children reached
- Monitor OPcache hit ratio drops below 90%
- Alert on excessive cache restarts
- Track memory usage trends for capacity planning