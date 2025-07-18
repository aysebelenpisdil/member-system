<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

girisKontrol();

// Admin kontrolü
if (!adminMi()) {
    header("Location: ../dashboard.php");
    exit();
}

$mesaj = '';
$hata = '';

// Ayarları kaydet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'general':
                // Genel ayarlar
                $site_name = temizle($_POST['site_name']);
                $site_url = temizle($_POST['site_url']);
                $contact_email = temizle($_POST['contact_email']);
                $timezone = $_POST['timezone'];
                
                try {
                    // Ayarları güncelle veya ekle
                    $ayarlar = [
                        'site_name' => $site_name,
                        'site_url' => $site_url,
                        'contact_email' => $contact_email,
                        'timezone' => $timezone
                    ];
                    
                    foreach ($ayarlar as $key => $value) {
                        $check = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
                        $check->execute([$key]);
                        
                        if ($check->rowCount() > 0) {
                            $update = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                            $update->execute([$value, $key]);
                        } else {
                            $insert = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                            $insert->execute([$key, $value]);
                        }
                    }
                    
                    $mesaj = "Genel ayarlar güncellendi!";
                } catch (PDOException $e) {
                    $hata = "Ayarlar güncellenirken hata oluştu!";
                }
                break;
                
            case 'security':
                // Güvenlik ayarları
                $max_login_attempts = intval($_POST['max_login_attempts']);
                $lockout_time = intval($_POST['lockout_time']);
                $session_timeout = intval($_POST['session_timeout']);
                $password_min_length = intval($_POST['password_min_length']);
                
                try {
                    $ayarlar = [
                        'max_login_attempts' => $max_login_attempts,
                        'lockout_time' => $lockout_time,
                        'session_timeout' => $session_timeout,
                        'password_min_length' => $password_min_length
                    ];
                    
                    foreach ($ayarlar as $key => $value) {
                        $check = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
                        $check->execute([$key]);
                        
                        if ($check->rowCount() > 0) {
                            $update = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                            $update->execute([$value, $key]);
                        } else {
                            $insert = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                            $insert->execute([$key, $value]);
                        }
                    }
                    
                    $mesaj = "Güvenlik ayarları güncellendi!";
                } catch (PDOException $e) {
                    $hata = "Ayarlar güncellenirken hata oluştu!";
                }
                break;
                
            case 'maintenance':
                // Bakım modu
                $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
                $maintenance_message = temizle($_POST['maintenance_message']);
                
                try {
                    $ayarlar = [
                        'maintenance_mode' => $maintenance_mode,
                        'maintenance_message' => $maintenance_message
                    ];
                    
                    foreach ($ayarlar as $key => $value) {
                        $check = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
                        $check->execute([$key]);
                        
                        if ($check->rowCount() > 0) {
                            $update = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                            $update->execute([$value, $key]);
                        } else {
                            $insert = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                            $insert->execute([$key, $value]);
                        }
                    }
                    
                    $mesaj = "Bakım modu ayarları güncellendi!";
                } catch (PDOException $e) {
                    $hata = "Ayarlar güncellenirken hata oluştu!";
                }
                break;
        }
    }
}

// Mevcut ayarları getir
function getAyar($db, $key, $default = '') {
    $sorgu = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $sorgu->execute([$key]);
    $result = $sorgu->fetch();
    return $result ? $result['setting_value'] : $default;
}

// İstatistikler
$toplam_kullanici = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$toplam_aktivite = $db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$toplam_bildirim = $db->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
$db_boyutu = $db->query("SELECT SUM(data_length + index_length) / 1024 / 1024 as size FROM information_schema.TABLES WHERE table_schema = '$database'")->fetch()['size'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Ayarları - Admin Paneli</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libertinus+Mono&display=swap');
        
        body {
            font-family: 'Libertinus Mono', monospace;
            background-color: #f5e6d3;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .admin-header {
            background-color: #1a1a1a;
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 400;
            letter-spacing: 1px;
            display: inline-block;
        }
        
        .admin-badge {
            background-color: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 15px;
            text-transform: uppercase;
        }
        
        .admin-nav {
            background-color: #2d2d2d;
            padding: 0 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: inline-block;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }
        
        .admin-nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .admin-nav a.active {
            background-color: #1a1a1a;
        }
        
        .logout-btn {
            float: right;
            background-color: #dc3545;
            color: white !important;
            padding: 15px 20px;
        }
        
        .logout-btn:hover {
            background-color: #c82333 !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        
        .settings-menu {
            background-color: white;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            height: fit-content;
            overflow: hidden;
        }
        
        .menu-item {
            display: block;
            padding: 20px 25px;
            color: #1a1a1a;
            text-decoration: none;
            border-bottom: 1px solid #e8d5c4;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .menu-item:last-child {
            border-bottom: none;
        }
        
        .menu-item:hover {
            background-color: #f5e6d3;
            padding-left: 30px;
        }
        
        .menu-item.active {
            background-color: #1a1a1a;
            color: white;
        }
        
        .settings-content {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .settings-section {
            display: none;
        }
        
        .settings-section.active {
            display: block;
        }
        
        .settings-section h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d2d2d;
            font-weight: 400;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Libertinus Mono', monospace;
            background-color: #fafafa;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1a1a1a;
            background-color: white;
            box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Libertinus Mono', monospace;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #1a1a1a;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #000;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }
        
        .alert-success {
            background-color: #efe;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #1a1a1a;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .danger-zone {
            background-color: #fff5f5;
            padding: 30px;
            border-radius: 8px;
            border: 2px solid #ffcccc;
            margin-top: 30px;
        }
        
        .danger-zone h3 {
            color: #dc3545;
            margin-top: 0;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-menu {
                display: flex;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .menu-item {
                border-bottom: none;
                border-right: 1px solid #e8d5c4;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Sistem Ayarları</h1>
        <span class="admin-badge">Yönetici</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php">Genel Bakış</a>
        <a href="users.php">Kullanıcılar</a>
        <a href="activity-logs.php">Aktivite Kayıtları</a>
        <a href="settings.php" class="active">Sistem Ayarları</a>
        <a href="../dashboard.php">Ana Siteye Dön</a>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </nav>
    
    <div class="container">
        <?php if($mesaj): ?>
            <div class="alert alert-success">
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>
        
        <?php if($hata): ?>
            <div class="alert alert-error">
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>
        
        <div class="settings-grid">
            <div class="settings-menu">
                <a class="menu-item active" onclick="showSection('general')">Genel Ayarlar</a>
                <a class="menu-item" onclick="showSection('security')">Güvenlik</a>
                <a class="menu-item" onclick="showSection('email')">Email Ayarları</a>
                <a class="menu-item" onclick="showSection('maintenance')">Bakım Modu</a>
                <a class="menu-item" onclick="showSection('database')">Veritabanı</a>
                <a class="menu-item" onclick="showSection('system')">Sistem Bilgisi</a>
            </div>
            
            <div class="settings-content">
                <!-- Genel Ayarlar -->
                <div class="settings-section active" id="general">
                    <h2>Genel Ayarlar</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="general">
                        
                        <div class="form-group">
                            <label for="site_name">Site Adı</label>
                            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars(getAyar($db, 'site_name', 'Üye Sistemi')); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_url">Site URL</label>
                            <input type="url" id="site_url" name="site_url" value="<?php echo htmlspecialchars(getAyar($db, 'site_url', SITE_URL)); ?>" required>
                            <small>Sonunda / işareti olmadan yazın</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">İletişim Email</label>
                            <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars(getAyar($db, 'contact_email', '')); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="timezone">Saat Dilimi</label>
                            <select id="timezone" name="timezone">
                                <option value="Europe/Istanbul" <?php echo getAyar($db, 'timezone', 'Europe/Istanbul') == 'Europe/Istanbul' ? 'selected' : ''; ?>>İstanbul (UTC+3)</option>
                                <option value="Europe/London" <?php echo getAyar($db, 'timezone') == 'Europe/London' ? 'selected' : ''; ?>>Londra (UTC+0)</option>
                                <option value="America/New_York" <?php echo getAyar($db, 'timezone') == 'America/New_York' ? 'selected' : ''; ?>>New York (UTC-5)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>
                </div>
                
                <!-- Güvenlik Ayarları -->
                <div class="settings-section" id="security">
                    <h2>Güvenlik Ayarları</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="security">
                        
                        <div class="form-group">
                            <label for="max_login_attempts">Maksimum Giriş Denemesi</label>
                            <input type="number" id="max_login_attempts" name="max_login_attempts" value="<?php echo getAyar($db, 'max_login_attempts', MAX_LOGIN_ATTEMPTS); ?>" min="3" max="10" required>
                            <small>Başarısız giriş denemesi limiti</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="lockout_time">Kilitlenme Süresi (saniye)</label>
                            <input type="number" id="lockout_time" name="lockout_time" value="<?php echo getAyar($db, 'lockout_time', LOGIN_LOCKOUT_TIME); ?>" min="300" max="3600" required>
                            <small>Çok fazla başarısız denemeden sonra bekleme süresi</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="session_timeout">Oturum Zaman Aşımı (saniye)</label>
                            <input type="number" id="session_timeout" name="session_timeout" value="<?php echo getAyar($db, 'session_timeout', SESSION_TIMEOUT); ?>" min="600" max="7200" required>
                            <small>İşlem yapılmadığında otomatik çıkış süresi</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_min_length">Minimum Şifre Uzunluğu</label>
                            <input type="number" id="password_min_length" name="password_min_length" value="<?php echo getAyar($db, 'password_min_length', PASSWORD_MIN_LENGTH); ?>" min="6" max="20" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>
                </div>
                
                <!-- Email Ayarları -->
                <div class="settings-section" id="email">
                    <h2>Email Ayarları</h2>
                    
                    <div class="alert alert-error">
                        Email ayarları güvenlik nedeniyle config.php dosyasından yapılmalıdır.
                    </div>
                    
                    <div class="form-group">
                        <label>SMTP Sunucu</label>
                        <input type="text" value="<?php echo SMTP_HOST; ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="text" value="<?php echo SMTP_PORT; ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Gönderen Email</label>
                        <input type="text" value="<?php echo SMTP_FROM; ?>" disabled>
                    </div>
                </div>
                
                <!-- Bakım Modu -->
                <div class="settings-section" id="maintenance">
                    <h2>Bakım Modu</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="maintenance">
                        
                        <div class="form-group">
                            <label for="maintenance_mode">Bakım Modu</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="maintenance_mode" <?php echo getAyar($db, 'maintenance_mode', '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <small style="margin-left: 60px;">Aktif olduğunda sadece adminler giriş yapabilir</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="maintenance_message">Bakım Mesajı</label>
                            <textarea id="maintenance_message" name="maintenance_message" rows="4"><?php echo htmlspecialchars(getAyar($db, 'maintenance_message', 'Sitemiz şu anda bakımdadır. Lütfen daha sonra tekrar deneyin.')); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>
                </div>
                
                <!-- Veritabanı -->
                <div class="settings-section" id="database">
                    <h2>Veritabanı İşlemleri</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($db_boyutu, 2); ?> MB</div>
                            <div class="stat-label">Veritabanı Boyutu</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($toplam_kullanici); ?></div>
                            <div class="stat-label">Toplam Kullanıcı</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($toplam_aktivite); ?></div>
                            <div class="stat-label">Aktivite Kaydı</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($toplam_bildirim); ?></div>
                            <div class="stat-label">Bildirim</div>
                        </div>
                    </div>
                    
                    <div class="danger-zone">
                        <h3>Tehlikeli İşlemler</h3>
                        
                        <div style="margin-bottom: 20px;">
                            <button class="btn btn-primary" onclick="alert('Yedekleme özelliği yakında eklenecek!')">Veritabanı Yedeği Al</button>
                            <small style="display: block; margin-top: 10px;">Tüm veritabanının yedeğini indirir</small>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <button class="btn btn-danger" onclick="if(confirm('Tüm aktivite kayıtları silinecek. Emin misiniz?')) alert('Bu özellik güvenlik nedeniyle devre dışı!')">Aktivite Kayıtlarını Temizle</button>
                            <small style="display: block; margin-top: 10px;">30 günden eski aktivite kayıtlarını siler</small>
                        </div>
                        
                        <div>
                            <button class="btn btn-danger" onclick="alert('Bu özellik güvenlik nedeniyle devre dışı!')">Veritabanını Optimize Et</button>
                            <small style="display: block; margin-top: 10px;">Veritabanı tablolarını optimize eder</small>
                        </div>
                    </div>
                </div>
                
                <!-- Sistem Bilgisi -->
                <div class="settings-section" id="system">
                    <h2>Sistem Bilgisi</h2>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">PHP Versiyonu</td>
                            <td style="padding: 15px;"><?php echo phpversion(); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">MySQL Versiyonu</td>
                            <td style="padding: 15px;"><?php echo $db->query('SELECT VERSION()')->fetchColumn(); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">Sunucu</td>
                            <td style="padding: 15px;"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">İşletim Sistemi</td>
                            <td style="padding: 15px;"><?php echo PHP_OS; ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">Sunucu Zamanı</td>
                            <td style="padding: 15px;"><?php echo date('d.m.Y H:i:s'); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">Max Upload Boyutu</td>
                            <td style="padding: 15px;"><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 15px; font-weight: 600;">Memory Limit</td>
                            <td style="padding: 15px;"><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; font-weight: 600;">Max Execution Time</td>
                            <td style="padding: 15px;"><?php echo ini_get('max_execution_time'); ?> saniye</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showSection(sectionId) {
            // Tüm sectionları gizle
            document.querySelectorAll('.settings-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Tüm menü itemlarından active class'ı kaldır
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // İlgili section'ı göster
            document.getElementById(sectionId).classList.add('active');
            
            // İlgili menü item'ına active class ekle
            event.target.classList.add('active');
        }
    </script>
</body>
</html>