import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 10 },   // Ramp up to 10 users
    { duration: '1m', target: 10 },   // Stay at 10 users
    { duration: '20s', target: 0 },    // Ramp down to 0 users
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    http_req_failed: ['rate<0.01'],    // Error rate must be less than 1%
  },
};

const BASE_URL = __ENV.API_URL || 'http://127.0.0.1:8000/api';

export default function () {
  // Test public endpoints
  let response = http.get(`${BASE_URL}/products`);
  check(response, {
    'products status is 200': (r) => r.status === 200,
    'products response time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);

  // Test top selling endpoint
  response = http.get(`${BASE_URL}/products/top-selling`);
  check(response, {
    'top-selling status is 200': (r) => r.status === 200,
    'top-selling response time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);

  // Test categories endpoint
  response = http.get(`${BASE_URL}/categories`);
  check(response, {
    'categories status is 200': (r) => r.status === 200,
    'categories response time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);
}
