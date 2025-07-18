<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$mesaj = '';
$hata = '';
$basarili = false;

// Token kontrolü
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Token'a sahip kullanıcıyı bul
        $sorgu = $db->prepare("SELECT id, name, email, email_verified FROM users WHERE verification_token = ?");
        $sorgu->execute([$token]);
        $kullanici = $sorgu->fetch();
        
        if ($kullanici) {
            if ($kullanici['email_verified']) {
                $mesaj = "Email adresiniz zaten doğrulanmış. Giriş yapabilirsiniz.";
                $basarili = true;
            } else {
                // Email'i doğrula
                $guncelle = $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
                $guncelle->execute([$kullanici['id']]);
                
                // Aktivite kaydı
                aktiviteKaydet($db, $kullanici['id'], 'email_verified', 'Email adresi doğrulandı');
                
                // Bildirim ekle
                bildirimEkle($db, $kullanici['id'], 'Email Doğrulandı', 
                    'Email adresiniz başarıyla doğrulandı. Artık tüm özellikleri kullanabilirsiniz.', 'success');
                
                $mesaj = "Tebrikler! Email adresiniz başarıyla doğrulandı. Artık giriş yapabilirsiniz.";
                $basarili = true;
            }
        } else {
            $hata = "Geçersiz doğrulama linki. Link süresi dolmuş veya hatalı olabilir.";
        }
    } catch(PDOException $e) {
        $hata = "Doğrulama sırasında bir hata oluştu!";
    }
} else {
    $hata = "Doğrulama token'ı bulunamadı!";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Doğrulama - Üye Sistemi</title>
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
            max-width: 500px;
            width: 100%;
            background-color: #ffffff;
            padding: 60px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 30px;
            display: block;
        }
        
        .icon.success {
            animation: bounce 0.5s ease;
        }
        
        .icon.error {
            animation: shake 0.5s ease;
        }
        
        h1 {
            color: #1a1a1a;
            margin-bottom: 20px;
            font-weight: 400;
            font-size: 28px;
            letter-spacing: 1px;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 40px;
        }
        
        .message.success {
            color: #155724;
        }
        
        .message.error {
            color: #721c24;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 30px;
            background-color: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 400;
            font-family: 'Libertinus Mono', monospace;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            background-color: #000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .btn::before {
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
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-top: 40px;
            border: 1px solid #e9ecef;
        }
        
        .info-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
            color: #1a1a1a;
        }
        
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #666;
            line-height: 1.6;
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
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-10px);
            }
            75% {
                transform: translateX(10px);
            }
        }
        
        .loader {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #1a1a1a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px 0;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 20px;
                padding: 40px 20px;
            }
            
            .icon {
                font-size: 60px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if($basarili): ?>
            <span class="icon success">✅</span>
            <h1>Email Doğrulandı!</h1>
            <p class="message success"><?php echo $mesaj; ?></p>
            <a href="login.php" class="btn">Giriş Yap</a>
            
            <div class="info-box">
                <h3>Sonraki Adımlar</h3>
                <p>
                    Email adresiniz doğrulandığına göre, artık hesabınıza giriş yapabilir ve 
                    tüm özellikleri kullanmaya başlayabilirsiniz. Güvenliğiniz için şifrenizi 
                    düzenli olarak değiştirmeyi unutmayın.
                </p>
            </div>
        <?php else: ?>
            <span class="icon error">❌</span>
            <h1>Doğrulama Başarısız</h1>
            <p class="message error"><?php echo $hata; ?></p>
            <a href="login.php" class="btn">Giriş Sayfasına Git</a>
            
            <div class="info-box">
                <h3>Sorun mu yaşıyorsunuz?</h3>
                <p>
                    Eğer email doğrulama linkiniz çalışmıyorsa, lütfen giriş yaptıktan sonra 
                    profil sayfanızdan yeni bir doğrulama linki talep edin veya destek ekibimizle 
                    iletişime geçin.
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Otomatik yönlendirme
        <?php if($basarili): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 10000); // 10 saniye sonra login sayfasına yönlendir
        <?php endif; ?>
    </script>
</body>
</html>