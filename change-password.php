<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];
$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mevcut_sifre = $_POST['current_password'];
    $yeni_sifre = $_POST['new_password'];
    $yeni_sifre_tekrar = $_POST['confirm_password'];
    
    if (empty($mevcut_sifre) || empty($yeni_sifre) || empty($yeni_sifre_tekrar)) {
        $hata = "Tüm alanları doldurmanız gerekiyor!";
    } elseif ($yeni_sifre !== $yeni_sifre_tekrar) {
        $hata = "Yeni şifreler eşleşmiyor!";
    } elseif (strlen($yeni_sifre) < PASSWORD_MIN_LENGTH) {
        $hata = "Yeni şifre en az " . PASSWORD_MIN_LENGTH . " karakter olmalıdır!";
    } else {
        try {
            // Mevcut şifreyi kontrol et
            $sorgu = $db->prepare("SELECT password FROM users WHERE id = ?");
            $sorgu->execute([$kullanici_id]);
            $kullanici = $sorgu->fetch();
            
            if (password_verify($mevcut_sifre, $kullanici['password'])) {
                // Şifre gücü kontrolü
                $sifre_kontrolu = sifreGucuKontrol($yeni_sifre);
                
                if ($sifre_kontrolu['guc'] < 75) {
                    $hata = "Şifreniz yeterince güçlü değil:<br>" . implode('<br>', $sifre_kontrolu['mesajlar']);
                } else {
                    // Yeni şifreyi hashle ve güncelle
                    $yeni_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                    $guncelle = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $guncelle->execute([$yeni_hash, $kullanici_id]);
                    
                    // Aktivite kaydı
                    aktiviteKaydet($db, $kullanici_id, 'password_change', 'Şifre değiştirildi');
                    
                    // Bildirim ekle
                    bildirimEkle($db, $kullanici_id, 'Şifre Değişikliği', 'Şifreniz başarıyla değiştirildi. Eğer bu işlemi siz yapmadıysanız, lütfen hemen bizimle iletişime geçin.', 'warning');
                    
                    $mesaj = "Şifreniz başarıyla değiştirildi!";
                }
            } else {
                $hata = "Mevcut şifreniz yanlış!";
            }
        } catch(PDOException $e) {
            $hata = "Bir hata oluştu, lütfen tekrar deneyin!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - Üye Sistemi</title>
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
            max-width: 500px;
            margin: 0 auto;
        }
        
        .password-card {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #2d2d2d;
            font-weight: 400;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Libertinus Mono', monospace;
            transition: all 0.2s ease;
            background-color: #fafafa;
        }
        
        input:focus {
            outline: none;
            border-color: #1a1a1a;
            background-color: white;
            box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
        }
        
        .password-strength {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
            display: none;
        }
        
        .strength-weak {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .strength-medium {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .strength-strong {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background-color: #1a1a1a;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 400;
            font-family: 'Libertinus Mono', monospace;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background-color: #000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
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
        
        .password-tips {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #e8d5c4;
        }
        
        .password-tips h3 {
            margin-top: 0;
            font-size: 16px;
            color: #1a1a1a;
        }
        
        .password-tips ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #666;
            font-size: 14px;
            line-height: 1.8;
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
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🔑 ŞİFRE DEĞİŞTİR</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <a href="settings.php">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="password-card">
            <h2>Şifre Değiştir</h2>
            
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
            
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Mevcut Şifre</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Yeni Şifre</label>
                    <input type="password" id="new_password" name="new_password" required onkeyup="checkPasswordStrength(this.value)">
                    <div id="password-strength" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Yeni Şifre (Tekrar)</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-primary">Şifreyi Değiştir</button>
            </form>
            
            <div class="password-tips">
                <h3>Güçlü Şifre İpuçları</h3>
                <ul>
                    <li>En az 8 karakter uzunluğunda olmalı</li>
                    <li>Büyük ve küçük harfler içermeli</li>
                    <li>En az bir rakam içermeli</li>
                    <li>Özel karakterler eklemek güvenliği artırır</li>
                    <li>Kişisel bilgilerinizi kullanmayın</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        function checkPasswordStrength(password) {
            const strengthDiv = document.getElementById('password-strength');
            let strength = 0;
            let message = '';
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            
            strengthDiv.style.display = 'block';
            
            if (strength <= 25) {
                strengthDiv.className = 'password-strength strength-weak';
                message = 'Zayıf şifre';
            } else if (strength <= 50) {
                strengthDiv.className = 'password-strength strength-medium';
                message = 'Orta güçte şifre';
            } else {
                strengthDiv.className = 'password-strength strength-strong';
                message = 'Güçlü şifre';
            }
            
            strengthDiv.textContent = message + ' (' + strength + '%)';
        }
    </script>
</body>
</html>