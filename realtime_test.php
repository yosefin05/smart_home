<?php
// realtime_test.php - Test real-time updates
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Real-time Test</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #ffff00; }
        .data-box { background: #222; padding: 15px; margin: 10px 0; border-left: 4px solid #00ff00; }
        .timestamp { color: #888; font-size: 12px; }
        button { padding: 10px 20px; margin: 5px; background: #333; color: #00ff00; border: 1px solid #00ff00; cursor: pointer; }
        button:hover { background: #00ff00; color: #000; }
    </style>
</head>
<body>
    <h1>🔄 Real-time Updates Test</h1>
    
    <div class="data-box">
        <h3>📊 Live Sensor Data</h3>
        <div id="sensorData">Loading...</div>
        <div class="timestamp" id="sensorTime"></div>
    </div>
    
    <div class="data-box">
        <h3>🌤️ Live Weather Data</h3>
        <div id="weatherData">Loading...</div>
        <div class="timestamp" id="weatherTime"></div>
    </div>
    
    <div class="data-box">
        <h3>🎛️ Live Servo Status</h3>
        <div id="servoData">Loading...</div>
        <div class="timestamp" id="servoTime"></div>
    </div>
    
    <div class="data-box">
        <h3>🎮 Test Controls</h3>
        <button onclick="testControl('pintu', 'open')">🚪 Buka Pintu</button>
        <button onclick="testControl('pintu', 'close')">🔒 Tutup Pintu</button>
        <button onclick="testControl('jemuran', 'open')">☀️ Buka Jemuran</button>
        <button onclick="testControl('jemuran', 'close')">🌧️ Tutup Jemuran</button>
        <div id="controlResult"></div>
    </div>
    
    <div class="data-box">
        <h3>📈 Update Statistics</h3>
        <div>Updates: <span id="updateCount">0</span></div>
        <div>Errors: <span id="errorCount">0</span></div>
        <div>Last Update: <span id="lastUpdate">Never</span></div>
        <button onclick="toggleUpdates()">⏸️ Pause Updates</button>
    </div>

    <script>
        let updateCount = 0;
        let errorCount = 0;
        let updatesEnabled = true;
        
        async function fetchRealTimeData() {
            if (!updatesEnabled) return;
            
            try {
                const response = await fetch('api/get_sensor_data.php');
                const data = await response.json();
                
                if (data.success) {
                    updateCount++;
                    document.getElementById('updateCount').textContent = updateCount;
                    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                    
                    // Update sensor data
                    if (data.sensor) {
                        document.getElementById('sensorData').innerHTML = `
                            <span class="success">🌡️ Suhu: ${data.sensor.suhu}°C</span><br>
                            <span class="success">💧 Kelembapan: ${data.sensor.kelembapan}%</span><br>
                            <span class="success">💡 Cahaya: ${data.sensor.cahaya}</span><br>
                            <span class="success">🌧️ Hujan: ${data.sensor.hujan == 1 ? 'Basah' : 'Kering'}</span><br>
                            <span class="success">🚪 Pintu: ${data.sensor.status_pintu}</span><br>
                            <span class="success">🏠 Jemuran: ${data.sensor.status_jemuran}</span><br>
                            <span class="success">💡 LED: ${data.sensor.led == 1 ? 'ON' : 'OFF'}</span>
                        `;
                        document.getElementById('sensorTime').textContent = 'Updated: ' + new Date(data.sensor.created_at).toLocaleString();
                    }
                    
                    // Update weather data
                    if (data.bmkg) {
                        document.getElementById('weatherData').innerHTML = `
                            <span class="info">🌡️ BMKG Suhu: ${data.bmkg.suhu}°C</span><br>
                            <span class="info">💧 BMKG Kelembapan: ${data.bmkg.kelembapan}%</span><br>
                            <span class="info">🌤️ Kondisi: ${data.bmkg.deskripsi}</span>
                        `;
                        document.getElementById('weatherTime').textContent = 'Updated: ' + new Date(data.bmkg.created_at).toLocaleString();
                    }
                    
                    // Update servo data
                    if (data.servo) {
                        document.getElementById('servoData').innerHTML = `
                            <span class="info">🚪 Servo Pintu: ${data.servo.servo_pintu == 1 ? 'BUKA' : 'TUTUP'}</span><br>
                            <span class="info">🏠 Servo Jemuran: ${data.servo.servo_jemuran == 1 ? 'BUKA' : 'TUTUP'}</span>
                        `;
                        document.getElementById('servoTime').textContent = 'Updated: ' + new Date(data.servo.updated_at).toLocaleString();
                    }
                    
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
                
            } catch (error) {
                errorCount++;
                document.getElementById('errorCount').textContent = errorCount;
                console.error('Real-time update error:', error);
                
                document.getElementById('sensorData').innerHTML = `<span class="error">❌ Error: ${error.message}</span>`;
            }
        }
        
        async function testControl(device, action) {
            try {
                const response = await fetch('control_servo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `device=${device}&action=${action}`
                });
                
                const result = await response.json();
                document.getElementById('controlResult').innerHTML = 
                    `<span class="${result.success ? 'success' : 'error'}">
                        ${result.success ? '✅' : '❌'} ${result.message}
                    </span><br>`;
                
                // Force update after control
                if (result.success) {
                    setTimeout(fetchRealTimeData, 500);
                }
                
            } catch (error) {
                document.getElementById('controlResult').innerHTML = 
                    `<span class="error">❌ Control Error: ${error.message}</span><br>`;
            }
        }
        
        function toggleUpdates() {
            updatesEnabled = !updatesEnabled;
            const button = document.querySelector('button[onclick="toggleUpdates()"]');
            button.textContent = updatesEnabled ? '⏸️ Pause Updates' : '▶️ Resume Updates';
            button.style.background = updatesEnabled ? '#333' : '#ff4444';
        }
        
        // Start real-time updates every 2 seconds
        setInterval(fetchRealTimeData, 2000);
        fetchRealTimeData();
        
        // Show connection status
        window.addEventListener('online', () => {
            document.body.style.borderTop = '5px solid #00ff00';
        });
        
        window.addEventListener('offline', () => {
            document.body.style.borderTop = '5px solid #ff0000';
        });
    </script>
</body>
</html>