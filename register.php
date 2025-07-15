<?php
// Gerekli dosyaları dahil et
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Hata ve başarı mesajları için değişkenler
$hata = '';
$basari = '';

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verileri geldi, işlemleri yapalım
    
    // 1. Verileri al ve temizle
    $name = temizle($_POST['name']);
    $email = temizle($_POST['email']);
    $password = $_POST['password']; // Şifreyi temizleme, olduğu gibi al
    $password2 = $_POST['password2'];
    
    // 2. Boş alan kontrolü
    if (empty($name)) {
        $hata = "İsim alanı boş bırakılamaz!";
    }
    elseif (empty($email)) {
        $hata = "Email alanı boş bırakılamaz!";
    }
    elseif (empty($password)) {
        $hata = "Şifre alanı boş bırakılamaz!";
    }
    // 3. Email format kontrolü
    elseif (!emailGecerliMi($email)) {
        $hata = "Geçerli bir email adresi giriniz!";
    }
    // 4. Şifre uzunluk kontrolü
    elseif (strlen($password) < 6) {
        $hata = "Şifre en az 6 karakter olmalıdır!";
    }
    // 5. Şifrelerin eşleşme kontrolü
    elseif ($password !== $password2) {
        $hata = "Şifreler eşleşmiyor!";
    }
    else {
        // Tüm kontroller tamam, veritabanı işlemlerine geç
        
        // 6. Email daha önce kullanılmış mı?
        try {
            $kontrol = $db->prepare("SELECT id FROM users WHERE email = ?");
            $kontrol->execute([$email]);
            
            if ($kontrol->rowCount() > 0) {
                $hata = "Bu email adresi zaten kullanılıyor!";
            }
            else {
                // 7. Her şey tamam, kullanıcıyı kaydet
                
                // Şifreyi hashle (güvenlik için)
                $hashlenmis_sifre = password_hash($password, PASSWORD_DEFAULT);
                
                // Veritabanına kaydet
                $kayit = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $sonuc = $kayit->execute([$name, $email, $hashlenmis_sifre]);
                
                if ($sonuc) {
                    $basari = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
                    // Formu temizle
                    $name = $email = '';
                } else {
                    $hata = "Kayıt sırasında bir hata oluştu!";
                }
            }
            
        } catch(PDOException $e) {
            $hata = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}
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
            background-color: #f5e6d3; /* Krem rengi arkaplan */
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
            border: 1px solid #e8d5c4; /* Hafif krem kenarlık */
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
        
        button {
            width: 100%;
            padding: 14px;
            background-color: #1a1a1a; /* Siyah buton */
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
        
        /* Claude tarzı hover efekti */
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
        
        /* Form animasyonu */
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
        
        /* Mesaj kutuları */
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
        
        /* Responsive */
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
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Adınız Soyadınız</label>
                <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Adresiniz</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required>
                <small>En az 6 karakter olmalıdır</small>
            </div>
            
            <div class="form-group">
                <label for="password2">Şifre (Tekrar)</label>
                <input type="password" id="password2" name="password2" required>
            </div>
            
            <button type="submit">Kayıt Ol</button>
        </form>
        
        <div class="link">
            Zaten üye misiniz? <a href="login.php">Giriş Yap</a>
        </div>
    </div>
</body>
</html>