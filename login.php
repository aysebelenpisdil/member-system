<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (girisYapmisMi()) {
    header("Location: dashboard.php");
    exit();
}

$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = temizle($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $hata = "Email ve şifre alanları boş bırakılamaz!";
    } else {
        // Giriş denemesi kaydı
        $ip = ipAdresiAl();
        try {
            // Son 15 dakikadaki başarısız denemeleri kontrol et
            $lockout_time = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);
            $check_attempts = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0 AND attempted_at > ?");
            $check_attempts->execute([$email, $lockout_time]);
            $failed_attempts = $check_attempts->fetchColumn();
            
            if ($failed_attempts >= MAX_LOGIN_ATTEMPTS) {
                $hata = "Çok fazla başarısız deneme! Lütfen 15 dakika sonra tekrar deneyin.";
            } else {
                $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
                $sorgu->execute([$email]);
                $kullanici = $sorgu->fetch();
                
                if ($kullanici) {
                    if ($kullanici['status'] === 'banned') {
                        $hata = "Hesabınız engellenmiş!";
                        $log_attempt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
                        $log_attempt->execute([$email, $ip]);
                    } elseif ($kullanici['status'] === 'inactive') {
                        $hata = "Hesabınız aktif değil!";
                        $log_attempt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
                        $log_attempt->execute([$email, $ip]);
                    } elseif (password_verify($password, $kullanici['password'])) {
                        // Başarılı giriş
                        $_SESSION['kullanici_id'] = $kullanici['id'];
                        $_SESSION['kullanici_adi'] = $kullanici['name'];
                        $_SESSION['kullanici_email'] = $kullanici['email'];
                        $_SESSION['kullanici_rol'] = $kullanici['role'] ?? 'user';
                        $_SESSION['last_activity'] = time();
                        
                        // Başarılı giriş kaydı
                        $log_attempt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 1)");
                        $log_attempt->execute([$email, $ip]);
                        
                        // Kullanıcı bilgilerini güncelle
                        $update_user = $db->prepare("UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = ?");
                        $update_user->execute([$kullanici['id']]);
                        
                        // Aktivite kaydı
                        aktiviteKaydet($db, $kullanici['id'], 'login', 'Sisteme giriş yapıldı');
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $hata = "Şifre yanlış!";
                        $log_attempt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
                        $log_attempt->execute([$email, $ip]);
                    }
                } else {
                    $hata = "Bu email adresi ile kayıtlı kullanıcı bulunamadı!";
                    $log_attempt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
                    $log_attempt->execute([$email, $ip]);
                }
            }
        } catch(PDOException $e) {
            $hata = "Giriş sırasında bir hata oluştu!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Üye Sistemi</title>
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
        
        .links-row {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e8d5c4;
        }
        
        .links-row a {
            color: #1a1a1a;
            text-decoration: none;
            font-size: 13px;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s ease;
        }
        
        .links-row a:hover {
            border-bottom-color: #1a1a1a;
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
        
        .login-icon {
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><span class="login-icon">🔐</span>Giriş Yap</h2>
        
        <?php if($hata): ?>
            <div class="alert alert-error">
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Adresiniz</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required>
            </div>
            
            <button type="submit">Giriş Yap</button>
        </form>
        
        <div class="links-row">
            <a href="forgot-password.php">Şifremi Unuttum</a>
            <a href="register.php">Kayıt Ol</a>
        </div>
    </div>
</body>
</html>