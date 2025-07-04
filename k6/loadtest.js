import http from 'k6/http';
import { check, sleep } from 'k6';
import { htmlReport } from 'https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js';
import { textSummary } from "https://jslib.k6.io/k6-summary/0.0.1/index.js";

// Configure for exactly 3000 requests with 100 VUs
export let options = {
    insecureSkipTLSVerify: true,
    vus: 100,           // 100 Virtual Users
    iterations: 3000,   // Total of 3000 requests (30 requests per VU on average)
};

export default function () {
    const url = 'https://localhost/en/blog/';
    const res = http.get(url);

    // Basic checks
    check(res, {
        'status is 200': (r) => r.status === 200,
        'body size > 1KB': (r) => r.body.length > 1024,
    });

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