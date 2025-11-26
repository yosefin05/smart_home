<?php
// Get latest sensor data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sensorStmt = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
    $sensorData = $sensorStmt->fetch();
    
    $bmkgStmt = $pdo->query("SELECT * FROM bmkg_data ORDER BY created_at DESC LIMIT 1");
    $bmkgData = $bmkgStmt->fetch();
    
    // Get today's statistics
    $todayStats = $pdo->query("
        SELECT 
            COUNT(*) as total_records,
            AVG(suhu) as avg_temp,
            AVG(kelembapan) as avg_humidity,
            SUM(CASE WHEN hujan = 1 THEN 1 ELSE 0 END) as rain_detections
        FROM sensor_data 
        WHERE DATE(created_at) = CURDATE()
    ")->fetch();
} catch(PDOException $e) {
    $sensorData = null;
    $bmkgData = null;
    $todayStats = ['total_records' => 0, 'avg_temp' => 0, 'avg_humidity' => 0, 'rain_detections' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home IoT Dashboard</title>
    <link rel="stylesheet" href="assets/css/dark.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home nav-icon"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="sensors.php" class="nav-link">
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
            <h1 class="header-title">Smart Home Dashboard</h1>
            <p class="header-subtitle">Real-time monitoring dan kontrol sistem IoT rumah pintar</p>
        </div>

        <div class="content-body">
            <!-- Statistics Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?= $todayStats['total_records'] ?? 0 ?></span>
                    <span class="stat-label">Data Hari Ini</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= number_format($todayStats['avg_temp'] ?? 0, 1) ?>°C</span>
                    <span class="stat-label">Suhu Rata-rata</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= number_format($todayStats['avg_humidity'] ?? 0, 1) ?>%</span>
                    <span class="stat-label">Kelembapan Rata-rata</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $todayStats['rain_detections'] ?? 0 ?></span>
                    <span class="stat-label">Deteksi Hujan</span>
                </div>
            </div>

            <!-- Sensor Data -->
            <div class="sensor-grid">
                <?php if ($sensorData): ?>
                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">🌡️</div>
                        <span class="sensor-status status-online">Online</span>
                    </div>
                    <div class="sensor-value"><?= $sensorData['suhu'] ?>°C</div>
                    <div class="sensor-label">Suhu Ruangan</div>
                    <div class="sensor-time">DHT22 • <?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                </div>

                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">💧</div>
                        <span class="sensor-status status-online">Online</span>
                    </div>
                    <div class="sensor-value"><?= $sensorData['kelembapan'] ?>%</div>
                    <div class="sensor-label">Kelembapan Udara</div>
                    <div class="sensor-time">DHT22 • <?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                </div>

                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">💡</div>
                        <span class="sensor-status status-online">Online</span>
                    </div>
                    <div class="sensor-value"><?= $sensorData['cahaya'] ?></div>
                    <div class="sensor-label">Intensitas Cahaya</div>
                    <div class="sensor-time">LDR • <?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                </div>

                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon"><?= $sensorData['hujan'] ? '🌧️' : '☀️' ?></div>
                        <span class="sensor-status <?= $sensorData['hujan'] ? 'status-warning' : 'status-online' ?>">
                            <?= $sensorData['hujan'] ? 'Hujan' : 'Kering' ?>
                        </span>
                    </div>
                    <div class="sensor-value"><?= $sensorData['hujan'] ? 'Basah' : 'Kering' ?></div>
                    <div class="sensor-label">Status Cuaca</div>
                    <div class="sensor-time">Rain Sensor • <?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                </div>
                <?php else: ?>
                <div class="sensor-card">
                    <div class="sensor-header">
                        <div class="sensor-icon">⚠️</div>
                        <span class="sensor-status status-offline">Offline</span>
                    </div>
                    <div class="sensor-value">--</div>
                    <div class="sensor-label">Sensor Tidak Terhubung</div>
                    <div class="sensor-time">Periksa koneksi ESP32</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Device Controls -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🎛️ Status Perangkat</h2>
                </div>
                <div class="card-body">
                    <div class="device-grid">
                        <?php if ($sensorData): ?>
                        <div class="device-card">
                            <div class="device-header">
                                <div class="device-icon door">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div class="device-info">
                                    <h3>Servo Pintu</h3>
                                    <p>Status: <?= $sensorData['status_pintu'] ?></p>
                                </div>
                            </div>
                            <div class="device-controls">
                                <a href="controls.php" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> Kontrol
                                </a>
                            </div>
                        </div>

                        <div class="device-card">
                            <div class="device-header">
                                <div class="device-icon laundry">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <div class="device-info">
                                    <h3>Servo Jemuran</h3>
                                    <p>Status: <?= $sensorData['status_jemuran'] ?></p>
                                </div>
                            </div>
                            <div class="device-controls">
                                <a href="controls.php" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> Kontrol
                                </a>
                            </div>
                        </div>

                        <div class="device-card">
                            <div class="device-header">
                                <div class="device-icon light">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div class="device-info">
                                    <h3>LED Otomatis</h3>
                                    <p>Status: <?= $sensorData['led'] ? 'ON' : 'OFF' ?></p>
                                </div>
                            </div>
                            <div class="device-controls">
                                <span class="btn btn-outline" disabled>
                                    <i class="fas fa-magic"></i> Auto Mode
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Weather Information -->
            <?php if ($bmkgData): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🌤️ Cuaca BMKG</h2>
                    <a href="weather.php" class="btn btn-outline">Detail</a>
                </div>
                <div class="card-body">
                    <div class="weather-card">
                        <div class="weather-icon">
                            <?php
                            $desc = strtolower($bmkgData['deskripsi'] ?? '');
                            if (strpos($desc, 'hujan') !== false) echo '🌧️';
                            elseif (strpos($desc, 'berawan') !== false) echo '☁️';
                            elseif (strpos($desc, 'cerah') !== false) echo '☀️';
                            else echo '🌤️';
                            ?>
                        </div>
                        <div class="weather-info">
                            <div class="weather-temp"><?= $bmkgData['suhu'] ?? '--' ?>°C</div>
                            <div class="weather-desc"><?= $bmkgData['deskripsi'] ?? 'N/A' ?></div>
                            <div class="weather-location">📍 Indonesia</div>
                        </div>
                        <div class="weather-details">
                            <div class="weather-detail">
                                <i class="fas fa-tint"></i>
                                <span>Kelembapan: <?= $bmkgData['kelembapan'] ?? '--' ?>%</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-wind"></i>
                                <span>BMKG Data</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-clock"></i>
                                <span><?= $bmkgData ? date('d/m/Y H:i', strtotime($bmkgData['created_at'])) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- System Status -->
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Sistem Online</strong><br>
                    Semua sensor dan perangkat terhubung dengan baik. Auto-refresh setiap 30 detik.
                </div>
            </div>
        </div>

        <div class="footer">
            <div>
                <span>&copy; 2024 Smart Home IoT System</span>
            </div>
            <div class="footer-status">
                <div class="status-dot"></div>
                <span>System Online</span>
                <span id="last-update"></span>
            </div>
        </div>
    </div>

    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('last-update').textContent = now.toLocaleTimeString('id-ID');
        }
        
        updateTime();
        setInterval(updateTime, 1000);
        
        // Add connection status indicator
        let isOnline = true;
        function updateConnectionStatus() {
            const statusDot = document.querySelector('.status-dot');
            if (statusDot) {
                statusDot.style.background = isOnline ? 'var(--accent-green)' : 'var(--accent-red)';
            }
        }
        
        // Check connection every 10 seconds
        setInterval(async () => {
            try {
                const response = await fetch('api/ping.php');
                isOnline = response.ok;
            } catch (error) {
                isOnline = false;
            }
            updateConnectionStatus();
        }, 10000);
        
        // Real-time updates every 5 seconds
        async function updateSensorData() {
            try {
                const response = await fetch('api/get_sensor_data.php');
                const data = await response.json();
                
                if (data.success && data.sensor) {
                    // Update sensor values without page reload
                    updateSensorCards(data.sensor);
                    updateDeviceStatus(data.sensor);
                }
            } catch (error) {
                console.log('Update error:', error);
            }
        }
        
        function updateSensorCards(sensor) {
            const cards = document.querySelectorAll('.sensor-card');
            if (cards.length >= 4) {
                // Update temperature
                cards[0].querySelector('.sensor-value').textContent = sensor.suhu + '°C';
                cards[0].querySelector('.sensor-time').textContent = 'DHT22 • ' + new Date(sensor.created_at).toLocaleTimeString('id-ID');
                
                // Update humidity
                cards[1].querySelector('.sensor-value').textContent = sensor.kelembapan + '%';
                cards[1].querySelector('.sensor-time').textContent = 'DHT22 • ' + new Date(sensor.created_at).toLocaleTimeString('id-ID');
                
                // Update light
                cards[2].querySelector('.sensor-value').textContent = sensor.cahaya;
                cards[2].querySelector('.sensor-time').textContent = 'LDR • ' + new Date(sensor.created_at).toLocaleTimeString('id-ID');
                
                // Update rain
                const rainIcon = sensor.hujan == 1 ? '🌧️' : '☀️';
                const rainValue = sensor.hujan == 1 ? 'Basah' : 'Kering';
                const rainStatus = sensor.hujan == 1 ? 'status-warning' : 'status-online';
                
                cards[3].querySelector('.sensor-icon').textContent = rainIcon;
                cards[3].querySelector('.sensor-value').textContent = rainValue;
                cards[3].querySelector('.sensor-status').className = 'sensor-status ' + rainStatus;
                cards[3].querySelector('.sensor-time').textContent = 'Rain Sensor • ' + new Date(sensor.created_at).toLocaleTimeString('id-ID');
            }
        }
        
        function updateDeviceStatus(sensor) {
            const deviceCards = document.querySelectorAll('.device-card');
            if (deviceCards.length >= 3) {
                // Update pintu status
                deviceCards[0].querySelector('.device-info p').textContent = 'Status: ' + sensor.status_pintu;
                
                // Update jemuran status
                deviceCards[1].querySelector('.device-info p').textContent = 'Status: ' + sensor.status_jemuran;
                
                // Update LED status
                deviceCards[2].querySelector('.device-info p').textContent = 'Status: ' + (sensor.led == 1 ? 'ON' : 'OFF');
            }
        }
        
        // Start real-time updates
        setInterval(updateSensorData, 5000);
        updateSensorData();
    </script>
</body>
</html>