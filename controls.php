<?php
// Get latest sensor data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sensorStmt = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
    $sensorData = $sensorStmt->fetch();
} catch(PDOException $e) {
    $sensorData = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Control - Smart Home IoT</title>
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
                <a href="index.php" class="nav-link">
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
                <a href="controls.php" class="nav-link active">
                    <i class="fas fa-sliders-h nav-icon"></i>
                    Controls
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1 class="header-title">Device Controls</h1>
            <p class="header-subtitle">Kontrol manual servo pintu, jemuran, dan monitoring LED otomatis</p>
        </div>

        <div class="content-body">
            <!-- Device Controls -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🎮 Manual Controls</h2>
                </div>
                <div class="card-body">
                    <div class="device-grid">
                        <!-- Servo Pintu -->
                        <div class="device-card">
                            <div class="device-header">
                                <div class="device-icon door">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div class="device-info">
                                    <h3>Servo Pintu</h3>
                                    <p>Status: <?= $sensorData ? $sensorData['status_pintu'] : 'N/A' ?></p>
                                </div>
                            </div>
                            <div class="device-controls">
                                <button class="btn btn-success" onclick="controlServo('pintu', 'open')">
                                    <i class="fas fa-unlock"></i> Buka Pintu
                                </button>
                                <button class="btn btn-danger" onclick="controlServo('pintu', 'close')">
                                    <i class="fas fa-lock"></i> Tutup Pintu
                                </button>
                            </div>
                            <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius); font-size: 0.875rem; color: var(--text-secondary);">
                                <i class="fas fa-info-circle"></i> 
                                Pintu otomatis tertutup 3 detik setelah dibuka via NFC
                            </div>
                        </div>
                        
                        <!-- Servo Jemuran -->
                        <div class="device-card">
                            <div class="device-header">
                                <div class="device-icon laundry">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <div class="device-info">
                                    <h3>Servo Jemuran</h3>
                                    <p>Status: <?= $sensorData ? $sensorData['status_jemuran'] : 'N/A' ?></p>
                                </div>
                            </div>
                            <div class="device-controls">
                                <button class="btn btn-success" onclick="controlServo('jemuran', 'open')">
                                    <i class="fas fa-sun"></i> Buka Jemuran
                                </button>
                                <button class="btn btn-danger" onclick="controlServo('jemuran', 'close')">
                                    <i class="fas fa-cloud-rain"></i> Tutup Jemuran
                                </button>
                            </div>
                            <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius); font-size: 0.875rem; color: var(--text-secondary);">
                                <i class="fas fa-info-circle"></i> 
                                Otomatis tertutup saat sensor hujan mendeteksi air
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- LED Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💡 LED Otomatis</h2>
                </div>
                <div class="card-body">
                    <div class="device-card">
                        <div class="device-header">
                            <div class="device-icon light">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="device-info">
                                <h3>Lampu LED Otomatis</h3>
                                <p>Status: <?= ($sensorData && $sensorData['led']) ? 'ON' : 'OFF' ?></p>
                            </div>
                        </div>
                        
                        <div class="grid grid-2" style="margin: 1rem 0;">
                            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius); text-align: center;">
                                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">Sensor Cahaya</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--accent-yellow);">
                                    <?= $sensorData ? $sensorData['cahaya'] : 'N/A' ?> lux
                                </div>
                            </div>
                            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius); text-align: center;">
                                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">Mode Kontrol</div>
                                <div style="font-size: 1.5rem; font-weight: 600; color: var(--accent-blue);">
                                    Otomatis
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius); font-size: 0.875rem; color: var(--text-secondary);">
                            <i class="fas fa-info-circle"></i> 
                            LED menyala otomatis saat cahaya < 500 lux. Tidak dapat dikontrol manual.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            <?php if ($sensorData): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Status Terkini</h2>
                </div>
                <div class="card-body">
                    <div class="sensor-grid">
                        <div class="sensor-card">
                            <div class="sensor-header">
                                <div class="sensor-icon">🚪</div>
                                <span class="sensor-status <?= $sensorData['status_pintu'] === 'TERBUKA' ? 'status-warning' : 'status-online' ?>">
                                    <?= $sensorData['status_pintu'] ?>
                                </span>
                            </div>
                            <div class="sensor-value"><?= $sensorData['servo_pintu'] ?>°</div>
                            <div class="sensor-label">Posisi Servo Pintu</div>
                            <div class="sensor-time"><?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                        </div>

                        <div class="sensor-card">
                            <div class="sensor-header">
                                <div class="sensor-icon">👕</div>
                                <span class="sensor-status <?= $sensorData['status_jemuran'] === 'TERBUKA' ? 'status-online' : 'status-warning' ?>">
                                    <?= $sensorData['status_jemuran'] ?>
                                </span>
                            </div>
                            <div class="sensor-value"><?= $sensorData['servo_jemuran'] ?>°</div>
                            <div class="sensor-label">Posisi Servo Jemuran</div>
                            <div class="sensor-time"><?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                        </div>

                        <div class="sensor-card">
                            <div class="sensor-header">
                                <div class="sensor-icon">💡</div>
                                <span class="sensor-status <?= $sensorData['led'] ? 'status-warning' : 'status-offline' ?>">
                                    <?= $sensorData['led'] ? 'ON' : 'OFF' ?>
                                </span>
                            </div>
                            <div class="sensor-value"><?= $sensorData['cahaya'] ?></div>
                            <div class="sensor-label">Sensor LDR</div>
                            <div class="sensor-time"><?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                        </div>

                        <div class="sensor-card">
                            <div class="sensor-header">
                                <div class="sensor-icon"><?= $sensorData['hujan'] ? '🌧️' : '☀️' ?></div>
                                <span class="sensor-status <?= $sensorData['hujan'] ? 'status-warning' : 'status-online' ?>">
                                    <?= $sensorData['hujan'] ? 'Hujan' : 'Kering' ?>
                                </span>
                            </div>
                            <div class="sensor-value"><?= $sensorData['suhu'] ?>°C</div>
                            <div class="sensor-label">Suhu & Cuaca</div>
                            <div class="sensor-time"><?= date('H:i:s', strtotime($sensorData['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Sensor Offline</strong><br>
                    Tidak dapat mengontrol perangkat. Periksa koneksi ESP32.
                </div>
            </div>
            <?php endif; ?>
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
        // Control servo function
        async function controlServo(device, action) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading
            button.innerHTML = '<div class="loading"></div> Processing...';
            button.disabled = true;
            
            try {
                const response = await fetch('control_servo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `device=${device}&action=${action}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    showNotification(`${device} berhasil ${action === 'open' ? 'dibuka' : 'ditutup'}`, 'success');
                    
                    // Update status immediately
                    setTimeout(updateControlStatus, 500);
                } else {
                    showNotification('Gagal mengontrol perangkat: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Error: ' + error.message, 'error');
            } finally {
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification ' + type;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i> 
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Real-time control updates
        async function updateControlStatus() {
            try {
                const response = await fetch('api/get_sensor_data.php');
                const data = await response.json();
                
                if (data.success && data.sensor) {
                    updateDeviceStatus(data.sensor);
                    updateCurrentStatus(data.sensor);
                }
            } catch (error) {
                console.log('Control update error:', error);
            }
        }
        
        function updateDeviceStatus(sensor) {
            // Update device info
            const deviceCards = document.querySelectorAll('.device-card');
            if (deviceCards.length >= 2) {
                deviceCards[0].querySelector('.device-info p').textContent = 'Status: ' + sensor.status_pintu;
                deviceCards[1].querySelector('.device-info p').textContent = 'Status: ' + sensor.status_jemuran;
            }
            
            // Update LED card
            const ledCard = document.querySelector('.led-card');
            if (ledCard) {
                const ledStatus = ledCard.querySelector('.led-status');
                const ledValue = ledCard.querySelectorAll('.value');
                
                if (ledStatus) {
                    ledStatus.textContent = 'Status: ' + (sensor.led == 1 ? 'ON' : 'OFF');
                    ledStatus.className = 'led-status ' + (sensor.led == 1 ? 'on' : 'off');
                }
                
                if (ledValue.length > 0) {
                    ledValue[0].textContent = sensor.cahaya + ' lux';
                }
            }
        }
        
        function updateCurrentStatus(sensor) {
            const statusCards = document.querySelectorAll('.sensor-card');
            if (statusCards.length >= 4) {
                // Update servo positions and status
                statusCards[0].querySelector('.sensor-value').textContent = sensor.servo_pintu + '°';
                statusCards[1].querySelector('.sensor-value').textContent = sensor.servo_jemuran + '°';
                statusCards[2].querySelector('.sensor-value').textContent = sensor.cahaya;
                statusCards[3].querySelector('.sensor-value').textContent = sensor.suhu + '°C';
                
                // Update timestamps
                const timeStr = new Date(sensor.created_at).toLocaleTimeString('id-ID');
                statusCards.forEach(card => {
                    const timeElement = card.querySelector('.sensor-time');
                    if (timeElement) timeElement.textContent = timeStr;
                });
            }
        }
        
        // Start real-time updates every 2 seconds
        setInterval(updateControlStatus, 2000);
        updateControlStatus();
        
        // Add slideOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>