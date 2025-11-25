<?php
// save_sensor.php
// Simpan di: C:\xampp\htdocs\smarthome\save_sensor.php

header("Content-Type: text/plain; charset=utf-8");
header("Access-Control-Allow-Origin: *");

// === Konfigurasi Database ===
$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

// === Log untuk debugging ===
error_log("=== REQUEST RECEIVED ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Query String: " . $_SERVER['QUERY_STRING']);
error_log("GET Data: " . print_r($_GET, true));

// === Koneksi Database ===
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo "ERROR: Koneksi gagal - " . $conn->connect_error;
    error_log("Database connection failed: " . $conn->connect_error);
    exit();
}

// Set charset
$conn->set_charset("utf8");

$suhu = isset($_GET['suhu']) ? floatval($_GET['suhu']) : null;
$kelembapan = isset($_GET['kelembapan']) ? floatval($_GET['kelembapan']) : null;
$cahaya = isset($_GET['cahaya']) ? intval($_GET['cahaya']) : null;
$hujan = isset($_GET['hujan']) ? intval($_GET['hujan']) : null;
$servo_jemuran = isset($_GET['servo_jemuran']) ? intval($_GET['servo_jemuran']) : 0;
$servo_pintu = isset($_GET['servo_pintu']) ? intval($_GET['servo_pintu']) : 0;
$led = isset($_GET['led']) ? intval($_GET['led']) : 0;
$status_jemuran = isset($_GET['status_jemuran']) ? $_GET['status_jemuran'] : "TERTUTUP";
$status_pintu = isset($_GET['status_pintu']) ? $_GET['status_pintu'] : "TERTUTUP";

// === Debug: Log data yang diterima ===
error_log("Received Data:");
error_log("suhu=$suhu, kelembapan=$kelembapan, cahaya=$cahaya, hujan=$hujan");
error_log("servo_jemuran=$servo_jemuran, servo_pintu=$servo_pintu, led=$led");
error_log("status_jemuran=$status_jemuran, status_pintu=$status_pintu");

// === Validasi Data ===
if ($suhu === null || $kelembapan === null || $cahaya === null || $hujan === null) {
    echo "ERROR: Parameter tidak lengkap!";
    echo "\nData diterima: suhu=$suhu, kelembapan=$kelembapan, cahaya=$cahaya, hujan=$hujan";
    error_log("Validation failed: incomplete parameters");
    exit();
}

// === Insert ke Database ===
$sql = "INSERT INTO sensor_data 
        (suhu, kelembapan, cahaya, hujan, servo_jemuran, servo_pintu, led, status_jemuran, status_pintu, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "ERROR: Prepare statement gagal - " . $conn->error;
    error_log("Prepare failed: " . $conn->error);
    exit();
}

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
    $insert_id = $conn->insert_id;
    echo "SUCCESS: Data tersimpan dengan ID=$insert_id";
    error_log("Data saved successfully with ID: $insert_id");
} else {
    echo "ERROR: Gagal menyimpan - " . $stmt->error;
    error_log("Execute failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>