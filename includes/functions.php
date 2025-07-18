<?php
// Bu dosya tüm yardımcı fonksiyonlarımızı içerir

// 1. Basit selamlama fonksiyonu
function merhaba() {
    echo "Merhaba Dünya!";
}

// 2. İsim alıp selamlayan fonksiyon
function selamla($isim) {
    echo "Merhaba, " . $isim . "!";
}

// 3. İki sayıyı toplayıp sonucu döndüren fonksiyon
function topla($sayi1, $sayi2) {
    return $sayi1 + $sayi2;
}

// 4. Kullanıcıdan gelen veriyi güvenli hale getiren fonksiyon
function temizle($veri) {
    $veri = trim($veri);
    $veri = stripslashes($veri);
    $veri = htmlspecialchars($veri);
    return $veri;
}

// 5. Email adresinin geçerli olup olmadığını kontrol eden fonksiyon
function emailGecerliMi($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 6. Daha kısa email kontrol fonksiyonu
function emailKontrol($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 7. Değişken içeriğini detaylı gösteren hata ayıklama fonksiyonu
function debug($degisken) {
    echo "<pre>";
    print_r($degisken);
    echo "</pre>";
}

// 8. Şifre yeterince güçlü mü kontrol eden fonksiyon
function sifreGucluMu($sifre) {
    if (strlen($sifre) < 6) {
        return false;
    }
    return true;
}

// 9. Rastgele güvenlik kodu üreten fonksiyon
function guvenlikKoduUret() {
    return rand(100000, 999999);
}

// 10. Tarih formatını Türkçe yapan fonksiyon
function tarihFormatla($tarih) {
    return date("d.m.Y", strtotime($tarih));
}

// 11. Kullanıcı giriş yapmış mı kontrol eden fonksiyon
function girisYapmisMi() {
    return isset($_SESSION['kullanici_id']) && !empty($_SESSION['kullanici_id']);
}

// 12. Giriş yapmamışsa login sayfasına yönlendir
function girisKontrol() {
    if (!girisYapmisMi()) {
        header("Location: login.php");
        exit();
    }
}

// 13. Başarı mesajı göster
function basariMesaji($mesaj) {
    return '<div class="alert alert-success">' . $mesaj . '</div>';
}

// 14. Hata mesajı göster  
function hataMesaji($mesaj) {
    return '<div class="alert alert-error">' . $mesaj . '</div>';
}

// 15. Bilgi mesajı göster
function bilgiMesaji($mesaj) {
    return '<div class="alert alert-info">' . $mesaj . '</div>';
}

// 16. Uyarı mesajı göster
function uyariMesaji($mesaj) {
    return '<div class="alert alert-warning">' . $mesaj . '</div>';
}

// 17. Rastgele token üret (email doğrulama, şifre sıfırlama için)
function tokenUret($uzunluk = 32) {
    return bin2hex(random_bytes($uzunluk));
}

// 18. Kullanıcının rolünü kontrol et
function rolKontrol($rol) {
    if (!girisYapmisMi()) {
        return false;
    }
    return $_SESSION['kullanici_rol'] === $rol;
}

// 19. Admin mi kontrol et
function adminMi() {
    return rolKontrol('admin');
}

// 20. Tarih-saat formatla
function tarihSaatFormatla($tarih) {
    return date("d.m.Y H:i", strtotime($tarih));
}

// 21. Aktivite kaydı ekle
function aktiviteKaydet($db, $kullanici_id, $aksiyon, $aciklama = '') {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Bilinmiyor';
        
        $sorgu = $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        return $sorgu->execute([$kullanici_id, $aksiyon, $aciklama, $ip, $user_agent]);
    } catch(PDOException $e) {
        return false;
    }
}

// 22. Bildirim ekle
function bildirimEkle($db, $kullanici_id, $baslik, $mesaj, $tip = 'info') {
    try {
        $sorgu = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        return $sorgu->execute([$kullanici_id, $baslik, $mesaj, $tip]);
    } catch(PDOException $e) {
        return false;
    }
}

// 23. Okunmamış bildirim sayısı
function okunmamisBildirimSayisi($db, $kullanici_id) {
    try {
        $sorgu = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $sorgu->execute([$kullanici_id]);
        return $sorgu->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}

// 24. Dosya yükleme kontrolü
function dosyaKontrol($dosya, $izinli_uzantilar = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($dosya['error']) || $dosya['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $dosya_adi = $dosya['name'];
    $uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
    
    if (!in_array($uzanti, $izinli_uzantilar)) {
        return false;
    }
    
    // Boyut kontrolü (5MB)
    if ($dosya['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    return true;
}

// 25. Güvenli dosya adı oluştur
function guvenliDosyaAdi($dosya_adi) {
    $uzanti = pathinfo($dosya_adi, PATHINFO_EXTENSION);
    $yeni_ad = uniqid() . '_' . time() . '.' . $uzanti;
    return $yeni_ad;
}

// 26. Şifre gücü detaylı kontrol
function sifreGucuKontrol($sifre) {
    $guc = 0;
    $mesajlar = [];
    
    // Uzunluk kontrolü
    if (strlen($sifre) >= 8) {
        $guc += 25;
    } else {
        $mesajlar[] = "En az 8 karakter olmalı";
    }
    
    // Büyük harf kontrolü
    if (preg_match('/[A-Z]/', $sifre)) {
        $guc += 25;
    } else {
        $mesajlar[] = "En az bir büyük harf içermeli";
    }
    
    // Küçük harf kontrolü
    if (preg_match('/[a-z]/', $sifre)) {
        $guc += 25;
    } else {
        $mesajlar[] = "En az bir küçük harf içermeli";
    }
    
    // Rakam kontrolü
    if (preg_match('/[0-9]/', $sifre)) {
        $guc += 25;
    } else {
        $mesajlar[] = "En az bir rakam içermeli";
    }
    
    return [
        'guc' => $guc,
        'mesajlar' => $mesajlar
    ];
}

// 27. Zaman farkını hesapla (5 dakika önce, 2 saat önce gibi)
function zamanFarki($tarih) {
    $simdi = time();
    $gecmis = strtotime($tarih);
    $fark = $simdi - $gecmis;
    
    if ($fark < 60) {
        return "Az önce";
    } elseif ($fark < 3600) {
        $dakika = floor($fark / 60);
        return $dakika . " dakika önce";
    } elseif ($fark < 86400) {
        $saat = floor($fark / 3600);
        return $saat . " saat önce";
    } elseif ($fark < 604800) {
        $gun = floor($fark / 86400);
        return $gun . " gün önce";
    } else {
        return tarihFormatla($tarih);
    }
}

// 28. IP adresini al
function ipAdresiAl() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor';
    }
}

// 29. Sayfalama için limit ve offset hesapla
function sayfalama($sayfa, $limit = 10) {
    $sayfa = max(1, intval($sayfa));
    $offset = ($sayfa - 1) * $limit;
    return [
        'limit' => $limit,
        'offset' => $offset,
        'sayfa' => $sayfa
    ];
}

// 30. CSRF token oluştur ve kontrol et
function csrfTokenOlustur() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfTokenKontrol($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>