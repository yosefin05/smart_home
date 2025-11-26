<?php
require __DIR__ . "/../config.php";
allow_cors();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "no_input"]);
    exit;
}

$adm4 = $data['adm4'] ?? BMKG_ADM4;
$waktu = $data['waktu_data'] ?? date('Y-m-d H:i:s');
$suhu = $data['suhu'] ?? null;
$hum = $data['kelembapan'] ?? null;
$desc = $data['deskripsi'] ?? null;
$raw = json_encode($data['raw_json'] ?? []);

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        INSERT INTO bmkg_data (adm4_code, waktu_data, suhu, kelembapan, deskripsi, raw_json, created_at)
        VALUES (:adm4, :waktu, :suhu, :hum, :desc, :raw, NOW())
    ");
    $stmt->execute([
        ':adm4' => $adm4,
        ':waktu' => $waktu,
        ':suhu' => $suhu,
        ':hum' => $hum,
        ':desc' => $desc,
        ':raw' => $raw
    ]);

    echo json_encode([
        'success' => true,
        'insert_id' => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
