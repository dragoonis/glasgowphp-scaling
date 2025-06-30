import http from 'k6/http';
import { check, sleep } from 'k6';
import { htmlReport } from 'https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js';
import { textSummary } from "https://jslib.k6.io/k6-summary/0.0.1/index.js";

// 1. Define your test stages (ramp-up, sustain, ramp-down)
export let options = {
    stages: [
      { duration: '10s', target: 20 },  // ramp up to 50 virtual users over 30s
      { duration: '5s',  target: 20 },  // stay at 50 users for 1m
      { duration: '10s', target: 0 },   // ramp down to 0 over 30s
    ],
    thresholds: {
      http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    },
};

export default function () {
  // 2. Send GET request
  // const res = http.get('http://localhost:8088/en/blog/');
  const res = http.get('http://localhost:9501');

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