<?php
// Get BMKG weather data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Latest BMKG data
    $bmkgStmt = $pdo->query("SELECT * FROM bmkg_data ORDER BY created_at DESC LIMIT 1");
    $bmkgData = $bmkgStmt->fetch();
    
    // Recent BMKG data for history
    $historyStmt = $pdo->query("
        SELECT * FROM bmkg_data 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $historyData = $historyStmt->fetchAll();
    
    // Get local sensor data for comparison
    $sensorStmt = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
    $sensorData = $sensorStmt->fetch();
    
} catch(PDOException $e) {
    $bmkgData = null;
    $historyData = [];
    $sensorData = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather BMKG - Smart Home IoT</title>
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
                <a href="weather.php" class="nav-link active">
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
            <h1 class="header-title">Weather BMKG</h1>
            <p class="header-subtitle">Data cuaca resmi dari Badan Meteorologi, Klimatologi, dan Geofisika</p>
        </div>

        <div class="content-body">
            <!-- Current Weather -->
            <?php if ($bmkgData): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🌤️ Cuaca Terkini</h2>
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
                            <div class="weather-location">📍 <?= $bmkgData['lokasi'] ?? 'Indonesia' ?></div>
                        </div>
                        <div class="weather-details">
                            <div class="weather-detail">
                                <i class="fas fa-tint"></i>
                                <span>Kelembapan: <?= $bmkgData['kelembapan'] ?? '--' ?>%</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-wind"></i>
                                <span>Angin: <?= $bmkgData['kecepatan_angin'] ?? '--' ?> km/h</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-eye"></i>
                                <span>Visibilitas: <?= $bmkgData['visibilitas'] ?? '--' ?> km</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-clock"></i>
                                <span><?= $bmkgData ? date('d/m/Y H:i', strtotime($bmkgData['created_at'])) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-cloud-exclamation"></i>
                <div>
                    <strong>Data Cuaca Tidak Tersedia</strong><br>
                    Belum ada data cuaca dari BMKG. Sistem akan mengambil data secara otomatis.
                </div>
            </div>
            <?php endif; ?>

            <!-- Comparison with Local Sensor -->
            <?php if ($bmkgData && $sensorData): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Perbandingan Data</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-3">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <h4 style="color: var(--text-secondary); margin-bottom: 1rem;">🌡️ Suhu</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>BMKG:</span> <strong><?= $bmkgData['suhu'] ?>°C</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Sensor Lokal:</span> <strong><?= $sensorData['suhu'] ?>°C</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: var(--accent-yellow);">
                                    <span>Selisih:</span> <strong><?= abs($bmkgData['suhu'] - $sensorData['suhu']) ?>°C</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <h4 style="color: var(--text-secondary); margin-bottom: 1rem;">💧 Kelembapan</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>BMKG:</span> <strong><?= $bmkgData['kelembapan'] ?>%</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Sensor Lokal:</span> <strong><?= $sensorData['kelembapan'] ?>%</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: var(--accent-yellow);">
                                    <span>Selisih:</span> <strong><?= abs($bmkgData['kelembapan'] - $sensorData['kelembapan']) ?>%</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <h4 style="color: var(--text-secondary); margin-bottom: 1rem;">🌧️ Kondisi Hujan</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>BMKG:</span> 
                                    <strong><?= strpos(strtolower($bmkgData['deskripsi']), 'hujan') !== false ? '🌧️ Hujan' : '☀️ Tidak Hujan' ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Sensor Lokal:</span> 
                                    <strong><?= $sensorData['hujan'] ? '🌧️ Terdeteksi' : '☀️ Kering' ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Weather History -->
            <?php if (!empty($historyData)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📅 Riwayat Cuaca BMKG</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Suhu</th>
                                    <th>Kelembapan</th>
                                    <th>Kondisi</th>
                                    <th>Angin</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyData as $row): ?>
                                <tr>
                                    <td><?= date('d/m H:i', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['suhu'] ?>°C</td>
                                    <td><?= $row['kelembapan'] ?>%</td>
                                    <td>
                                        <?php
                                        $desc = strtolower($row['deskripsi']);
                                        if (strpos($desc, 'hujan') !== false) echo '🌧️';
                                        elseif (strpos($desc, 'berawan') !== false) echo '☁️';
                                        elseif (strpos($desc, 'cerah') !== false) echo '☀️';
                                        else echo '🌤️';
                                        ?>
                                        <?= $row['deskripsi'] ?>
                                    </td>
                                    <td><?= $row['kecepatan_angin'] ?? '--' ?> km/h</td>
                                    <td><?= $row['lokasi'] ?? 'N/A' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Weather Alerts -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⚠️ Peringatan Cuaca</h2>
                </div>
                <div class="card-body">
                    <?php if ($bmkgData): ?>
                        <?php if (strpos(strtolower($bmkgData['deskripsi']), 'hujan') !== false): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-cloud-rain"></i>
                            <div>
                                <strong>Peringatan Hujan</strong><br>
                                BMKG memprediksi hujan. Jemuran akan otomatis tertutup jika sensor mendeteksi air.
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($bmkgData['suhu'] > 35): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-thermometer-full"></i>
                            <div>
                                <strong>Suhu Tinggi</strong><br>
                                Suhu mencapai <?= $bmkgData['suhu'] ?>°C. Pastikan ventilasi rumah baik.
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($bmkgData['kelembapan'] > 80): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-tint"></i>
                            <div>
                                <strong>Kelembapan Tinggi</strong><br>
                                Kelembapan <?= $bmkgData['kelembapan'] ?>%. Waspada kondisi lembab berlebih.
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!strpos(strtolower($bmkgData['deskripsi']), 'hujan') && !($bmkgData['suhu'] > 35) && !($bmkgData['kelembapan'] > 80)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Cuaca Normal</strong><br>
                                Kondisi cuaca dalam batas normal. Tidak ada peringatan khusus.
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Tidak Ada Peringatan</strong><br>
                            Belum ada data cuaca untuk menampilkan peringatan.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer">
            <div>
                <span>&copy; 2024 Smart Home IoT System | Data cuaca dari BMKG</span>
            </div>
            <div class="footer-status">
                <div class="status-dot"></div>
                <span>Auto-refresh 30s</span>
            </div>
        </div>
    </div>

    <script>
        // Real-time weather updates
        async function updateWeatherData() {
            try {
                const response = await fetch('api/get_sensor_data.php');
                const data = await response.json();
                
                if (data.success) {
                    if (data.bmkg) updateBMKGData(data.bmkg);
                    if (data.sensor && data.bmkg) updateComparisonData(data.bmkg, data.sensor);
                }
            } catch (error) {
                console.log('Weather update error:', error);
            }
        }
        
        function updateBMKGData(bmkg) {
            const tempElement = document.querySelector('.weather-temp');
            const descElement = document.querySelector('.weather-desc');
            
            if (tempElement) tempElement.textContent = (bmkg.suhu || '--') + '°C';
            if (descElement) descElement.textContent = bmkg.deskripsi || 'N/A';
        }
        
        function updateComparisonData(bmkg, sensor) {
            const comparisonCards = document.querySelectorAll('.grid-3 .card');
            if (comparisonCards.length >= 2) {
                // Update temperature comparison
                const tempCard = comparisonCards[0];
                const tempValues = tempCard.querySelectorAll('strong');
                if (tempValues.length >= 3) {
                    tempValues[0].textContent = bmkg.suhu + '°C';
                    tempValues[1].textContent = sensor.suhu + '°C';
                    tempValues[2].textContent = Math.abs(bmkg.suhu - sensor.suhu).toFixed(1) + '°C';
                }
                
                // Update humidity comparison
                const humCard = comparisonCards[1];
                const humValues = humCard.querySelectorAll('strong');
                if (humValues.length >= 3) {
                    humValues[0].textContent = bmkg.kelembapan + '%';
                    humValues[1].textContent = sensor.kelembapan + '%';
                    humValues[2].textContent = Math.abs(bmkg.kelembapan - sensor.kelembapan).toFixed(1) + '%';
                }
            }
        }
        
        // Start real-time updates every 10 seconds
        setInterval(updateWeatherData, 10000);
        updateWeatherData();
    </script>
</body>
</html>