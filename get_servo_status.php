<?php
// get_servo_status.php - Format yang sesuai dengan ESP32 code
header("Content-Type: text/plain; charset=utf-8");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS servo_control (
        id INT AUTO_INCREMENT PRIMARY KEY,
        servo_pintu INT DEFAULT 0,
        servo_jemuran INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Get or create servo status
    $stmt = $pdo->query("SELECT * FROM servo_control ORDER BY id DESC LIMIT 1");
    $servo = $stmt->fetch();
    
    if (!$servo) {
        // Insert default values
        $pdo->exec("INSERT INTO servo_control (servo_pintu, servo_jemuran) VALUES (0, 0)");
        echo "0,0";
    } else {
        // Return format: pintu,jemuran (sesuai ESP32 code)
        echo $servo['servo_pintu'] . "," . $servo['servo_jemuran'];
    }
    
} catch(PDOException $e) {
    echo "0,0"; // Default jika error
}
?>