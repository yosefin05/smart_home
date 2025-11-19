<?php
// api/get_bmkg.php
require __DIR__ . "/../config.php";
allow_cors();

$adm4 = BMKG_ADM4;
$bmkgUrl = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=35.15.08.1006";

$ch = curl_init($bmkgUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err || !$res) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$err ?: 'no_response']);
    exit;
}

$json = json_decode($res, true);
if (!$json) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'invalid_json']);
    exit;
}

// safe parsing: get first forecast item if exists
$peek = null;
if (isset($json['data'][0]['cuaca'][0][0])) {
    $peek = $json['data'][0]['cuaca'][0][0];
}

// map fields we care about
$suhu = $peek['t'] ?? null;
$kelembapan = $peek['hu'] ?? null;
$deskripsi = $peek['weather_desc'] ?? null;
$waktu_data = date('Y-m-d H:i:s');

$response = [
    'success' => true,
    'raw' => $json,
    'data' => [
        'adm4' => $adm4,
        'waktu_data' => $waktu_data,
        'suhu' => $suhu,
        'kelembapan' => $kelembapan,
        'deskripsi' => $deskripsi
    ]
];

// if client requests save=1, insert row into bmkg_data
if (isset($_GET['save']) && $_GET['save'] == '1') {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO bmkg_data (adm4_code, waktu_data, suhu, kelembapan, deskripsi, raw_json, created_at)
                               VALUES (:adm4, :waktu, :suhu, :hum, :desc, :raw, NOW())");
        $stmt->execute([
            ':adm4' => $adm4,
            ':waktu' => $waktu_data,
            ':suhu' => $suhu,
            ':hum' => $kelembapan,
            ':desc' => $deskripsi,
            ':raw' => json_encode($json)
        ]);
        $response['saved'] = true;
        $response['insert_id'] = $pdo->lastInsertId();
    } catch (Exception $e) {
        $response['saved'] = false;
        $response['save_error'] = $e->getMessage();
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
