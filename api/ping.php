<?php
// api/ping.php - Connection check
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'online',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>