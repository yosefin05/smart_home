<?php
header("Content-Type: text/plain; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$commandFile = 'servo_command.txt';

if (file_exists($commandFile)) {
    echo trim(file_get_contents($commandFile));
} else {
    echo "0,0";
}
?>