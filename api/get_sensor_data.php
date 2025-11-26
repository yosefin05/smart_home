<?php
// api/get_sensor_data.php - Real-time sensor data API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get latest sensor data
    $stmt = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
    $sensorData = $stmt->fetch();
    
    // Get latest BMKG data
    $stmt = $pdo->query("SELECT * FROM bmkg_data ORDER BY created_at DESC LIMIT 1");
    $bmkgData = $stmt->fetch();
    
    // Get servo control status
    $stmt = $pdo->query("SELECT * FROM servo_control ORDER BY id DESC LIMIT 1");
    $servoData = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'sensor' => $sensorData,
        'bmkg' => $bmkgData,
        'servo' => $servoData,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>