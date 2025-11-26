<?php
// fetch_bmkg.php - Script untuk mengambil data BMKG secara otomatis
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== FETCH BMKG DATA ===\n";

try {
    // Ambil data dari API BMKG
    $bmkgUrl = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=" . BMKG_ADM4;
    
    $ch = curl_init($bmkgUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("CURL Error: " . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception("HTTP Error: " . $httpCode);
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception("Invalid JSON response");
    }
    
    echo "✅ Data BMKG berhasil diambil\n";
    
    // Parse data cuaca
    $cuacaData = null;
    if (isset($data['data'][0]['cuaca'][0][0])) {
        $cuacaData = $data['data'][0]['cuaca'][0][0];
    }
    
    if (!$cuacaData) {
        throw new Exception("Format data BMKG tidak sesuai");
    }
    
    $suhu = $cuacaData['t'] ?? null;
    $kelembapan = $cuacaData['hu'] ?? null;
    $deskripsi = $cuacaData['weather_desc'] ?? null;
    
    echo "📊 Data Cuaca:\n";
    echo "   Suhu: {$suhu}°C\n";
    echo "   Kelembapan: {$kelembapan}%\n";
    echo "   Deskripsi: {$deskripsi}\n";
    
    // Simpan ke database
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        INSERT INTO bmkg_data (adm4_code, waktu_data, suhu, kelembapan, deskripsi, raw_json, created_at)
        VALUES (:adm4, :waktu, :suhu, :kelembapan, :deskripsi, :raw_json, NOW())
    ");
    
    $stmt->execute([
        ':adm4' => BMKG_ADM4,
        ':waktu' => date('Y-m-d H:i:s'),
        ':suhu' => $suhu,
        ':kelembapan' => $kelembapan,
        ':deskripsi' => $deskripsi,
        ':raw_json' => json_encode($data)
    ]);
    
    $insertId = $pdo->lastInsertId();
    echo "💾 Data tersimpan ke database dengan ID: {$insertId}\n";
    echo "✅ SUCCESS: BMKG data updated!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
?>