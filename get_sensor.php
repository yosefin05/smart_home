<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Koneksi gagal"]);
    exit();
}

$conn->set_charset("utf8");

// Ambil data sensor terbaru
$sql = "SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Tidak ada data sensor"
    ]);
}

$conn->close();
?>