<div class="wrap user-tracking-dashboard">
    <h1>User Tracking Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Sessions</h3>
            <p><?php echo number_format($stats['total_sessions']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Today's Sessions</h3>
            <p><?php echo number_format($stats['today_sessions']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Fraud Attempts</h3>
            <p><?php echo number_format($stats['fraud_attempts']); ?></p>
        </div>
    </div>

    <div class="chart-container">
        <h2>Sessions Over Time</h2>
        <canvas id="sessionsChart" height="400"></canvas>
    </div>

    <div class="top-countries">
        <h2>Top Countries</h2>
        <ul>
            <?php foreach ($stats['top_countries'] as $country): ?>
                <li>
                    <span class="country-name"><?php echo esc_html($country->country ?: 'Unknown'); ?></span>
                    <span class="country-count"><?php echo number_format($country->count); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const ctx = document.getElementById('sessionsChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return "'" . esc_js(date('M j', strtotime($item->date))) . "'"; }, $chart_data)); ?>],
            datasets: [{
                label: 'Sessions',
                data: [<?php echo implode(',', array_map(function($item) { return $item->count; }, $chart_data)); ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>