<?php
// test_koneksi.php - Simpan di folder smarthome

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";
$database = "smarthome_db";

echo "<h2>Test Koneksi Database</h2>";

// Test 1: Koneksi
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Koneksi GAGAL: " . $conn->connect_error);
}
echo "✅ Koneksi database berhasil!<br><br>";

// Test 2: Cek tabel
$result = $conn->query("SHOW TABLES LIKE 'sensor_data'");
if ($result->num_rows > 0) {
    echo "✅ Tabel 'sensor_data' ditemukan!<br><br>";
} else {
    die("❌ Tabel 'sensor_data' TIDAK ditemukan!");
}

// Test 3: Cek struktur tabel
echo "<h3>Struktur Tabel:</h3>";
$result = $conn->query("DESCRIBE sensor_data");
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table><br>";

// Test 4: Insert data manual
echo "<h3>Test Insert Data:</h3>";
$sql = "INSERT INTO sensor_data (suhu, kelembapan, cahaya, hujan, servo_jemuran, servo_pintu, led, status_jemuran, status_pintu) 
        VALUES (28.5, 65.0, 1500, 3000, 0, 0, 1, 'TERBUKA', 'TERTUTUP')";

if ($conn->query($sql)) {
    echo "✅ Insert berhasil! ID: " . $conn->insert_id . "<br>";
} else {
    echo "❌ Insert GAGAL: " . $conn->error . "<br>";
}

// Test 5: Tampilkan data
echo "<h3>Data dalam tabel:</h3>";
$result = $conn->query("SELECT * FROM sensor_data ORDER BY id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Suhu</th><th>Kelembapan</th><th>Cahaya</th><th>Hujan</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['suhu']}</td><td>{$row['kelembapan']}</td><td>{$row['cahaya']}</td><td>{$row['hujan']}</td><td>{$row['created_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "Tidak ada data.";
}

$conn->close();
?>