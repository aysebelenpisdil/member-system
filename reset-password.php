<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$mesaj = '';
$hata = '';
$token_gecerli = false;
$kullanici_id = null;

// Token kontrolü
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $sorgu = $db->prepare("SELECT id, reset_token_expires FROM users WHERE reset_token = ?");
        $sorgu->execute([$token]);
        $kullanici = $sorgu->fetch();
        
        if ($kullanici) {
            // Token süresi kontrolü
            if (strtotime($kullanici['reset_token_expires']) > time()) {
                $token_gecerli = true;
                $kullanici_id = $kullanici['id'];
            } else {
                $hata = "Şifre sıfırlama linkinizin süresi dolmuş. Lütfen yeni bir link talep edin.";
            }
        } else {
            $hata = "Geçersiz şifre sıfırlama linki.";
        }
    } catch(PDOException $e) {
        $hata = "Bir hata oluştu!";
    }
} else {
    header("Location: forgot-password.php");
    exit();
}

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_gecerli) {
    $yeni_sifre = $_POST['password'];
    $yeni_sifre_tekrar = $_POST['confirm_password'];
    
    if (empty($yeni_sifre) || empty($yeni_sifre_tekrar)) {
        $hata = "Tüm alanları doldurmanız gerekiyor!";
    } elseif ($yeni_sifre !== $yeni_sifre_tekrar) {
        $hata = "Şifreler eşleşmiyor!";
    } elseif (strlen($yeni_sifre) < PASSWORD_MIN_LENGTH) {
        $hata = "Şifre en az " . PASSWORD_MIN_LENGTH . " karakter olmalıdır!";
    } else {
        // Şifre gücü kontrolü
        $sifre_kontrolu = sifreGucuKontrol($yeni_sifre);
        
        if ($sifre_kontrolu['guc'] < 75) {
            $hata = "Şifreniz yeterince güçlü değil:<br>" . implode('<br>', $sifre_kontrolu['mesajlar']);
        } else {
            try {
                // Yeni şifreyi hashle ve güncelle
                $yeni_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                $guncelle = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $guncelle->execute([$yeni_hash, $kullanici_id]);
                
                // Aktivite kaydı
                aktiviteKaydet($db, $kullanici_id, 'password_reset', 'Şifre sıfırlama işlemi tamamlandı');
                
                // Bildirim ekle
                bildirimEkle($db, $kullanici_id, 'Şifre Değişikliği', 'Şifreniz başarıyla sıfırlandı. Eğer bu işlemi siz yapmadıysanız, lütfen hemen bizimle iletişime geçin.', 'warning');
                
                $mesaj = "Şifreniz başarıyla değiştirildi! Şimdi giriş yapabilirsiniz.";
                $token_gecerli = false; // Formu gizle
                
            } catch(PDOException $e) {
                $hata = "Bir hata oluştu, lütfen tekrar deneyin!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama - Üye Sistemi</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libertinus+Mono&display=swap');
        
        body {
            font-family: 'Libertinus Mono', monospace;
            background-color: #f5e6d3;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 420px;
            width: 100%;
            background-color: #ffffff;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            animation: fadeIn 0.6s ease;
        }
        
        h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 20px;
            font-weight: 400;
            font-size: 28px;
            letter-spacing: 1px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-size: 14px;
            line-height: 1.6;
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
        
        button {
            width: 100%;
            padding: 14px;
            background-color: #1a1a1a;
            color: #ffffff;
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
        
        button:hover {
            background-color: #000000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        button:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        button::before {
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
        
        button:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .link {
            text-align: center;
            margin-top: 30px;
            color: #666666;
            font-size: 14px;
        }
        
        .link a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 400;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s ease;
        }
        
        .link a:hover {
            border-bottom-color: #1a1a1a;
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
        
        .icon {
            display: block;
            text-align: center;
            font-size: 48px;
            margin-bottom: 20px;
            animation: rotate 2s linear infinite;
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
        
        .password-tips {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-top: 30px;
            font-size: 13px;
        }
        
        .password-tips h3 {
            margin-top: 0;
            font-size: 14px;
            color: #1a1a1a;
        }
        
        .password-tips ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #666;
            line-height: 1.6;
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
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔐</div>
        <h2>Yeni Şifre Belirle</h2>
        
        <?php if($mesaj): ?>
            <div class="alert alert-success">
                <?php echo $mesaj; ?>
            </div>
            <div class="link">
                <a href="login.php">Giriş Yap</a>
            </div>
        <?php elseif(!$token_gecerli && $hata): ?>
            <div class="alert alert-error">
                <?php echo $hata; ?>
            </div>
            <div class="link">
                <a href="forgot-password.php">Yeni Link Talep Et</a>
            </div>
        <?php else: ?>
            <p class="subtitle">Hesabınız için yeni bir şifre belirleyin.</p>
            
            <?php if($hata): ?>
                <div class="alert alert-error">
                    <?php echo $hata; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">Yeni Şifre</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           autofocus
                           onkeyup="checkPasswordStrength(this.value)">
                    <div id="password-strength" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Yeni Şifre (Tekrar)</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required>
                </div>
                
                <button type="submit">Şifreyi Değiştir</button>
            </form>
            
            <div class="password-tips">
                <h3>Güçlü Şifre İpuçları</h3>
                <ul>
                    <li>En az 8 karakter uzunluğunda olmalı</li>
                    <li>Büyük ve küçük harfler içermeli</li>
                    <li>En az bir rakam içermeli</li>
                    <li>Özel karakterler eklemek güvenliği artırır</li>
                </ul>
            </div>
        <?php endif; ?>
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