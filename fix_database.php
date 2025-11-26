<?php
// fix_database.php - Fix semua masalah database
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Database</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #ffff00; }
        .step { margin: 15px 0; padding: 15px; border-left: 3px solid #00ff00; background: #222; }
    </style>
</head>
<body>
    <h1>🔧 Fix Smart Home Database</h1>
    
    <div class="step">
        <h3>📊 Step 1: Create Missing Tables</h3>
        <?php
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<span class='success'>✅ Database connected</span><br>";
            
            // Create device_control_log
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS device_control_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    device_type VARCHAR(50) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    control_source VARCHAR(20) DEFAULT 'WEB',
                    executed TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "<span class='success'>✅ Table 'device_control_log' ready</span><br>";
            
            // Create web_commands
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS web_commands (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    device_type VARCHAR(50) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    executed TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "<span class='success'>✅ Table 'web_commands' ready</span><br>";
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>🧪 Step 2: Test Control System</h3>
        <button onclick="testControl('pintu', 'open')" style="padding: 10px; margin: 5px; background: #333; color: #00ff00; border: 1px solid #00ff00; cursor: pointer;">
            🚪 Test Buka Pintu
        </button>
        <button onclick="testControl('jemuran', 'close')" style="padding: 10px; margin: 5px; background: #333; color: #00ff00; border: 1px solid #00ff00; cursor: pointer;">
            🌧️ Test Tutup Jemuran
        </button>
        <div id="testResult"></div>
    </div>
    
    <div class="step">
        <h3>📡 Step 3: ESP32 Command Check</h3>
        <button onclick="checkESP32Commands()" style="padding: 10px; margin: 5px; background: #333; color: #00ff00; border: 1px solid #00ff00; cursor: pointer;">
            🤖 Check ESP32 Commands
        </button>
        <div id="esp32Result"></div>
    </div>
    
    <div class="step">
        <h3>📋 Step 4: Current Status</h3>
        <?php
        try {
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<span class='info'>📊 Available tables:</span><br>";
            foreach ($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "   - $table ($count records)<br>";
            }
            
            echo "<br><span class='info'>🎛️ Recent control commands:</span><br>";
            $stmt = $pdo->query("SELECT * FROM device_control_log ORDER BY created_at DESC LIMIT 3");
            $commands = $stmt->fetchAll();
            
            if (empty($commands)) {
                echo "   No commands yet<br>";
            } else {
                foreach ($commands as $cmd) {
                    $status = $cmd['executed'] ? '✅' : '⏳';
                    echo "   $status {$cmd['device_type']} -> {$cmd['action']} ({$cmd['created_at']})<br>";
                }
            }
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Status error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="step">
        <h3>🚀 Step 5: Ready to Use</h3>
        <span class='success'>✅ Database fixed and ready!</span><br><br>
        <strong>Access your Smart Home:</strong><br>
        <a href="index.php" style="color: #00ffff;">🏠 Dashboard</a> | 
        <a href="controls.php" style="color: #00ffff;">🎛️ Controls</a> | 
        <a href="sensors.php" style="color: #00ffff;">📊 Sensors</a> | 
        <a href="weather.php" style="color: #00ffff;">🌤️ Weather</a><br><br>
        
        <strong>ESP32 URLs:</strong><br>
        <code>Save data: http://localhost/smarthome/save_sensor.php</code><br>
        <code>Get commands: http://localhost/smarthome/get_commands.php</code><br>
    </div>

    <script>
        async function testControl(device, action) {
            try {
                const response = await fetch('control_servo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `device=${device}&action=${action}`
                });
                
                const result = await response.json();
                document.getElementById('testResult').innerHTML = 
                    `<span class="${result.success ? 'success' : 'error'}">
                        ${result.success ? '✅' : '❌'} ${result.message}
                    </span><br>`;
                    
            } catch (error) {
                document.getElementById('testResult').innerHTML = 
                    `<span class="error">❌ Test Error: ${error.message}</span><br>`;
            }
        }
        
        async function checkESP32Commands() {
            try {
                const response = await fetch('get_commands.php');
                const result = await response.text();
                
                document.getElementById('esp32Result').innerHTML = 
                    `<span class="info">📡 ESP32 would receive: ${result}</span><br>`;
                    
            } catch (error) {
                document.getElementById('esp32Result').innerHTML = 
                    `<span class="error">❌ ESP32 Error: ${error.message}</span><br>`;
            }
        }
    </script>
</body>
</html>