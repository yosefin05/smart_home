<?php
// get_commands.php - ESP32 ambil perintah kontrol dari website
header("Content-Type: text/plain; charset=utf-8");
header("Access-Control-Allow-Origin: *");

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
    
    // Ambil perintah yang belum dieksekusi
    $stmt = $pdo->query("
        SELECT * FROM device_control_log 
        WHERE control_source = 'WEB' AND executed = 0 
        ORDER BY created_at ASC 
        LIMIT 1
    ");
    
    $command = $stmt->fetch();
    
    if ($command) {
        // Tandai sebagai sudah dieksekusi
        $updateStmt = $pdo->prepare("UPDATE device_control_log SET executed = 1 WHERE id = ?");
        $updateStmt->execute([$command['id']]);
        
        // Return command untuk ESP32
        echo "COMMAND:" . $command['device_type'] . ":" . $command['action'];
    } else {
        echo "NO_COMMAND";
    }
    
} catch(PDOException $e) {
    echo "ERROR:" . $e->getMessage();
}
?>