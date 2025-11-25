<?php
header("Content-Type: text/plain; charset=utf-8");

$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo "ERROR: Koneksi gagal\n";
    exit();
}

echo "=== TEST SERVO CONTROL ===\n\n";

// Test 1: Cek data terbaru
$sql = "SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo "Data terbaru:\n";
    echo "ID: " . $data['id'] . "\n";
    echo "Waktu: " . $data['created_at'] . "\n";
    echo "Servo Pintu: " . $data['servo_pintu'] . "\n";
    echo "Status Pintu: " . $data['status_pintu'] . "\n";
    echo "Servo Jemuran: " . $data['servo_jemuran'] . "\n";
    echo "Status Jemuran: " . $data['status_jemuran'] . "\n\n";
} else {
    echo "Tidak ada data sensor\n\n";
}

// Test 2: Simulasi buka pintu
echo "=== SIMULASI BUKA PINTU ===\n";
$sql = "UPDATE sensor_data SET servo_pintu = 1, status_pintu = 'TERBUKA' WHERE id = (SELECT id FROM (SELECT id FROM sensor_data ORDER BY created_at DESC LIMIT 1) AS temp)";
if ($conn->query($sql)) {
    echo "Berhasil update servo pintu ke TERBUKA\n";
} else {
    echo "Gagal update: " . $conn->error . "\n";
}

// Test 3: Cek hasil update
$result = $conn->query("SELECT servo_pintu, status_pintu FROM sensor_data ORDER BY created_at DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo "Hasil update - Servo Pintu: " . $data['servo_pintu'] . ", Status: " . $data['status_pintu'] . "\n\n";
}

// Test 4: Output untuk Arduino
echo "=== OUTPUT UNTUK ARDUINO ===\n";
$result = $conn->query("SELECT servo_pintu, servo_jemuran, status_pintu, status_jemuran FROM sensor_data ORDER BY created_at DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo "Format Arduino: " . $data['servo_pintu'] . "," . $data['servo_jemuran'] . "," . $data['status_pintu'] . "," . $data['status_jemuran'] . "\n";
}

$conn->close();
?>