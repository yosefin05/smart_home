<?php
// check_database.php - Script untuk mengecek database dan tabel
header('Content-Type: text/plain; charset=utf-8');

echo "=== DATABASE CHECK ===\n\n";

try {
    // Test koneksi database
    $pdo = new PDO('mysql:host=localhost;dbname=smarthome_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Koneksi database berhasil\n\n";
    
    // Cek tabel sensor_data
    echo "📊 SENSOR DATA:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sensor_data");
    $count = $stmt->fetch()['total'];
    echo "   Total records: {$count}\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
        $latest = $stmt->fetch();
        echo "   Latest data: {$latest['created_at']}\n";
        echo "   Suhu: {$latest['suhu']}°C\n";
        echo "   Kelembapan: {$latest['kelembapan']}%\n";
        echo "   Cahaya: {$latest['cahaya']}\n";
        echo "   Hujan: " . ($latest['hujan'] ? 'Ya' : 'Tidak') . "\n";
    } else {
        echo "   ⚠️ Belum ada data sensor\n";
    }
    
    echo "\n🌤️ BMKG DATA:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bmkg_data");
    $count = $stmt->fetch()['total'];
    echo "   Total records: {$count}\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM bmkg_data ORDER BY created_at DESC LIMIT 1");
        $latest = $stmt->fetch();
        echo "   Latest data: {$latest['created_at']}\n";
        echo "   Suhu: {$latest['suhu']}°C\n";
        echo "   Kelembapan: {$latest['kelembapan']}%\n";
        echo "   Deskripsi: {$latest['deskripsi']}\n";
    } else {
        echo "   ⚠️ Belum ada data BMKG\n";
    }
    
    echo "\n🎛️ DEVICE CONTROL LOG:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM device_control_log");
    $count = $stmt->fetch()['total'];
    echo "   Total records: {$count}\n";
    
    echo "\n✅ Database check completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "\n💡 Pastikan:\n";
    echo "   - XAMPP MySQL sudah running\n";
    echo "   - Database 'smarthome_db' sudah dibuat\n";
    echo "   - Tabel sudah diimport dari SQL file\n";
}

echo "\n=== SELESAI ===\n";
?>