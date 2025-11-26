<?php
// test_esp32_format.php - Test format yang sesuai dengan ESP32
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test ESP32 Format</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #ffff00; }
        button { padding: 15px 25px; margin: 10px; background: #333; color: #00ff00; border: 2px solid #00ff00; cursor: pointer; font-size: 16px; }
        button:hover { background: #00ff00; color: #000; }
        .log { background: #222; padding: 15px; margin: 15px 0; border-left: 4px solid #00ff00; }
        .status-box { background: #333; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 18px; }
    </style>
</head>
<body>
    <h1>🔧 Test ESP32 Servo Format</h1>
    
    <div class="log">
        <h3>📡 Current Servo Status (ESP32 Format)</h3>
        <div class="status-box">
            <strong>ESP32 akan baca:</strong> 
            <span id="servoStatus" style="color: #ffff00;">Loading...</span>
        </div>
        <small>Format: servo_pintu,servo_jemuran (0=tutup, 1=buka)</small>
    </div>
    
    <div class="log">
        <h3>🎮 Test Controls</h3>
        <button onclick="sendCommand('pintu', 'open')">🚪 BUKA PINTU (1,?)</button>
        <button onclick="sendCommand('pintu', 'close')">🔒 TUTUP PINTU (0,?)</button>
        <br>
        <button onclick="sendCommand('jemuran', 'open')">☀️ BUKA JEMURAN (?,1)</button>
        <button onclick="sendCommand('jemuran', 'close')">🌧️ TUTUP JEMURAN (?,0)</button>
        <br><br>
        <div id="commandResult"></div>
    </div>
    
    <div class="log">
        <h3>🤖 ESP32 Simulation</h3>
        <button onclick="simulateESP32Read()">📡 Simulate ESP32 Read</button>
        <div id="esp32Result"></div>
    </div>
    
    <div class="log">
        <h3>📋 Database Status</h3>
        <?php
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check servo_control table
            $stmt = $pdo->query("SELECT * FROM servo_control ORDER BY id DESC LIMIT 1");
            $servo = $stmt->fetch();
            
            if ($servo) {
                echo "<span class='success'>✅ Servo Control Table: EXISTS</span><br>";
                echo "<span class='info'>Current: Pintu={$servo['servo_pintu']}, Jemuran={$servo['servo_jemuran']}</span><br>";
                echo "<span class='info'>Updated: {$servo['updated_at']}</span><br>";
            } else {
                echo "<span class='error'>❌ No servo control data</span><br>";
            }
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="log">
        <h3>🔗 ESP32 Code Check</h3>
        <div style="color: #ffff00;">
            <strong>ESP32 code menggunakan URL:</strong><br>
            <code>const char* SERVO_URL = "http://10.218.20.188/smarthome/get_servo_command.php";</code><br><br>
            
            <strong>Tapi seharusnya:</strong><br>
            <code style="color: #00ff00;">const char* SERVO_URL = "http://10.218.20.188/smarthome/get_servo_status.php";</code><br><br>
            
            <strong>Atau buat file get_servo_command.php yang return format yang benar</strong>
        </div>
    </div>

    <script>
        async function loadServoStatus() {
            try {
                const response = await fetch('get_servo_status.php');
                const status = await response.text();
                document.getElementById('servoStatus').textContent = status;
            } catch (error) {
                document.getElementById('servoStatus').textContent = 'Error: ' + error.message;
            }
        }
        
        async function sendCommand(device, action) {
            try {
                const response = await fetch('control_servo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `device=${device}&action=${action}`
                });
                
                const result = await response.json();
                document.getElementById('commandResult').innerHTML = 
                    `<span class="${result.success ? 'success' : 'error'}">
                        ${result.success ? '✅' : '❌'} ${result.message}<br>
                        Servo Status: ${result.servo_status || 'N/A'}
                    </span><br>`;
                
                // Refresh status
                setTimeout(loadServoStatus, 500);
                
            } catch (error) {
                document.getElementById('commandResult').innerHTML = 
                    `<span class="error">❌ Error: ${error.message}</span><br>`;
            }
        }
        
        async function simulateESP32Read() {
            document.getElementById('esp32Result').innerHTML = 
                '<span class="info">🤖 ESP32 reading servo status...</span><br>';
                
            try {
                const response = await fetch('get_servo_status.php');
                const status = await response.text();
                
                document.getElementById('esp32Result').innerHTML = 
                    `<span class="success">📡 ESP32 received: <strong>${status}</strong></span><br>
                     <span class="info">Format: pintu,jemuran</span><br>`;
                     
            } catch (error) {
                document.getElementById('esp32Result').innerHTML = 
                    `<span class="error">❌ ESP32 Error: ${error.message}</span><br>`;
            }
        }
        
        // Load initial status
        loadServoStatus();
        
        // Auto refresh every 3 seconds
        setInterval(loadServoStatus, 3000);
    </script>
</body>
</html>