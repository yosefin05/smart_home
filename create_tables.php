<?php
// create_tables.php - Buat tabel yang hilang
header('Content-Type: text/plain; charset=utf-8');

echo "=== CREATE MISSING TABLES ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database\n\n";
    
    // Create device_control_log table
    $sql = "
    CREATE TABLE IF NOT EXISTS device_control_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_type VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        control_source VARCHAR(20) DEFAULT 'WEB',
        executed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Table 'device_control_log' created\n";
    
    // Create web_commands table
    $sql = "
    CREATE TABLE IF NOT EXISTS web_commands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_type VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        executed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Table 'web_commands' created\n";
    
    // Insert test command
    $stmt = $pdo->prepare("INSERT INTO device_control_log (device_type, action, control_source, executed) VALUES (?, ?, ?, ?)");
    $stmt->execute(['test', 'init', 'SYSTEM', 1]);
    echo "✅ Test record inserted\n";
    
    echo "\n🎉 All tables created successfully!\n";
    echo "\n📋 Available tables:\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "   - {$table}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
?>