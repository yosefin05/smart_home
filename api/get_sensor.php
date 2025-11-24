<?php
// get_sensor.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi gagal"]);
    exit();
}

// Ambil parameter limit (default 10)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Ambil data terbaru
$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "count" => count($data),
    "data" => $data
]);

$stmt->close();
$conn->close();
?>