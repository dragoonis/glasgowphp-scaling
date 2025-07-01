# PHP-FPM Performance Dashboard

## Dashboard Panels Description

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
