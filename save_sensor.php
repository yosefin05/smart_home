<?php
// save_sensor.php
// Simpan di: C:\xampp\htdocs\smarthome\save_sensor.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// === Konfigurasi Database ===
$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

// === Koneksi Database ===
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Koneksi gagal: " . $conn->connect_error
    ]);
    exit();
}

// === Ambil Data dari GET/POST ===
$suhu = isset($_REQUEST['suhu']) ? floatval($_REQUEST['suhu']) : null;
$kelembapan = isset($_REQUEST['kelembapan']) ? floatval($_REQUEST['kelembapan']) : null;
$cahaya = isset($_REQUEST['cahaya']) ? intval($_REQUEST['cahaya']) : null;
$hujan = isset($_REQUEST['hujan']) ? intval($_REQUEST['hujan']) : null;
$servo_jemuran = isset($_REQUEST['servo_jemuran']) ? intval($_REQUEST['servo_jemuran']) : 0;
$servo_pintu = isset($_REQUEST['servo_pintu']) ? intval($_REQUEST['servo_pintu']) : 0;
$led = isset($_REQUEST['led']) ? intval($_REQUEST['led']) : 0;
$status_jemuran = isset($_REQUEST['status_jemuran']) ? $_REQUEST['status_jemuran'] : "TERTUTUP";
$status_pintu = isset($_REQUEST['status_pintu']) ? $_REQUEST['status_pintu'] : "TERTUTUP";

// === Validasi Data ===
if ($suhu === null || $kelembapan === null || $cahaya === null || $hujan === null) {
    echo json_encode([
        "status" => "error",
        "message" => "Parameter tidak lengkap! Butuh: suhu, kelembapan, cahaya, hujan"
    ]);
    exit();
}

// === Insert ke Database ===
$sql = "INSERT INTO sensor_data (suhu, kelembapan, cahaya, hujan, servo_jemuran, servo_pintu, led, status_jemuran, status_pintu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddiiiisss", 
    $suhu, 
    $kelembapan, 
    $cahaya, 
    $hujan, 
    $servo_jemuran, 
    $servo_pintu, 
    $led, 
    $status_jemuran, 
    $status_pintu
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Data berhasil disimpan",
        "id" => $conn->insert_id
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>