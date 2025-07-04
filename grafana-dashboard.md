# Web Server Monitoring Dashboards Guide

A comprehensive guide to understanding and using two Grafana dashboards for full-stack web server monitoring: **Caddy HTTP Server** with FrankenPHP support and **PHP-FPM Performance** monitoring.

## Overview

This monitoring suite provides complete visibility into your web application stack through two specialized dashboards. Each dashboard focuses on different aspects of your infrastructure, working together to give you end-to-end insights into web server and PHP application performance.

---

## 🌐 Caddy HTTP Server Dashboard

### Dashboard Structure

The Caddy dashboard is organized into four main sections:

#### 1. Summary Section
High-level overview panels providing instant health indicators:

- **Requests in Flight** - Shows current active requests being processed by the server
- **Total Requests** - Cumulative request count for the selected time range
- **Requests per Second** - Real-time request rate showing traffic intensity
- **Median Request Duration** - P50 response time for overall performance assessment
- **Median Response Duration** - P50 time-to-first-byte for server responsiveness
- **Total Middleware Errors** - Count of errors encountered in Caddy middleware
- **Request/Response Sizes** - Data transfer statistics (median and total)
- **4xx/5xx Response Percentages** - Error rate indicators for client and server errors

#### 2. FrankenPHP Metrics Section
Specialized panels for PHP application server monitoring:

**Thread Management:**
- **Busy PHP Threads** - Number of threads currently processing requests (🟢 <10, 🟡 10-15, 🔴 >15)
- **Total PHP Threads** - Available thread capacity
- **Thread Utilization %** - Efficiency metric showing busy/total thread ratio (🟢 <70%, 🟡 70-90%, 🔴 >90%)

**Worker Management:**
- **Busy PHP Workers** - Active workers per worker type (🟢 <8, 🟡 8-12, 🔴 >12)
- **Total PHP Workers** - Configured worker capacity per type
- **Ready PHP Workers** - Workers that have successfully initialized
- **Request Queue Depth** - Pending requests waiting for processing (🟢 0, 🟡 1-5, 🔴 >10)

**Performance Tracking:**
- **Worker Requests Total** - Cumulative requests processed by workers
- **Average Worker Request Time** - Performance per worker type (🟢 <0.1s, 🟡 0.1-0.5s, 🔴 >0.5s)
- **Worker Restarts** - Restart count indicating stability (🟢 0, 🟡 1-5, 🔴 >5)

**Time Series Visualizations:**
- **PHP Thread Utilization** - Thread usage trends over time
- **PHP Worker Status & Queue** - Worker states and queue depth trends
- **Worker Request Rate** - Requests per second by worker type
- **Average Worker Request Time** - Performance trends by worker

#### 3. Detailed Metrics Section
In-depth analysis panels for comprehensive monitoring:

**Request Analysis:**
- **Rate of Requests by HTTP Method** - Stacked time series showing GET, POST, PUT, DELETE patterns
- **HTTP Status Breakdown** - Pie chart showing 2xx, 3xx, 4xx, 5xx distribution
- **HTTP Method Breakdown** - Pie chart showing request method distribution

**Performance Analysis:**
- **Request Duration Percentiles** - P50, P75, P90, P95, P99 response times over time
- **Data Transfer Rate** - Request vs response data flow visualization
- **Rate of 4xx and 5xx Responses** - Error trends by status code
- **Middleware Error Rate** - Caddy-specific error monitoring

#### 4. Heatmaps Section (Collapsible)
Distribution analysis for detailed performance insights:

- **Request Duration Heatmap** - Response time distribution patterns
- **Request Size Heatmap** - Incoming request size patterns
- **Response Duration Heatmap** - Time-to-first-byte distribution
- **Response Size Heatmap** - Outgoing response size patterns

### Key Dashboard Variables
- **Datasource** - Prometheus instance selector
- **Job** - Caddy job name filter
- **Instance** - Specific Caddy instance selector
- **Interval** - Metrics aggregation period (30s, 1m, 5m, etc.)

---

## 🐘 PHP-FPM Performance Dashboard

### Dashboard Structure

The PHP-FPM dashboard is organized into two main collapsible sections:

#### 1. PHP-FPM Metrics Section

**Critical Health Indicators (Top Row):**
- **Max Children Reached** - Times the process limit was hit (🟢 0, 🟡 ≥1, 🔴 ≥5)
- **Scrape Failures** - Monitoring system reliability (🟢 0, 🟡 ≥1, 🔴 ≥5)
- **Slow Requests** - Requests exceeding configured threshold (🟢 0, 🟡 ≥1, 🔴 ≥10)
- **Process Utilization** - Percentage of active processes (🟢 <70%, 🟡 70-90%, 🔴 >90%)
- **Uptime** - Pool runtime since last restart
- **Queue Depth** - Pending connections waiting for processes (🟢 0, 🟡 ≥1, 🔴 ≥10)

**Performance Trends:**
- **Request Rate** - Time series showing requests per second using `rate(phpfpm_accepted_connections[5m])`
- **Request Duration** - Dual-line chart showing average and maximum processing times
- **Process States** - Stacked area chart showing active vs idle process distribution
- **Process Details** - Color-coded table listing individual process states (🟢 Idle, 🔵 Running)

**Resource Monitoring:**
- **Memory Usage per Request** - Average and maximum memory consumption trends

#### 2. OPcache Metrics Section

**Cache Health Indicators (Top Row):**
- **OPcache Hit Ratio** - Cache efficiency percentage (🔴 <80%, 🟡 80-95%, 🟢 >95%)
- **OPcache Memory Usage** - Memory utilization percentage (🟢 <70%, 🟡 70-85%, 🔴 >85%)
- **Script Cache Usage** - Cached scripts vs maximum capacity (🟢 <80%, 🟡 80-90%, 🔴 >90%)
- **OPcache Status** - Enabled/disabled indicator (🔴 Disabled, 🟢 Enabled)

**Cache Performance:**
- **Cache Hit/Miss Rate** - Time series showing hit vs miss trends (🟢 Hits, 🔴 Misses)
- **Memory Usage Breakdown** - Stacked chart showing used, free, and wasted memory (🔵 Used, 🟢 Free, 🟠 Wasted)
- **Interned Strings** - String deduplication efficiency metrics
- **JIT Status** - Just-In-Time compilation details table with color-coded status

**Cache Management:**
- **Cache Restarts** - Stacked time series showing:
  - Out-of-memory restarts (OOM)
  - Hash table full restarts
  - Manual restarts

### Key Dashboard Variables
- **Datasource** - Prometheus instance selector
- **Pool** - PHP-FPM pool selector (dynamically populated)

---

## 📊 Understanding the Metrics

### Performance Indicators

**Green Indicators (Good Performance):**
- Low error rates (<1%)
- Fast response times (<100ms P95)
- High cache hit ratios (>95%)
- Low process utilization (<70%)
- Empty request queues

**Yellow Indicators (Warning):**
- Moderate error rates (1-5%)
- Elevated response times (100-500ms P95)
- Medium cache hit ratios (80-95%)
- Medium process utilization (70-90%)
- Small request queues (1-5)

**Red Indicators (Critical Issues):**
- High error rates (>5%)
- Slow response times (>500ms P95)
- Low cache hit ratios (<80%)
- High process utilization (>90%)
- Large request queues (>10)
