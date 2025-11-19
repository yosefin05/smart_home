<?php
// api/get_history.php
require __DIR__ . "/../config.php";
allow_cors();

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, adm4_code, waktu_data, suhu, kelembapan, deskripsi, created_at
                       FROM bmkg_data
                       ORDER BY created_at DESC
                       LIMIT :lim");
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>true,'data'=>$rows]);
