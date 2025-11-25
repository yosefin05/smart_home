<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Koneksi database gagal"]);
    exit();
}

// Ambil 1 data terbaru (karena dashboard hanya butuh terbaru)
$sql = "SELECT suhu, kelembapan, created_at FROM sensor_data";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "data" => [
            "suhu" => $row["suhu"],
            "kelembapan" => $row["kelembapan"],
            "created_at" => $row["created_at"]
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Tidak ada data sensor"
    ]);
}

$conn->close();
