import http from 'k6/http';
import { check, sleep } from 'k6';
import { htmlReport } from 'https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js';
import { textSummary } from "https://jslib.k6.io/k6-summary/0.0.1/index.js";

// 1. Define your test stages (ramp-up, sustain, ramp-down)
export let options = {
    stages: [
        { duration: '1s', target: 5 },    // Warm up
        { duration: '5s', target: 50 },    // Quick spike to 50 users
        { duration: '15s', target: 50 },   // Stay at spike (test PHP-FPM limits)
        { duration: '5s', target: 10 },    // Drop back to normal
        { duration: '10s', target: 10 },   // Normal load
        { duration: '5s', target: 100 },   // Even bigger spike (stress test)
        { duration: '5s', target: 100 },   // Stay at big spike
        { duration: '5s', target: 0 },     // Ramp down
    ],
    thresholds: {
      http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    },
};

export default function () {
  // 2. Send GET request
  const res = http.get('http://localhost:8088/en/blog/');
  // const res = http.get('http://localhost:9501');

  // 3. Basic checks
  check(res, {
    'status is 200': (r) => r.status === 200,
    'body size > 1KB': (r) => r.body.length > 1024,
  });

  // 4. Pause between iterations
  sleep(1);
}

export function handleSummary(data) {

    // get an ISO timestamp and make it filesystem-friendly
    const now = new Date().toISOString().replace(/[:]/g, '-');
    // build your filename
    const filename = `./k6/report-${now}.html`;

    return {
        [filename]: htmlReport(data),
        stdout: textSummary(data, { indent: " ", enableColors: true }),
    };
}