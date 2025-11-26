<?php
// Get sensor data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Latest data
    $latestStmt = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
    $latestData = $latestStmt->fetch();
    
    // Recent data for chart (last 24 hours)
    $chartStmt = $pdo->query("
        SELECT suhu, kelembapan, cahaya, hujan, 
               DATE_FORMAT(created_at, '%H:%i') as time_label,
               created_at
        FROM sensor_data 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $chartData = $chartStmt->fetchAll();
    
    // Today's statistics
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_readings,
            MIN(suhu) as min_temp, MAX(suhu) as max_temp, AVG(suhu) as avg_temp,
            MIN(kelembapan) as min_humidity, MAX(kelembapan) as max_humidity, AVG(kelembapan) as avg_humidity,
            MIN(cahaya) as min_light, MAX(cahaya) as max_light, AVG(cahaya) as avg_light,
            SUM(CASE WHEN hujan = 1 THEN 1 ELSE 0 END) as rain_count
        FROM sensor_data 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stats = $statsStmt->fetch();
    
} catch(PDOException $e) {
    $latestData = null;
    $chartData = [];
    $stats = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Data - Smart Home IoT</title>
    <link rel="stylesheet" href="assets/css/dark.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fas fa-microchip"></i>
                <span>Smart Home</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home nav-icon"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="sensors.php" class="nav-link active">
                    <i class="fas fa-thermometer-half nav-icon"></i>
                    Sensors
                </a>
            </div>
            <div class="nav-item">
                <a href="weather.php" class="nav-link">
                    <i class="fas fa-cloud-sun nav-icon"></i>
                    Weather
                </a>
            </div>
            <div class="nav-item">
                <a href="controls.php" class="nav-link">
                    <i class="fas fa-sliders-h nav-icon"></i>
                    Controls
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1 class="header-title">Sensor Monitoring</h1>
            <p class="header-subtitle">Real-time data dari sensor DHT22, LDR, dan Rain Sensor</p>
        </div>

        <div class="content-body">
            <!-- Current Readings -->
            <?php if ($latestData): ?>
            <div class="sensor-grid">
                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">🌡️</div>
                        <span class="sensor-status status-online">Live</span>
                    </div>
                    <div class="sensor-value"><?= $latestData['suhu'] ?>°C</div>
                    <div class="sensor-label">Suhu DHT22</div>
                    <div class="sensor-time"><?= date('H:i:s', strtotime($latestData['created_at'])) ?></div>
                </div>
                
                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">💧</div>
                        <span class="sensor-status status-online">Live</span>
                    </div>
                    <div class="sensor-value"><?= $latestData['kelembapan'] ?>%</div>
                    <div class="sensor-label">Kelembapan DHT22</div>
                    <div class="sensor-time"><?= date('H:i:s', strtotime($latestData['created_at'])) ?></div>
                </div>
                
                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">💡</div>
                        <span class="sensor-status status-online">Live</span>
                    </div>
                    <div class="sensor-value"><?= $latestData['cahaya'] ?></div>
                    <div class="sensor-label">Sensor LDR</div>
                    <div class="sensor-time"><?= date('H:i:s', strtotime($latestData['created_at'])) ?></div>
                </div>
                
                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon"><?= $latestData['hujan'] ? '🌧️' : '☀️' ?></div>
                        <span class="sensor-status <?= $latestData['hujan'] ? 'status-warning' : 'status-online' ?>">
                            <?= $latestData['hujan'] ? 'Basah' : 'Kering' ?>
                        </span>
                    </div>
                    <div class="sensor-value"><?= $latestData['hujan'] ? 'Hujan' : 'Kering' ?></div>
                    <div class="sensor-label">Rain Sensor</div>
                    <div class="sensor-time"><?= date('H:i:s', strtotime($latestData['created_at'])) ?></div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Sensor Offline</strong><br>
                    Tidak ada data sensor. Periksa koneksi ESP32 dan database.
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <?php if ($stats && $stats['total_readings'] > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Statistik Hari Ini</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-number"><?= $stats['total_readings'] ?></span>
                            <span class="stat-label">Total Pembacaan</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?= number_format($stats['avg_temp'], 1) ?>°C</span>
                            <span class="stat-label">Suhu Rata-rata</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?= number_format($stats['avg_humidity'], 1) ?>%</span>
                            <span class="stat-label">Kelembapan Rata-rata</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?= $stats['rain_count'] ?></span>
                            <span class="stat-label">Deteksi Hujan</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-3" style="margin-top: 2rem;">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <h4 style="color: var(--text-secondary); margin-bottom: 1rem;">Suhu (°C)</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Min:</span> <strong><?= number_format($stats['min_temp'], 1) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Max:</span> <strong><?= number_format($stats['max_temp'], 1) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Avg:</span> <strong><?= number_format($stats['avg_temp'], 1) ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <h4 style="color: var(--text-secondary); margin-bottom: 1rem;">Kelembapan (%)</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Min:</span> <strong><?= number_format($stats['min_humidity'], 1) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Max:</span> <strong><?= number_format($stats['max_humidity'], 1) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Avg:</span> <strong><?= number_format($stats['avg_humidity'], 1) ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <h4 style="color: var(--text-secondary); margin-bottom: 1rem;">Cahaya</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Min:</span> <strong><?= number_format($stats['min_light']) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Max:</span> <strong><?= number_format($stats['max_light']) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Avg:</span> <strong><?= number_format($stats['avg_light']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Charts -->
            <?php if (!empty($chartData)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📈 Grafik Sensor (24 Jam Terakhir)</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="sensorChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Data Table -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🕒 Data Terbaru</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($chartData)): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Suhu (°C)</th>
                                    <th>Kelembapan (%)</th>
                                    <th>Cahaya</th>
                                    <th>Hujan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($chartData, 0, 10) as $row): ?>
                                <tr>
                                    <td><?= date('H:i:s', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['suhu'] ?></td>
                                    <td><?= $row['kelembapan'] ?></td>
                                    <td><?= $row['cahaya'] ?></td>
                                    <td><?= $row['hujan'] ? '🌧️ Basah' : '☀️ Kering' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Belum Ada Data</strong><br>
                            Data sensor akan muncul setelah ESP32 mengirim data pertama.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer">
            <div>
                <span>&copy; 2024 Smart Home IoT System</span>
            </div>
            <div class="footer-status">
                <div class="status-dot"></div>
                <span>Auto-refresh 30s</span>
            </div>
        </div>
    </div>

    <script>
        <?php if (!empty($chartData)): ?>
        // Prepare chart data
        const chartData = <?= json_encode(array_reverse($chartData)) ?>;
        
        const ctx = document.getElementById('sensorChart').getContext('2d');
        window.sensorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.time_label),
                datasets: [{
                    label: 'Suhu (°C)',
                    data: chartData.map(d => d.suhu),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Kelembapan (%)',
                    data: chartData.map(d => d.kelembapan),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Cahaya',
                    data: chartData.map(d => d.cahaya),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Sensor Data Trends',
                        color: '#f8fafc'
                    },
                    legend: {
                        labels: {
                            color: '#cbd5e1'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#64748b' },
                        grid: { color: '#475569' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#64748b' },
                        grid: { color: '#475569' }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Real-time sensor updates
        async function updateSensorData() {
            try {
                const response = await fetch('../api/get_sensor_data.php');
                const data = await response.json();
                
                if (data.success && data.sensor) {
                    updateSensorCards(data.sensor);
                    updateStatistics(data.sensor);
                }
            } catch (error) {
                console.log('Update error:', error);
            }
        }
        
        function updateSensorCards(sensor) {
            const cards = document.querySelectorAll('.sensor-card');
            if (cards.length >= 4) {
                // Update values
                cards[0].querySelector('.sensor-value').textContent = sensor.suhu + '°C';
                cards[1].querySelector('.sensor-value').textContent = sensor.kelembapan + '%';
                cards[2].querySelector('.sensor-value').textContent = sensor.cahaya;
                
                const rainStatus = sensor.hujan == 1 ? 'Hujan' : 'Kering';
                const rainClass = sensor.hujan == 1 ? 'status-warning' : 'status-online';
                cards[3].querySelector('.sensor-value').textContent = rainStatus;
                cards[3].querySelector('.sensor-status').className = 'sensor-status ' + rainClass;
                
                // Update timestamps
                const timeStr = new Date(sensor.created_at).toLocaleTimeString('id-ID');
                cards[0].querySelector('.sensor-time').textContent = timeStr;
                cards[1].querySelector('.sensor-time').textContent = timeStr;
                cards[2].querySelector('.sensor-time').textContent = timeStr;
                cards[3].querySelector('.sensor-time').textContent = timeStr;
            }
        }
        
        function updateStatistics(sensor) {
            // Update chart if exists
            if (typeof Chart !== 'undefined' && window.sensorChart) {
                // Add new data point to chart
                const now = new Date();
                const timeLabel = now.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                
                window.sensorChart.data.labels.push(timeLabel);
                window.sensorChart.data.datasets[0].data.push(sensor.suhu);
                window.sensorChart.data.datasets[1].data.push(sensor.kelembapan);
                window.sensorChart.data.datasets[2].data.push(sensor.cahaya);
                
                // Keep only last 20 points
                if (window.sensorChart.data.labels.length > 20) {
                    window.sensorChart.data.labels.shift();
                    window.sensorChart.data.datasets.forEach(dataset => dataset.data.shift());
                }
                
                window.sensorChart.update('none');
            }
        }
        
        // Start real-time updates every 3 seconds
        setInterval(updateSensorData, 3000);
        updateSensorData();
    </script>
</body>
</html>