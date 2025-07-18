<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];

// Sayfalama
$sayfa = isset($_GET['sayfa']) ? intval($_GET['sayfa']) : 1;
$limit = 20;
$offset = ($sayfa - 1) * $limit;

// Toplam aktivite sayısı
$toplam_sorgu = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ?");
$toplam_sorgu->execute([$kullanici_id]);
$toplam_aktivite = $toplam_sorgu->fetchColumn();
$toplam_sayfa = ceil($toplam_aktivite / $limit);

// Aktiviteleri getir
$sorgu = $db->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$sorgu->execute([$kullanici_id, $limit, $offset]);
$aktiviteler = $sorgu->fetchAll();

// Aktivite ikonları ve açıklamaları
$aktivite_bilgileri = [
    'login' => ['icon' => '🔑', 'renk' => '#4CAF50', 'baslik' => 'Giriş Yapıldı'],
    'logout' => ['icon' => '🚪', 'renk' => '#FF5722', 'baslik' => 'Çıkış Yapıldı'],
    'profile_update' => ['icon' => '✏️', 'renk' => '#2196F3', 'baslik' => 'Profil Güncellendi'],
    'password_change' => ['icon' => '🔐', 'renk' => '#FF9800', 'baslik' => 'Şifre Değiştirildi'],
    'email_change' => ['icon' => '📧', 'renk' => '#9C27B0', 'baslik' => 'Email Değiştirildi'],
    'register' => ['icon' => '👤', 'renk' => '#00BCD4', 'baslik' => 'Kayıt Olundu']
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Geçmişi - Üye Sistemi</title>
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
        
        .logout-btn {
            background-color: #dc3545;
            color: white !important;
        }
        
        .logout-btn:hover {
            background-color: #c82333 !important;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .activity-header {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 20px;
        }
        
        .activity-header h2 {
            margin: 0 0 10px 0;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
        }
        
        .activity-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .activity-timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e8d5c4;
        }
        
        .activity-item {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e8d5c4;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .activity-icon {
            position: absolute;
            left: -30px;
            top: 20px;
            width: 40px;
            height: 40px;
            background-color: white;
            border: 2px solid #e8d5c4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .activity-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .activity-info h3 {
            margin: 0 0 5px 0;
            color: #1a1a1a;
            font-size: 16px;
            font-weight: 500;
        }
        
        .activity-description {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .activity-details {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            font-size: 13px;
            color: #999;
        }
        
        .activity-time {
            text-align: right;
            font-size: 13px;
            color: #999;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 15px;
            background-color: white;
            border: 1px solid #e8d5c4;
            border-radius: 4px;
            text-decoration: none;
            color: #1a1a1a;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: #1a1a1a;
            color: white;
        }
        
        .pagination .current {
            background-color: #1a1a1a;
            color: white;
        }
        
        .empty-state {
            background-color: white;
            padding: 60px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            text-align: center;
        }
        
        .empty-state h3 {
            color: #1a1a1a;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 16px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e8d5c4;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .activity-timeline {
                padding-left: 30px;
            }
            
            .activity-timeline::before {
                left: 15px;
            }
            
            .activity-icon {
                left: -25px;
                width: 30px;
                height: 30px;
                font-size: 16px;
            }
            
            .activity-content {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📊 AKTİVİTE GEÇMİŞİ</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <a href="notifications.php">Bildirimler</a>
            <a href="activity.php" class="active">Aktiviteler</a>
            <a href="settings.php">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="activity-header">
            <h2>Aktivite Geçmişi</h2>
            <p>Hesabınızdaki tüm işlemlerin detaylı kaydı</p>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-value"><?php echo $toplam_aktivite; ?></div>
                <div class="stat-label">Toplam Aktivite</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $_SESSION['kullanici_rol'] === 'admin' ? '👑' : '👤'; ?></div>
                <div class="stat-label">Hesap Türü: <?php echo ucfirst($_SESSION['kullanici_rol'] ?? 'user'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo date('d'); ?></div>
                <div class="stat-label"><?php echo strftime('%B %Y'); ?></div>
            </div>
        </div>
        
        <?php if(count($aktiviteler) > 0): ?>
            <div class="activity-timeline">
                <?php foreach($aktiviteler as $aktivite): ?>
                    <?php 
                    $bilgi = $aktivite_bilgileri[$aktivite['action']] ?? [
                        'icon' => '📌', 
                        'renk' => '#607D8B', 
                        'baslik' => ucfirst(str_replace('_', ' ', $aktivite['action']))
                    ];
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="border-color: <?php echo $bilgi['renk']; ?>;">
                            <?php echo $bilgi['icon']; ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-info">
                                <h3><?php echo $bilgi['baslik']; ?></h3>
                                <?php if($aktivite['description']): ?>
                                    <p class="activity-description">
                                        <?php echo htmlspecialchars($aktivite['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="activity-details">
                                    <span>IP: <?php echo htmlspecialchars($aktivite['ip_address']); ?></span>
                                    <span>Cihaz: <?php 
                                        $ua = $aktivite['user_agent'];
                                        if(strpos($ua, 'Mobile') !== false) echo '📱 Mobil';
                                        elseif(strpos($ua, 'Tablet') !== false) echo '📱 Tablet';
                                        else echo '💻 Masaüstü';
                                    ?></span>
                                </div>
                            </div>
                            <div class="activity-time">
                                <div><?php echo zamanFarki($aktivite['created_at']); ?></div>
                                <div style="font-size: 12px; margin-top: 5px;">
                                    <?php echo tarihSaatFormatla($aktivite['created_at']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($toplam_sayfa > 1): ?>
                <div class="pagination">
                    <?php if($sayfa > 1): ?>
                        <a href="?sayfa=1">İlk</a>
                        <a href="?sayfa=<?php echo $sayfa - 1; ?>">Önceki</a>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $sayfa - 2); $i <= min($toplam_sayfa, $sayfa + 2); $i++): ?>
                        <?php if($i == $sayfa): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?sayfa=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($sayfa < $toplam_sayfa): ?>
                        <a href="?sayfa=<?php echo $sayfa + 1; ?>">Sonraki</a>
                        <a href="?sayfa=<?php echo $toplam_sayfa; ?>">Son</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Henüz aktivite kaydınız yok</h3>
                <p>Hesabınızdaki işlemler burada görünecek.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>