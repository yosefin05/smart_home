<?php
// config.php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'smarthome_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// BMKG ADM4 Sekardangan
define('BMKG_ADM4', '3515171006');

function getPDO(){
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>'DB connect error: '.$e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

function allow_cors(){
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
}
