<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = temizle($_POST['email']);
    
    if (empty($email)) {
        $hata = "Email adresi boş bırakılamaz!";
    } elseif (!emailGecerliMi($email)) {
        $hata = "Geçerli bir email adresi giriniz!";
    } else {
        try {
            $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
            $sorgu->execute([$email]);
            $kullanici = $sorgu->fetch();
            
            if ($kullanici) {
                // Token oluştur
                $token = tokenUret();
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Token'ı veritabanına kaydet
                $update = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $update->execute([$token, $expires, $kullanici['id']]);
                
                // Email gönderme simülasyonu (gerçek projede mail gönderilir)
                $reset_link = SITE_URL . "reset-password.php?token=" . $token;
                
                // Aktivite kaydı
                aktiviteKaydet($db, $kullanici['id'], 'password_reset_request', 'Şifre sıfırlama talebi');
                
                $mesaj = "Şifre sıfırlama linki email adresinize gönderildi. Link 1 saat süreyle geçerlidir.";
                
                // Geliştirme ortamında linki göster
                if ($_SERVER['SERVER_NAME'] == 'localhost') {
                    $mesaj .= "<br><br><strong>Test Linki:</strong><br><a href='$reset_link'>$reset_link</a>";
                }
            } else {
                // Güvenlik için kullanıcı bulunamasa bile aynı mesajı göster
                $mesaj = "Eğer email adresiniz sistemde kayıtlıysa, şifre sıfırlama linki gönderildi.";
            }
        } catch(PDOException $e) {
            $hata = "İşlem sırasında bir hata oluştu!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Üye Sistemi</title>
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
        
        input[type="email"] {
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
            animation: pulse 2s infinite;
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
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .info-box {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #b8daff;
            margin-top: 30px;
            font-size: 13px;
            color: #004085;
            line-height: 1.6;
        }
        
        .info-box h3 {
            margin-top: 0;
            font-size: 14px;
            font-weight: 600;
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
        <div class="icon">🔒</div>
        <h2>Şifremi Unuttum</h2>
        <p class="subtitle">Email adresinizi girin, size şifre sıfırlama linki gönderelim.</p>
        
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
                <label for="email">Email Adresiniz</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="ornek@email.com"
                       required 
                       autofocus>
            </div>
            
            <button type="submit">Şifre Sıfırlama Linki Gönder</button>
        </form>
        
        <div class="link">
            Şifrenizi hatırladınız mı? <a href="login.php">Giriş Yap</a>
        </div>
        
        <div class="info-box">
            <h3>Nasıl çalışır?</h3>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Email adresinizi girin</li>
                <li>Size bir şifre sıfırlama linki göndereceğiz</li>
                <li>Link 1 saat süreyle geçerli olacak</li>
                <li>Linke tıklayarak yeni şifrenizi belirleyin</li>
            </ul>
        </div>
    </div>
</body>
</html>