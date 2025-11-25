<?php
header("Content-Type: text/plain; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$commandFile = 'servo_command.txt';

if (file_exists($commandFile)) {
    $command = trim(file_get_contents($commandFile));
    $parts = explode(',', $command);
    
    $servo_pintu = intval($parts[0] ?? 0);
    $servo_jemuran = intval($parts[1] ?? 0);
    
    $status_pintu = ($servo_pintu == 1) ? 'TERBUKA' : 'TERTUTUP';
    $status_jemuran = ($servo_jemuran == 1) ? 'TERBUKA' : 'TERTUTUP';
    
    // Format yang diharapkan ESP32: servo_pintu,servo_jemuran,status_pintu,status_jemuran
    echo $servo_pintu . "," . $servo_jemuran . "," . $status_pintu . "," . $status_jemuran;
} else {
    echo "0,0,TERTUTUP,TERTUTUP";
}
?>