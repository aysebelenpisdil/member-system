<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];
$mesaj = '';
$hata = '';

// Kullanıcı bilgilerini al
$sorgu = $db->prepare("SELECT * FROM users WHERE id = ?");
$sorgu->execute([$kullanici_id]);
$kullanici = $sorgu->fetch();

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = temizle($_POST['name']);
    $phone = temizle($_POST['phone']);
    $bio = temizle($_POST['bio']);
    $address = temizle($_POST['address']);
    $birth_date = $_POST['birth_date'];
    
    // Profil fotoğrafı yükleme
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        if (dosyaKontrol($_FILES['profile_image'])) {
            $yeni_ad = guvenliDosyaAdi($_FILES['profile_image']['name']);
            $hedef = PROFILE_IMAGES_PATH . $yeni_ad;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $hedef)) {
                // Eski resmi sil
                if ($kullanici['profile_image'] && file_exists(PROFILE_IMAGES_PATH . $kullanici['profile_image'])) {
                    unlink(PROFILE_IMAGES_PATH . $kullanici['profile_image']);
                }
                
                // Veritabanını güncelle
                $img_update = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $img_update->execute([$yeni_ad, $kullanici_id]);
                $kullanici['profile_image'] = $yeni_ad;
            }
        } else {
            $hata = "Geçersiz dosya formatı! (JPG, PNG, GIF)";
        }
    }
    
    if (!$hata) {
        try {
            $update = $db->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, address = ?, birth_date = ? WHERE id = ?");
            $update->execute([$name, $phone, $bio, $address, $birth_date, $kullanici_id]);
            
            $_SESSION['kullanici_adi'] = $name;
            
            aktiviteKaydet($db, $kullanici_id, 'profile_update', 'Profil bilgileri güncellendi');
            
            $mesaj = "Profil bilgileriniz başarıyla güncellendi!";
            
            // Güncel bilgileri çek
            $sorgu->execute([$kullanici_id]);
            $kullanici = $sorgu->fetch();
            
        } catch(PDOException $e) {
            $hata = "Güncelleme sırasında bir hata oluştu!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Üye Sistemi</title>
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
        
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .profile-sidebar {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            text-align: center;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background-color: #e8d5c4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #1a1a1a;
            overflow: hidden;
            position: relative;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .upload-btn {
            background-color: #1a1a1a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Libertinus Mono', monospace;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .upload-btn:hover {
            background-color: #000;
            transform: translateY(-1px);
        }
        
        .profile-info {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
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
        input[type="tel"],
        input[type="date"],
        textarea {
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
        
        input:focus,
        textarea:focus {
            outline: none;
            border-color: #1a1a1a;
            background-color: white;
            box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-primary {
            background-color: #1a1a1a;
            color: white;
            padding: 14px 30px;
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
        
        .btn-primary:hover {
            background-color: #000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        
        .stats-box {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e8d5c4;
        }
        
        .stat-item {
            margin: 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .stat-item strong {
            color: #1a1a1a;
        }
        
        input[type="file"] {
            display: none;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
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
        <h1>👤 PROFİLİM</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php" class="active">Profilim</a>
            <a href="settings.php">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
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
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php if($kullanici['profile_image']): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($kullanici['profile_image']); ?>" alt="Profil Fotoğrafı">
                    <?php else: ?>
                        <?php echo strtoupper(substr($kullanici['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                
                <h3><?php echo htmlspecialchars($kullanici['name']); ?></h3>
                <p style="color: #666; margin: 10px 0;"><?php echo htmlspecialchars($kullanici['email']); ?></p>
                
                <form method="POST" enctype="multipart/form-data" id="avatarForm">
                    <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="document.getElementById('avatarForm').submit();">
                    <label for="profile_image" class="upload-btn">Fotoğraf Değiştir</label>
                </form>
                
                <div class="stats-box">
                    <div class="stat-item">
                        <strong>Üye No:</strong> #<?php echo str_pad($kullanici['id'], 6, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div class="stat-item">
                        <strong>Kayıt Tarihi:</strong><br><?php echo tarihFormatla($kullanici['created_at']); ?>
                    </div>
                    <div class="stat-item">
                        <strong>Son Giriş:</strong><br><?php echo $kullanici['last_login'] ? tarihSaatFormatla($kullanici['last_login']) : 'İlk giriş'; ?>
                    </div>
                    <div class="stat-item">
                        <strong>Toplam Giriş:</strong> <?php echo $kullanici['login_count'] ?? 0; ?>
                    </div>
                </div>
            </div>
            
            <div class="profile-info">
                <h2 style="margin-top: 0; margin-bottom: 30px;">Profil Bilgileri</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Ad Soyad</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($kullanici['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($kullanici['email']); ?>" disabled style="cursor: not-allowed; opacity: 0.6;">
                        <small style="color: #666; display: block; margin-top: 5px;">Email adresi değiştirilemez</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Telefon</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($kullanici['phone'] ?? ''); ?>" placeholder="+90 (555) 123 45 67">
                    </div>
                    
                    <div class="form-group">
                        <label for="birth_date">Doğum Tarihi</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo $kullanici['birth_date'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Hakkımda</label>
                        <textarea id="bio" name="bio" placeholder="Kendinizden bahsedin..."><?php echo htmlspecialchars($kullanici['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" placeholder="Adres bilgileriniz..."><?php echo htmlspecialchars($kullanici['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">Değişiklikleri Kaydet</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>