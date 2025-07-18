<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$hata = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || !csrfTokenKontrol($_POST['csrf_token'])) {
        $hata = "Güvenlik hatası! Lütfen formu yeniden gönderin.";
    } else {
        // Form verileri
        $name = temizle($_POST['name']);
        $email = temizle($_POST['email']);
        $password = $_POST['password'];
        $password2 = $_POST['password2'];
        $terms = isset($_POST['terms']) ? true : false;
        
        // Validasyonlar
        if (empty($name)) {
            $hata = "İsim alanı boş bırakılamaz!";
        }
        elseif (strlen($name) < 3) {
            $hata = "İsim en az 3 karakter olmalıdır!";
        }
        elseif (empty($email)) {
            $hata = "Email alanı boş bırakılamaz!";
        }
        elseif (!emailGecerliMi($email)) {
            $hata = "Geçerli bir email adresi giriniz!";
        }
        elseif (empty($password)) {
            $hata = "Şifre alanı boş bırakılamaz!";
        }
        elseif (strlen($password) < 6) {
            $hata = "Şifre en az 6 karakter olmalıdır!";
        }
        elseif ($password !== $password2) {
            $hata = "Şifreler eşleşmiyor!";
        }
        elseif (!$terms) {
            $hata = "Kullanım şartlarını kabul etmelisiniz!";
        }
        else {
            // Şifre gücü kontrolü
            $sifre_kontrolu = sifreGucuKontrol($password);
            if ($sifre_kontrolu['guc'] < 50) {
                $hata = "Şifreniz yeterince güçlü değil:<br>" . implode('<br>', $sifre_kontrolu['mesajlar']);
            } else {
                try {
                    // Email kontrolü
                    $kontrol = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $kontrol->execute([$email]);
                    
                    if ($kontrol->rowCount() > 0) {
                        $hata = "Bu email adresi zaten kullanılıyor!";
                    } else {
                        // Doğrulama token'ı oluştur
                        $verification_token = tokenUret();
                        
                        // Şifreyi hashle
                        $hashlenmis_sifre = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Kullanıcıyı kaydet
                        $kayit = $db->prepare("
                            INSERT INTO users (name, email, password, verification_token, created_at) 
                            VALUES (?, ?, ?, ?, NOW())
                        ");
                        $sonuc = $kayit->execute([$name, $email, $hashlenmis_sifre, $verification_token]);
                        
                        if ($sonuc) {
                            $yeni_kullanici_id = $db->lastInsertId();
                            
                            // Aktivite kaydı
                            aktiviteKaydet($db, $yeni_kullanici_id, 'register', 'Yeni kayıt oluşturuldu');
                            
                            // Hoşgeldin bildirimi
                            bildirimEkle($db, $yeni_kullanici_id, 'Hoş Geldiniz!', 
                                'Üye sistemimize hoş geldiniz. Email adresinizi doğrulamayı unutmayın.', 'info');
                            
                            // Email gönderme simülasyonu
                            $verification_link = SITE_URL . "verify-email.php?token=" . $verification_token;
                            
                            $basari = "Kayıt başarılı! Email adresinize doğrulama linki gönderildi.";
                            
                            // Geliştirme ortamında linki göster
                            if ($_SERVER['SERVER_NAME'] == 'localhost') {
                                $basari .= "<br><br><strong>Test Linki:</strong><br>
                                           <a href='$verification_link' style='color: #1a1a1a;'>$verification_link</a>";
                            }
                            
                            // Formu temizle
                            $name = $email = '';
                        } else {
                            $hata = "Kayıt sırasında bir hata oluştu!";
                        }
                    }
                } catch(PDOException $e) {
                    $hata = "Veritabanı hatası!";
                }
            }
        }
    }
}

// CSRF token oluştur
$csrf_token = csrfTokenOlustur();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Üye Sistemi</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libertinus+Mono&display=swap');
        
        body {
            font-family: 'Libertinus Mono', monospace;
            background-color: #f5e6d3;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 420px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 40px;
            font-weight: 400;
            font-size: 28px;
            letter-spacing: 1px;
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
        
        input[type="text"],
        input[type="email"],
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
        
        .checkbox-group {
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-top: 5px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin-bottom: 0;
            font-size: 14px;
            text-transform: none;
            letter-spacing: normal;
            cursor: pointer;
        }
        
        .checkbox-group a {
            color: #1a1a1a;
            text-decoration: underline;
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
        
        button {
            position: relative;
            overflow: hidden;
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
        
        small {
            color: #888888;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        
        .container {
            animation: fadeIn 0.6s ease;
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }
        
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background-color: #efe;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .alert a {
            color: inherit;
            text-decoration: underline;
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
        
        .password-requirements {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
        
        .password-requirements h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #1a1a1a;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            line-height: 1.8;
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 20px auto;
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
        <h2>Kayıt Ol</h2>
        
        <?php if($hata): ?>
            <div class="alert alert-error">
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>
        
        <?php if($basari): ?>
            <div class="alert alert-success">
                <?php echo $basari; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!$basari): ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="name">Adınız Soyadınız</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" 
                       required 
                       minlength="3"
                       autofocus>
                <small>En az 3 karakter olmalıdır</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email Adresiniz</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                       required>
                <small>Doğrulama linki bu adrese gönderilecek</small>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       minlength="6"
                       onkeyup="checkPasswordStrength(this.value)">
                <div id="password-strength" class="password-strength"></div>
            </div>
            
            <div class="form-group">
                <label for="password2">Şifre (Tekrar)</label>
                <input type="password" 
                       id="password2" 
                       name="password2" 
                       required 
                       minlength="6">
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    <a href="#" onclick="alert('Kullanım şartları sayfası henüz hazır değil.'); return false;">
                        Kullanım şartlarını
                    </a> ve 
                    <a href="#" onclick="alert('Gizlilik politikası sayfası henüz hazır değil.'); return false;">
                        gizlilik politikasını
                    </a> okudum ve kabul ediyorum
                </label>
            </div>
            
            <button type="submit">Kayıt Ol</button>
        </form>
        
        <div class="password-requirements">
            <h4>Güçlü Şifre İpuçları</h4>
            <ul>
                <li>En az 8 karakter uzunluğunda olmalı</li>
                <li>Büyük ve küçük harfler içermeli</li>
                <li>En az bir rakam içermeli</li>
                <li>Özel karakterler güvenliği artırır</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="link">
            Zaten üye misiniz? <a href="login.php">Giriş Yap</a>
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
            
            if (password.length > 0) {
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
            } else {
                strengthDiv.style.display = 'none';
            }
        }
        
        // Form doğrulama
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const password2 = document.getElementById('password2').value;
            
            if (password !== password2) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor!');
                return false;
            }
        });
    </script>
</body>
</html>