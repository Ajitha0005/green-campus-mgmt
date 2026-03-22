<?php
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3307';
$db   = getenv('DB_NAME') ?: 'green_campus';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Force SSL for TiDB Cloud on Render
    if (getenv('RENDER')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database Connection Failed: " . $e->getMessage());
}
?>
