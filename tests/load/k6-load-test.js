/**
 * Script de test de charge k6
 * 
 * Installation : https://k6.io/docs/getting-started/installation/
 *   winget install k6
 * 
 * Exécution :
 *   k6 run tests/load/k6-load-test.js
 * 
 * Avec plus d'utilisateurs :
 *   k6 run --vus 50 --duration 60s tests/load/k6-load-test.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Métriques personnalisées
const errorRate = new Rate('errors');
const pageLoadTime = new Trend('page_load_time');

// Configuration du test
export const options = {
    // Montée progressive en charge
    stages: [
        { duration: '10s', target: 5 },   // Montée à 5 utilisateurs
        { duration: '30s', target: 20 },   // Montée à 20 utilisateurs
        { duration: '30s', target: 20 },   // Maintien à 20 utilisateurs
        { duration: '10s', target: 50 },   // Pic à 50 utilisateurs
        { duration: '20s', target: 50 },   // Maintien du pic
        { duration: '10s', target: 0 },    // Descente progressive
    ],
    thresholds: {
        // Critères de réussite
        http_req_duration: ['p(95)<2000'],  // 95% des requêtes < 2s
        http_req_failed: ['rate<0.05'],     // Moins de 5% d'erreurs
        errors: ['rate<0.1'],               // Taux d'erreur custom < 10%
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost/ProjetTemplatesRestaurants/public';

export default function () {
    // --- Scénario 1 : Page d'accueil (landing) ---
    group('Landing Page', function () {
        const res = http.get(`${BASE_URL}/?page=landing`);
        const success = check(res, {
            'landing status 200': (r) => r.status === 200,
            'landing body not empty': (r) => r.body.length > 0,
            'landing load < 1s': (r) => r.timings.duration < 1000,
        });
        errorRate.add(!success);
        pageLoadTime.add(res.timings.duration);
    });

    sleep(0.5);

    // --- Scénario 2 : Page de connexion ---
    group('Login Page', function () {
        const res = http.get(`${BASE_URL}/?page=login`);
        const success = check(res, {
            'login status 200': (r) => r.status === 200,
            'login has form': (r) => r.body.includes('password'),
            'login load < 1s': (r) => r.timings.duration < 1000,
        });
        errorRate.add(!success);
        pageLoadTime.add(res.timings.duration);
    });

    sleep(0.5);

    // --- Scénario 3 : Page vitrine publique (restaurant demo) ---
    group('Display Page (demo)', function () {
        const res = http.get(`${BASE_URL}/demo-menucraft`);
        const success = check(res, {
            'display status 200 or 302': (r) => [200, 302].includes(r.status),
            'display load < 2s': (r) => r.timings.duration < 2000,
        });
        errorRate.add(!success);
        pageLoadTime.add(res.timings.duration);
    });

    sleep(0.5);

    // --- Scénario 4 : Assets statiques (CSS/JS) ---
    group('Static Assets', function () {
        const assets = [
            '/assets/css/shared_variables.css',
            '/assets/css/display/carte.css',
            '/assets/css/display/banner.css',
        ];

        assets.forEach((asset) => {
            const res = http.get(`${BASE_URL}${asset}`);
            check(res, {
                [`${asset} status 200`]: (r) => r.status === 200,
                [`${asset} load < 500ms`]: (r) => r.timings.duration < 500,
            });
        });
    });

    sleep(0.5);

    // --- Scénario 5 : Tentative d'accès non-autorisé ---
    group('Unauthorized Access', function () {
        const protectedPages = ['dashboard', 'settings', 'carte', 'seed-demo'];

        protectedPages.forEach((page) => {
            const res = http.get(`${BASE_URL}/?page=${page}`, {
                redirects: 0,
            });
            check(res, {
                [`${page} redirects (302/303)`]: (r) => [302, 303].includes(r.status),
            });
        });
    });

    sleep(1);
}

// Résumé en fin de test
export function handleSummary(data) {
    const passed = data.root_group.checks.filter(c => c.passes > 0 && c.fails === 0).length;
    const total = data.root_group.checks.length;

    console.log(`\n========================================`);
    console.log(`  RÉSUMÉ DU TEST DE CHARGE`);
    console.log(`========================================`);
    console.log(`  Checks réussis : ${passed}/${total}`);
    console.log(`  Requêtes totales : ${data.metrics.http_reqs.values.count}`);
    console.log(`  Durée moyenne : ${Math.round(data.metrics.http_req_duration.values.avg)}ms`);
    console.log(`  P95 : ${Math.round(data.metrics.http_req_duration.values['p(95)'])}ms`);
    console.log(`  Taux d'erreur : ${(data.metrics.http_req_failed.values.rate * 100).toFixed(2)}%`);
    console.log(`========================================\n`);

    return {};
}
