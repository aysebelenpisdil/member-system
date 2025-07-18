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
} catch(PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

session_start();
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Istanbul');

// Site ayarları
define('SITE_NAME', 'Üye Sistemi');
define('SITE_URL', 'http://localhost/member-system/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('PROFILE_IMAGES_PATH', UPLOAD_PATH . 'profiles/');

// Güvenlik ayarları
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 dakika
define('SESSION_TIMEOUT', 3600); // 1 saat
define('PASSWORD_MIN_LENGTH', 6);

// Email ayarları
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Üye Sistemi');

// Upload klasörlerini oluştur
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
if (!file_exists(PROFILE_IMAGES_PATH)) {
    mkdir(PROFILE_IMAGES_PATH, 0777, true);
}
?>