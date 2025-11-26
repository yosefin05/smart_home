<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$device = $_POST['device'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($device) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Device and action required']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS device_control_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_type VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        control_source VARCHAR(20) DEFAULT 'WEB',
        executed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create servo_control table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS servo_control (
        id INT AUTO_INCREMENT PRIMARY KEY,
        servo_pintu INT DEFAULT 0,
        servo_jemuran INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Get current servo status
    $stmt = $pdo->query("SELECT * FROM servo_control ORDER BY id DESC LIMIT 1");
    $servo = $stmt->fetch();
    
    if (!$servo) {
        // Insert default
        $pdo->exec("INSERT INTO servo_control (servo_pintu, servo_jemuran) VALUES (0, 0)");
        $servoPintu = 0;
        $servoJemuran = 0;
    } else {
        $servoPintu = $servo['servo_pintu'];
        $servoJemuran = $servo['servo_jemuran'];
    }
    
    // Update servo status based on command
    if ($device == 'pintu') {
        $servoPintu = ($action == 'open') ? 1 : 0;
    } elseif ($device == 'jemuran') {
        $servoJemuran = ($action == 'open') ? 1 : 0;
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE servo_control SET servo_pintu = ?, servo_jemuran = ? WHERE id = (SELECT id FROM (SELECT id FROM servo_control ORDER BY id DESC LIMIT 1) as temp)");
    $stmt->execute([$servoPintu, $servoJemuran]);
    
    // Insert log
    $stmt = $pdo->prepare("INSERT INTO device_control_log (device_type, action, control_source, executed) VALUES (?, ?, 'WEB', 0)");
    $stmt->execute([$device, $action]);
    
    echo json_encode(['success' => true, 'message' => 'Command sent to ESP32', 'device' => $device, 'action' => $action, 'servo_status' => "{$servoPintu},{$servoJemuran}"]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>