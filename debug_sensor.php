<?php
header("Content-Type: text/plain; charset=utf-8");

$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo "ERROR: Koneksi gagal - " . $conn->connect_error;
    exit();
}

$conn->set_charset("utf8");

// Ambil 5 data terbaru untuk debug
$sql = "SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

echo "=== DEBUG DATA SENSOR TERBARU ===\n\n";

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Waktu: " . $row['created_at'] . "\n";
        echo "Suhu: " . $row['suhu'] . "\n";
        echo "Kelembapan: " . $row['kelembapan'] . "\n";
        echo "Cahaya: " . $row['cahaya'] . "\n";
        echo "Hujan: " . $row['hujan'] . " (1=basah, 0=kering)\n";
        echo "Servo Jemuran: " . $row['servo_jemuran'] . "\n";
        echo "Status Jemuran: " . $row['status_jemuran'] . "\n";
        echo "Servo Pintu: " . $row['servo_pintu'] . "\n";
        echo "Status Pintu: " . $row['status_pintu'] . "\n";
        echo "---\n";
    }
} else {
    echo "Tidak ada data sensor\n";
}

$conn->close();
?>