/**
 * Statistiques avancées — Graphiques Chart.js + fetch API
 */
document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('stats-loader');
    const periodBtns = document.querySelectorAll('.period-btn');
    let currentDays = 30;
    let charts = {};

    // Couleurs adaptées au thème
    function getColors() {
        const isDark = document.body.classList.contains('dark-mode');
        return {
            primary: getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#d97706',
            primaryLight: isDark ? 'rgba(217, 119, 6, 0.25)' : 'rgba(217, 119, 6, 0.12)',
            blue: '#3b82f6',
            blueLight: isDark ? 'rgba(59, 130, 246, 0.25)' : 'rgba(59, 130, 246, 0.12)',
            green: '#10b981',
            purple: '#8b5cf6',
            red: '#ef4444',
            orange: '#f59e0b',
            pink: '#ec4899',
            cyan: '#06b6d4',
            text: getComputedStyle(document.documentElement).getPropertyValue('--color-text').trim() || '#1c1917',
            textMuted: getComputedStyle(document.documentElement).getPropertyValue('--color-text-muted').trim() || '#78716c',
            border: getComputedStyle(document.documentElement).getPropertyValue('--color-border').trim() || '#e7e5e4',
            bg: getComputedStyle(document.documentElement).getPropertyValue('--color-bg').trim() || '#fff',
        };
    }

    // Options Chart.js globales
    function getChartDefaults() {
        const c = getColors();
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: c.text, font: { size: 12 } }
                },
                tooltip: {
                    backgroundColor: c.bg,
                    titleColor: c.text,
                    bodyColor: c.textMuted,
                    borderColor: c.border,
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: {
                    ticks: { color: c.textMuted, font: { size: 11 } },
                    grid: { color: c.border, drawBorder: false },
                },
                y: {
                    ticks: { color: c.textMuted, font: { size: 11 }, beginAtZero: true },
                    grid: { color: c.border, drawBorder: false },
                }
            }
        };
    }

    // Sélecteur de période
    periodBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            periodBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentDays = parseInt(this.dataset.days);
            fetchStats(currentDays);
        });
    });

    // Charger les données
    function fetchStats(days) {
        loader.classList.add('visible');

        fetch('?page=stats-data&days=' + days)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    console.error('Stats error:', data.message);
                    loader.classList.remove('visible');
                    return;
                }
                updateKPIs(data);
                renderVisitsChart(data.visits_per_day);
                renderDevicesChart(data.devices);
                renderBrowsersChart(data.browsers);
                renderHoursChart(data.hours);
                renderWeekdaysChart(data.weekdays);
                renderReferrersTable(data.referrers);
                loader.classList.remove('visible');
            })
            .catch(err => {
                console.error('Stats fetch error:', err);
                loader.classList.remove('visible');
            });
    }

    // KPIs
    function updateKPIs(data) {
        document.getElementById('kpi-total-visits').textContent = formatNumber(data.total_visits);
        document.getElementById('kpi-unique-visitors').textContent = formatNumber(data.unique_visitors);

        const avgDaily = data.period > 0 ? Math.round(data.total_visits / data.period) : 0;
        document.getElementById('kpi-avg-daily').textContent = formatNumber(avgDaily);

        // Mobile %
        const totalDevices = (data.devices || []).reduce((s, d) => s + d.count, 0);
        const mobileCount = (data.devices || []).find(d => d.device_type === 'mobile');
        const mobilePct = totalDevices > 0 && mobileCount ? Math.round((mobileCount.count / totalDevices) * 100) : 0;
        document.getElementById('kpi-mobile-pct').textContent = mobilePct + '%';

        // Tendance
        const trendEl = document.getElementById('kpi-trend-visits');
        if (data.trend) {
            const change = data.trend.change;
            if (change > 0) {
                trendEl.className = 'kpi-trend trend-up';
                trendEl.textContent = '+' + change + '%';
            } else if (change < 0) {
                trendEl.className = 'kpi-trend trend-down';
                trendEl.textContent = change + '%';
            } else {
                trendEl.className = 'kpi-trend trend-neutral';
                trendEl.textContent = '0%';
            }
        }
    }

    // Graphique visites par jour
    function renderVisitsChart(visitsPerDay) {
        const c = getColors();
        const ctx = document.getElementById('chart-visits-per-day');
        if (charts.visits) charts.visits.destroy();

        const labels = visitsPerDay.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
        });

        charts.visits = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Visites totales',
                        data: visitsPerDay.map(d => d.total),
                        borderColor: c.primary,
                        backgroundColor: c.primaryLight,
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: visitsPerDay.length > 60 ? 0 : 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Visiteurs uniques',
                        data: visitsPerDay.map(d => d.unique),
                        borderColor: c.blue,
                        backgroundColor: c.blueLight,
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: visitsPerDay.length > 60 ? 0 : 3,
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                ...getChartDefaults(),
                plugins: {
                    ...getChartDefaults().plugins,
                    legend: {
                        ...getChartDefaults().plugins.legend,
                        position: 'top',
                    }
                },
                scales: {
                    ...getChartDefaults().scales,
                    x: {
                        ...getChartDefaults().scales.x,
                        ticks: {
                            ...getChartDefaults().scales.x.ticks,
                            maxTicksLimit: 10,
                        }
                    },
                    y: {
                        ...getChartDefaults().scales.y,
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // Graphique appareils (doughnut)
    function renderDevicesChart(devices) {
        const c = getColors();
        const ctx = document.getElementById('chart-devices');
        if (charts.devices) charts.devices.destroy();

        const deviceLabels = { desktop: 'Ordinateur', mobile: 'Mobile', tablet: 'Tablette' };
        const deviceColors = { desktop: c.blue, mobile: c.purple, tablet: c.green };

        charts.devices = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: devices.map(d => deviceLabels[d.device_type] || d.device_type),
                datasets: [{
                    data: devices.map(d => d.count),
                    backgroundColor: devices.map(d => deviceColors[d.device_type] || c.textMuted),
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: c.text, padding: 16, font: { size: 12 } }
                    },
                    tooltip: getChartDefaults().plugins.tooltip,
                }
            }
        });
    }

    // Graphique navigateurs (doughnut)
    function renderBrowsersChart(browsers) {
        const c = getColors();
        const ctx = document.getElementById('chart-browsers');
        if (charts.browsers) charts.browsers.destroy();

        const browserColors = [c.blue, c.primary, c.green, c.purple, c.red, c.orange, c.pink, c.cyan];

        charts.browsers = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: browsers.map(b => b.browser),
                datasets: [{
                    data: browsers.map(b => b.count),
                    backgroundColor: browsers.map((_, i) => browserColors[i % browserColors.length]),
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: c.text, padding: 16, font: { size: 12 } }
                    },
                    tooltip: getChartDefaults().plugins.tooltip,
                }
            }
        });
    }

    // Graphique heures de pointe (bar)
    function renderHoursChart(hours) {
        const c = getColors();
        const ctx = document.getElementById('chart-hours');
        if (charts.hours) charts.hours.destroy();

        charts.hours = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: hours.map(h => h.hour + 'h'),
                datasets: [{
                    label: 'Visites',
                    data: hours.map(h => h.count),
                    backgroundColor: c.primary,
                    borderRadius: 4,
                    maxBarThickness: 20,
                }]
            },
            options: {
                ...getChartDefaults(),
                plugins: {
                    ...getChartDefaults().plugins,
                    legend: { display: false },
                },
                scales: {
                    ...getChartDefaults().scales,
                    x: {
                        ...getChartDefaults().scales.x,
                        ticks: {
                            ...getChartDefaults().scales.x.ticks,
                            maxTicksLimit: 12,
                        }
                    },
                    y: {
                        ...getChartDefaults().scales.y,
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // Graphique jours de la semaine (bar)
    function renderWeekdaysChart(weekdays) {
        const c = getColors();
        const ctx = document.getElementById('chart-weekdays');
        if (charts.weekdays) charts.weekdays.destroy();

        const dayColors = weekdays.map((_, i) => {
            // Week-end en couleur différente
            return (i === 0 || i === 6) ? c.primary : c.blue;
        });

        charts.weekdays = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: weekdays.map(d => d.day.substring(0, 3)),
                datasets: [{
                    label: 'Visites',
                    data: weekdays.map(d => d.count),
                    backgroundColor: dayColors,
                    borderRadius: 4,
                    maxBarThickness: 40,
                }]
            },
            options: {
                ...getChartDefaults(),
                plugins: {
                    ...getChartDefaults().plugins,
                    legend: { display: false },
                },
                scales: {
                    ...getChartDefaults().scales,
                    y: {
                        ...getChartDefaults().scales.y,
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // Table des référents
    function renderReferrersTable(referrers) {
        const container = document.getElementById('referrers-table');
        if (!referrers || referrers.length === 0) {
            container.innerHTML = '<div class="referrers-empty"><i class="fas fa-info-circle"></i> Aucune donnée de source de trafic disponible.</div>';
            return;
        }

        const maxCount = Math.max(...referrers.map(r => r.count));
        let html = '';
        referrers.forEach(r => {
            const pct = maxCount > 0 ? Math.round((r.count / maxCount) * 100) : 0;
            const icon = r.source === 'Direct' ? 'fa-arrow-right' : 'fa-external-link-alt';
            html += `
                <div class="referrer-row">
                    <div class="referrer-source">
                        <i class="fas ${icon}"></i>
                        <span>${escapeHtml(r.source)}</span>
                    </div>
                    <div class="referrer-bar-wrapper">
                        <div class="referrer-bar">
                            <div class="referrer-bar-fill" style="width: ${pct}%"></div>
                        </div>
                        <span class="referrer-count">${r.count}</span>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    // Helpers
    function formatNumber(n) {
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
        return String(n);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Écouter le changement de dark mode pour re-render les graphiques
    const darkModeObserver = new MutationObserver(() => {
        fetchStats(currentDays);
    });
    darkModeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    // Lancement initial
    fetchStats(currentDays);
});
