<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];
$kullanici_adi = $_SESSION['kullanici_adi'];
$kullanici_email = $_SESSION['kullanici_email'];

try {
    $sorgu = $db->prepare("SELECT * FROM users WHERE id = ?");
    $sorgu->execute([$kullanici_id]);
    $kullanici = $sorgu->fetch();
    
    $kayit_tarihi = date('d.m.Y H:i', strtotime($kullanici['created_at']));
    
} catch(PDOException $e) {
    die("Veritabanı hatası!");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - <?php echo htmlspecialchars($kullanici_adi); ?></title>
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
        
        .welcome-card {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1a1a1a, #333, #1a1a1a);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .welcome-card h2 {
            margin: 0 0 10px 0;
            font-size: 32px;
            color: #1a1a1a;
            font-weight: 400;
        }
        
        .welcome-card p {
            color: #666;
            margin: 0;
            font-size: 16px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e8d5c4;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .info-card h3 {
            margin: 0 0 20px 0;
            color: #1a1a1a;
            font-size: 18px;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card .icon {
            font-size: 24px;
        }
        
        .info-card p {
            margin: 10px 0;
            color: #666;
            font-size: 14px;
        }
        
        .info-card strong {
            color: #1a1a1a;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 ÜYE PANELİ</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <a href="settings.php">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-card">
            <h2>Hoş Geldin, <?php echo htmlspecialchars($kullanici_adi); ?>! 👋</h2>
            <p>Üye panelinize başarıyla giriş yaptınız.</p>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3><span class="icon">👤</span> Profil Bilgileri</h3>
                <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($kullanici['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($kullanici['email']); ?></p>
                <p><strong>Üye No:</strong> #<?php echo str_pad($kullanici['id'], 6, '0', STR_PAD_LEFT); ?></p>
            </div>
            
            <div class="info-card">
                <h3><span class="icon">📅</span> Üyelik Bilgileri</h3>
                <p><strong>Kayıt Tarihi:</strong> <?php echo $kayit_tarihi; ?></p>
                <p><strong>Üyelik Türü:</strong> Standart</p>
                <p><strong>Durum:</strong> <span style="color: #28a745;">Aktif</span></p>
            </div>
            
            <div class="info-card">
                <h3><span class="icon">🔐</span> Güvenlik</h3>
                <p><strong>Son Giriş:</strong> Az önce</p>
                <p><strong>2FA:</strong> <span style="color: #dc3545;">Pasif</span></p>
                <p><strong>Şifre Gücü:</strong> <span style="color: #ffc107;">Orta</span></p>
            </div>
        </div>
    
        <div class="action-grid">
            <a href="profile.php" class="action-btn">
                <span class="icon">✏️</span>
                <span>Profili Düzenle</span>
            </a>
            
            <a href="change-password.php" class="action-btn">
                <span class="icon">🔑</span>
                <span>Şifre Değiştir</span>
            </a>
            
            <a href="settings.php" class="action-btn">
                <span class="icon">⚙️</span>
                <span>Ayarlar</span>
            </a>
            
            <a href="support.php" class="action-btn">
                <span class="icon">💬</span>
                <span>Destek</span>
            </a>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2024 Üye Paneli. Tüm hakları saklıdır.</p>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.info-card, .action-btn');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>