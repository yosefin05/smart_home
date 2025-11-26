<?php
// setup_complete.php - Setup lengkap Smart Home
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Home Setup</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #ffff00; }
        .step { margin: 10px 0; padding: 10px; border-left: 3px solid #00ff00; }
    </style>
</head>
<body>
    <h1>🏠 Smart Home IoT Setup</h1>
    
    <div class="step">
        <h3>📊 Step 1: Database Check</h3>
        <?php
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<span class='success'>✅ Database connection: OK</span><br>";
            
            // Check tables
            $tables = ['sensor_data', 'bmkg_data', 'device_control_log'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "<span class='success'>✅ Table '$table': EXISTS</span><br>";
                } else {
                    echo "<span class='error'>❌ Table '$table': NOT FOUND</span><br>";
                }
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>🌤️ Step 2: Fetch BMKG Data</h3>
        <?php
        try {
            // Fetch BMKG data
            $bmkgUrl = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=3515171006";
            $ch = curl_init($bmkgUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if ($data && isset($data['data'][0]['cuaca'][0][0])) {
                    $cuaca = $data['data'][0]['cuaca'][0][0];
                    $suhu = $cuaca['t'] ?? null;
                    $kelembapan = $cuaca['hu'] ?? null;
                    $deskripsi = $cuaca['weather_desc'] ?? null;
                    
                    // Save to database
                    $stmt = $pdo->prepare("
                        INSERT INTO bmkg_data (adm4_code, waktu_data, suhu, kelembapan, deskripsi, raw_json, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute(['3515171006', date('Y-m-d H:i:s'), $suhu, $kelembapan, $deskripsi, json_encode($data)]);
                    
                    echo "<span class='success'>✅ BMKG data fetched and saved</span><br>";
                    echo "<span class='info'>📊 Suhu: {$suhu}°C, Kelembapan: {$kelembapan}%, Cuaca: {$deskripsi}</span><br>";
                } else {
                    echo "<span class='error'>❌ Invalid BMKG data format</span><br>";
                }
            } else {
                echo "<span class='error'>❌ Failed to fetch BMKG data (HTTP: $httpCode)</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ BMKG fetch error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>📡 Step 3: Generate Sensor Data</h3>
        <?php
        try {
            // Generate dummy sensor data
            for ($i = 0; $i < 5; $i++) {
                $suhu = rand(25, 35) + (rand(0, 9) / 10);
                $kelembapan = rand(60, 90);
                $cahaya = rand(100, 1000);
                $hujan = rand(0, 1);
                $servo_jemuran = $hujan ? 0 : 90;
                $servo_pintu = 0;
                $led = $cahaya < 500 ? 1 : 0;
                $status_jemuran = $hujan ? 'TERTUTUP' : 'TERBUKA';
                $status_pintu = 'TERTUTUP';
                $waktu = date('Y-m-d H:i:s', strtotime("-{$i} minutes"));
                
                $stmt = $pdo->prepare("
                    INSERT INTO sensor_data 
                    (suhu, kelembapan, cahaya, hujan, servo_jemuran, servo_pintu, led, status_jemuran, status_pintu, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$suhu, $kelembapan, $cahaya, $hujan, $servo_jemuran, $servo_pintu, $led, $status_jemuran, $status_pintu, $waktu]);
            }
            echo "<span class='success'>✅ 5 sensor data records generated</span><br>";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Sensor data error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>🎛️ Step 4: Test Device Control</h3>
        <?php
        try {
            // Test device control log
            $stmt = $pdo->prepare("INSERT INTO device_control_log (device_type, action, control_source, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute(['pintu', 'test', 'SETUP']);
            echo "<span class='success'>✅ Device control system: OK</span><br>";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Device control error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>🚀 Step 5: Final Check</h3>
        <?php
        try {
            // Count records
            $sensorCount = $pdo->query("SELECT COUNT(*) FROM sensor_data")->fetchColumn();
            $bmkgCount = $pdo->query("SELECT COUNT(*) FROM bmkg_data")->fetchColumn();
            $controlCount = $pdo->query("SELECT COUNT(*) FROM device_control_log")->fetchColumn();
            
            echo "<span class='success'>✅ Sensor data: {$sensorCount} records</span><br>";
            echo "<span class='success'>✅ BMKG data: {$bmkgCount} records</span><br>";
            echo "<span class='success'>✅ Control logs: {$controlCount} records</span><br>";
            
            if ($sensorCount > 0 && $bmkgCount > 0) {
                echo "<br><span class='success'>🎉 SETUP COMPLETE! Smart Home ready to use!</span><br>";
                echo "<br><strong>Access your Smart Home:</strong><br>";
                echo "<a href='index.php' style='color: #00ffff;'>🏠 Dashboard</a> | ";
                echo "<a href='sensors.php' style='color: #00ffff;'>📊 Sensors</a> | ";
                echo "<a href='weather.php' style='color: #00ffff;'>🌤️ Weather</a> | ";
                echo "<a href='controls.php' style='color: #00ffff;'>🎛️ Controls</a><br>";
            } else {
                echo "<br><span class='error'>❌ Setup incomplete. Check errors above.</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Final check error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>📝 ESP32 Integration</h3>
        <span class='info'>📡 ESP32 dapat mengirim data ke:</span><br>
        <code>http://localhost/smarthome/save_sensor.php?suhu=28.5&kelembapan=75&cahaya=300&hujan=0&servo_jemuran=90&servo_pintu=0&led=1&status_jemuran=TERBUKA&status_pintu=TERTUTUP</code><br><br>
        <span class='info'>🌐 BMKG data auto-fetch:</span><br>
        <code>http://localhost/smarthome/fetch_bmkg.php</code><br>
    </div>
</body>
</html>