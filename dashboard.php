<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];
$kullanici_adi = $_SESSION['kullanici_adi'];
$kullanici_email = $_SESSION['kullanici_email'];

// Kullanıcı bilgilerini güncelle
try {
    $sorgu = $db->prepare("SELECT * FROM users WHERE id = ?");
    $sorgu->execute([$kullanici_id]);
    $kullanici = $sorgu->fetch();
    
    // Son aktiviteleri al
    $aktiviteler = $db->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $aktiviteler->execute([$kullanici_id]);
    
    // Bildirim sayısı
    $bildirim_sayisi = okunmamisBildirimSayisi($db, $kullanici_id);
    
    // İstatistikler
    $toplam_giris = $kullanici['login_count'] ?? 0;
    $kayit_tarihi = $kullanici['created_at'];
    $son_giris = $kullanici['last_login'] ?? 'İlk giriş';
    
    // Profil doluluk oranı
    $profil_doluluk = 20; // Temel bilgiler
    if(!empty($kullanici['email_verified'])) $profil_doluluk += 20;
    if(!empty($kullanici['phone'])) $profil_doluluk += 15;
    if(!empty($kullanici['bio'])) $profil_doluluk += 15;
    if(!empty($kullanici['profile_image'])) $profil_doluluk += 15;
    if(!empty($kullanici['birth_date'])) $profil_doluluk += 15;
    
} catch(PDOException $e) {
    die("Veritabanı hatası!");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($kullanici_adi); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libertinus+Mono&display=swap');
        
        body {
            font-family: 'Libertinus Mono', monospace;
            background-color: #f5e6d3;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .navbar {
            background-color: #1a1a1a;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 400;
            letter-spacing: 1px;
        }
        
        .nav-links {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }
        
        .navbar a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .navbar a.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .notification-badge {
            position: relative;
        }
        
        .badge-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .logout-btn {
            background-color: #dc3545;
            color: white !important;
            margin-left: 10px;
        }
        
        .logout-btn:hover {
            background-color: #c82333 !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        
        .welcome-content {
            position: relative;
            z-index: 1;
        }
        
        .welcome-card h2 {
            margin: 0 0 15px 0;
            font-size: 32px;
            font-weight: 400;
        }
        
        .welcome-card p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .card h3 {
            margin: 0 0 25px 0;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-completion {
            margin-bottom: 30px;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin: 15px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1a1a1a 0%, #2d2d2d 100%);
            transition: width 0.5s ease;
        }
        
        .progress-items {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        
        .progress-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .progress-item.completed {
            color: #28a745;
        }
        
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background-color: #e9ecef;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .activity-icon.login {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .activity-icon.update {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .activity-icon.password {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-title {
            font-size: 14px;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        
        .activity-time {
            font-size: 12px;
            color: #999;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-btn {
            background-color: #1a1a1a;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn:hover {
            background-color: #000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .action-btn .icon {
            font-size: 32px;
        }
        
        .action-btn span {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .action-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .quick-stats {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .quick-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        
        .quick-stat-label {
            font-size: 14px;
            color: #666;
        }
        
        .quick-stat-value {
            font-size: 16px;
            font-weight: 500;
            color: #1a1a1a;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state p {
            margin: 10px 0 0 0;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-card h2 {
                font-size: 24px;
            }
        }
        
        .admin-notice {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-notice a {
            color: #856404;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 ÜYE PANELİ</h1>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <div class="notification-badge">
                <a href="notifications.php">
                    Bildirimler
                    <?php if($bildirim_sayisi > 0): ?>
                        <span class="badge-count"><?php echo $bildirim_sayisi; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <a href="settings.php">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <?php if($kullanici['role'] === 'admin'): ?>
        <div class="admin-notice">
            <span>👑</span>
            <span>Yönetici hesabı ile giriş yaptınız. <a href="admin/index.php">Admin paneline git →</a></span>
        </div>
        <?php endif; ?>
        
        <div class="welcome-card">
            <div class="welcome-content">
                <h2>Hoş geldin, <?php echo htmlspecialchars($kullanici_adi); ?>! 👋</h2>
                <p>Bugün <?php echo strftime('%d %B %Y, %A'); ?>. İyi günler dileriz!</p>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">📅</span>
                <div class="stat-value"><?php echo tarihFormatla($kayit_tarihi); ?></div>
                <div class="stat-label">Kayıt Tarihi</div>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">🔑</span>
                <div class="stat-value"><?php echo $toplam_giris; ?></div>
                <div class="stat-label">Toplam Giriş</div>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">⏰</span>
                <div class="stat-value"><?php echo $son_giris ? zamanFarki($son_giris) : 'İlk giriş'; ?></div>
                <div class="stat-label">Son Giriş</div>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">🔔</span>
                <div class="stat-value"><?php echo $bildirim_sayisi; ?></div>
                <div class="stat-label">Okunmamış Bildirim</div>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="card">
                <h3><span>📊</span> Son Aktiviteler</h3>
                
                <?php if($aktiviteler->rowCount() > 0): ?>
                    <div class="activity-list">
                        <?php while($aktivite = $aktiviteler->fetch()): ?>
                            <?php
                            $icon_class = 'login';
                            $icon = '🔑';
                            if(strpos($aktivite['action'], 'update') !== false) {
                                $icon_class = 'update';
                                $icon = '✏️';
                            } elseif(strpos($aktivite['action'], 'password') !== false) {
                                $icon_class = 'password';
                                $icon = '🔐';
                            }
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $icon_class; ?>">
                                    <?php echo $icon; ?>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">
                                        <?php echo ucfirst(str_replace('_', ' ', $aktivite['action'])); ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo zamanFarki($aktivite['created_at']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="activity.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px;">
                            Tüm aktiviteleri görüntüle →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span style="font-size: 48px;">📭</span>
                        <p>Henüz aktivite kaydınız yok</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <div class="card profile-completion">
                    <h3><span>📈</span> Profil Durumu</h3>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 14px; color: #666;">Tamamlanma Oranı</span>
                        <span style="font-size: 18px; font-weight: 600; color: #1a1a1a;">%<?php echo $profil_doluluk; ?></span>
                    </div>
                    
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $profil_doluluk; ?>%"></div>
                    </div>
                    
                    <div class="progress-items">
                        <div class="progress-item completed">
                            <span>✅</span> Temel bilgiler
                        </div>
                        <div class="progress-item <?php echo $kullanici['email_verified'] ? 'completed' : ''; ?>">
                            <span><?php echo $kullanici['email_verified'] ? '✅' : '⭕'; ?></span> Email doğrulama
                        </div>
                        <div class="progress-item <?php echo !empty($kullanici['phone']) ? 'completed' : ''; ?>">
                            <span><?php echo !empty($kullanici['phone']) ? '✅' : '⭕'; ?></span> Telefon numarası
                        </div>
                        <div class="progress-item <?php echo !empty($kullanici['profile_image']) ? 'completed' : ''; ?>">
                            <span><?php echo !empty($kullanici['profile_image']) ? '✅' : '⭕'; ?></span> Profil fotoğrafı
                        </div>
                        <div class="progress-item <?php echo !empty($kullanici['bio']) ? 'completed' : ''; ?>">
                            <span><?php echo !empty($kullanici['bio']) ? '✅' : '⭕'; ?></span> Hakkımda
                        </div>
                    </div>
                    
                    <?php if($profil_doluluk < 100): ?>
                        <div style="text-align: center; margin-top: 25px;">
                            <a href="profile.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">
                                Profilini tamamla →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <h3><span>⚡</span> Hızlı İstatistikler</h3>
                    
                    <div class="quick-stats">
                        <div class="quick-stat-item">
                            <span class="quick-stat-label">Hesap Türü</span>
                            <span class="quick-stat-value"><?php echo ucfirst($kullanici['role'] ?? 'user'); ?></span>
                        </div>
                        <div class="quick-stat-item">
                            <span class="quick-stat-label">Hesap Durumu</span>
                            <span class="quick-stat-value" style="color: #28a745;">Aktif</span>
                        </div>
                        <div class="quick-stat-item">
                            <span class="quick-stat-label">Email Doğrulama</span>
                            <span class="quick-stat-value">
                                <?php echo $kullanici['email_verified'] ? '✅ Doğrulandı' : '❌ Bekliyor'; ?>
                            </span>
                        </div>
                        <div class="quick-stat-item">
                            <span class="quick-stat-label">Üye No</span>
                            <span class="quick-stat-value">#<?php echo str_pad($kullanici['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3><span>🚀</span> Hızlı İşlemler</h3>
            
            <div class="action-grid">
                <a href="profile.php" class="action-btn">
                    <span class="icon">✏️</span>
                    <span>Profili Düzenle</span>
                </a>
                
                <a href="change-password.php" class="action-btn">
                    <span class="icon">🔑</span>
                    <span>Şifre Değiştir</span>
                </a>
                
                <a href="notifications.php" class="action-btn">
                    <span class="icon">🔔</span>
                    <span>Bildirimler</span>
                </a>
                
                <a href="support.php" class="action-btn">
                    <span class="icon">💬</span>
                    <span>Destek</span>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Otomatik oturum timeout kontrolü
        let lastActivity = Date.now();
        const TIMEOUT = <?php echo SESSION_TIMEOUT * 1000; ?>; // milisaniye
        
        function checkTimeout() {
            if (Date.now() - lastActivity > TIMEOUT) {
                window.location.href = 'logout.php?reason=timeout';
            }
        }
        
        // Her 60 saniyede bir kontrol et
        setInterval(checkTimeout, 60000);
        
        // Kullanıcı aktivitesini takip et
        document.addEventListener('click', function() {
            lastActivity = Date.now();
        });
        
        document.addEventListener('keypress', function() {
            lastActivity = Date.now();
        });
    </script>
</body>
</html>