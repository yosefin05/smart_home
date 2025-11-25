<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$servo = $_GET['servo'] ?? '';
$action = $_GET['action'] ?? '';

if (!$servo || !$action) {
    echo json_encode(["success" => false, "error" => "Parameter tidak lengkap"]);
    exit();
}

// Baca file command saat ini
$commandFile = 'servo_command.txt';
$currentCommand = file_exists($commandFile) ? trim(file_get_contents($commandFile)) : '0,0';
$commands = explode(',', $currentCommand);

$servo_pintu = intval($commands[0] ?? 0);
$servo_jemuran = intval($commands[1] ?? 0);

// Update command berdasarkan input
if ($servo === 'pintu') {
    $servo_pintu = ($action === 'open') ? 1 : 0;
} elseif ($servo === 'jemuran') {
    $servo_jemuran = ($action === 'open') ? 1 : 0;
}

// Simpan ke file
$newCommand = $servo_pintu . ',' . $servo_jemuran;
file_put_contents($commandFile, $newCommand);

// Log untuk debug
error_log("Servo control: $servo $action - New command: $newCommand");

echo json_encode([
    "success" => true, 
    "servo" => $servo, 
    "action" => $action, 
    "command" => $newCommand
]);
?>