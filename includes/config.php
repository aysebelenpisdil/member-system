<?php
$host = "localhost";
$username = "root";
$password = "root";
$database = "uye_sistemi";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "✅ Veritabanı bağlantısı başarılı!<br>";
} catch(PDOException $e) {
    die("❌ Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

session_start();
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Istanbul');
?>