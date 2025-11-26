<?php
require __DIR__ . "/../config.php";
$pdo = getPDO();

// Ambil semua data sensor (pakai tabel & kolom barumu)
$stmt = $pdo->query("
    SELECT 
        id,
        suhu,
        kelembapan,
        cahaya,
        hujan,
        created_at,
        servo_jemuran,
        servo_pintu,
        led,
        status_jemuran,
        status_pintu
    FROM sensor_data
    ORDER BY created_at ASC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek data kosong
if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Tidak ada data sensor']);
    exit;
}

// Header untuk CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sensor_dataset.csv"');

$out = fopen("php://output", "w");

// Tulis header kolom
fputcsv($out, array_keys($data[0]));

// Tulis isi data
foreach ($data as $row) {
    fputcsv($out, $row);
}

fclose($out);
?>
