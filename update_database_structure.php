<?php
// update_database_structure.php - Update struktur database untuk kontrol servo
header('Content-Type: text/plain; charset=utf-8');

echo "=== UPDATE DATABASE STRUCTURE ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected\n";
    
    // Add executed column to device_control_log if not exists
    try {
        $pdo->exec("ALTER TABLE device_control_log ADD COLUMN executed TINYINT(1) DEFAULT 0");
        echo "✅ Added 'executed' column to device_control_log\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "ℹ️ Column 'executed' already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Create web_commands table for real-time control
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
        echo "✅ Created 'web_commands' table\n";
    } catch (Exception $e) {
        echo "ℹ️ Table 'web_commands' already exists or error: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Database structure updated successfully!\n";
    echo "\n📋 ESP32 Integration:\n";
    echo "   - ESP32 kirim data: save_sensor.php\n";
    echo "   - ESP32 ambil perintah: get_commands.php\n";
    echo "   - Website kirim perintah: control_servo.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
?>