<?php
// test_servo_control.php - Test sistem kontrol servo
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Servo Control</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #ffff00; }
        button { padding: 10px 20px; margin: 5px; background: #333; color: #00ff00; border: 1px solid #00ff00; cursor: pointer; }
        button:hover { background: #00ff00; color: #000; }
        .log { background: #222; padding: 10px; margin: 10px 0; border-left: 3px solid #00ff00; }
    </style>
</head>
<body>
    <h1>🎛️ Test Servo Control System</h1>
    
    <div class="log">
        <h3>📋 Step 1: Update Database Structure</h3>
        <?php
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Add executed column
            try {
                $pdo->exec("ALTER TABLE device_control_log ADD COLUMN executed TINYINT(1) DEFAULT 0");
                echo "<span class='success'>✅ Added 'executed' column</span><br>";
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "<span class='info'>ℹ️ Column 'executed' already exists</span><br>";
                } else {
                    echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
                }
            }
            
            // Create web_commands table
            try {
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
                echo "<span class='error'>❌ Table error: " . $e->getMessage() . "</span><br>";
            }
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span><br>";
        }
        ?>
    </div>
    
    <div class="log">
        <h3>🎮 Step 2: Test Control Commands</h3>
        <button onclick="sendCommand('pintu', 'open')">🚪 Buka Pintu</button>
        <button onclick="sendCommand('pintu', 'close')">🔒 Tutup Pintu</button>
        <button onclick="sendCommand('jemuran', 'open')">☀️ Buka Jemuran</button>
        <button onclick="sendCommand('jemuran', 'close')">🌧️ Tutup Jemuran</button>
        <br><br>
        <div id="commandResult"></div>
    </div>
    
    <div class="log">
        <h3>📡 Step 3: Check Pending Commands</h3>
        <button onclick="checkCommands()">🔍 Check Commands</button>
        <button onclick="simulateESP32()">🤖 Simulate ESP32</button>
        <div id="commandCheck"></div>
    </div>
    
    <div class="log">
        <h3>📊 Step 4: Current Status</h3>
        <div id="currentStatus">
            <?php
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as pending FROM device_control_log WHERE executed = 0");
                $pending = $stmt->fetch()['pending'];
                echo "<span class='info'>Pending commands: {$pending}</span><br>";
                
                $stmt = $pdo->query("SELECT * FROM device_control_log ORDER BY created_at DESC LIMIT 5");
                $logs = $stmt->fetchAll();
                
                echo "<br><strong>Recent Commands:</strong><br>";
                foreach ($logs as $log) {
                    $status = $log['executed'] ? '✅' : '⏳';
                    echo "{$status} {$log['device_type']} -> {$log['action']} ({$log['control_source']}) - {$log['created_at']}<br>";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Status error: " . $e->getMessage() . "</span><br>";
            }
            ?>
        </div>
    </div>
    
    <div class="log">
        <h3>🔗 ESP32 Integration URLs</h3>
        <code>ESP32 kirim data: http://localhost/smarthome/save_sensor.php</code><br>
        <code>ESP32 ambil perintah: http://localhost/smarthome/get_commands.php</code><br>
        <code>Website kirim perintah: http://localhost/smarthome/control_servo.php</code><br>
    </div>

    <script>
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
                        ${result.success ? '✅' : '❌'} ${result.message}
                    </span><br>`;
                
                if (result.success) {
                    setTimeout(checkCommands, 500);
                }
            } catch (error) {
                document.getElementById('commandResult').innerHTML = 
                    `<span class="error">❌ Error: ${error.message}</span><br>`;
            }
        }
        
        async function checkCommands() {
            try {
                const response = await fetch('get_commands.php');
                const result = await response.text();
                
                document.getElementById('commandCheck').innerHTML = 
                    `<span class="info">📡 ESP32 Response: ${result}</span><br>`;
                    
            } catch (error) {
                document.getElementById('commandCheck').innerHTML = 
                    `<span class="error">❌ Check Error: ${error.message}</span><br>`;
            }
        }
        
        async function simulateESP32() {
            document.getElementById('commandCheck').innerHTML = 
                '<span class="info">🤖 Simulating ESP32...</span><br>';
                
            // Simulate ESP32 checking for commands
            for (let i = 0; i < 3; i++) {
                setTimeout(async () => {
                    const response = await fetch('get_commands.php');
                    const result = await response.text();
                    
                    document.getElementById('commandCheck').innerHTML += 
                        `<span class="success">ESP32 Check ${i+1}: ${result}</span><br>`;
                        
                    if (i === 2) {
                        setTimeout(() => location.reload(), 1000);
                    }
                }, i * 1000);
            }
        }
        
        // Auto refresh status every 5 seconds
        setInterval(() => {
            fetch('test_servo_control.php')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newStatus = doc.getElementById('currentStatus').innerHTML;
                    document.getElementById('currentStatus').innerHTML = newStatus;
                });
        }, 5000);
    </script>
</body>
</html>