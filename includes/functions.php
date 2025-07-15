<?php
// Bu dosya tüm yardımcı fonksiyonlarımızı içerir
// Diğer dosyalardan require_once ile çağırılacak

// 1. Basit selamlama fonksiyonu
function merhaba() {
    // echo = ekrana yazı yazdırır
    echo "Merhaba Dünya!";
}

// 2. İsim alıp selamlayan fonksiyon
function selamla($isim) {
    // $isim = parametre (dışarıdan gelen değer)
    // . (nokta) = metinleri birleştirir
    echo "Merhaba, " . $isim . "!";
}

// 3. İki sayıyı toplayıp sonucu döndüren fonksiyon
function topla($sayi1, $sayi2) {
    // return = değeri geri döndürür
    // echo gibi ekrana yazmaz, değeri gönderir
    return $sayi1 + $sayi2;
}

// 4. Kullanıcıdan gelen veriyi güvenli hale getiren fonksiyon
function temizle($veri) {
    // trim() = başındaki ve sonundaki boşlukları siler
    // Örnek: "  ali  " -> "ali"
    $veri = trim($veri);
    
    // stripslashes() = ters slash işaretlerini kaldırır
    // Örnek: "ali\'nin" -> "ali'nin"
    $veri = stripslashes($veri);
    
    // htmlspecialchars() = HTML kodlarını etkisiz hale getirir
    // Örnek: "<script>" -> "&lt;script&gt;"
    // Bu sayede zararlı kodlar çalışmaz
    $veri = htmlspecialchars($veri);
    
    // Temizlenmiş veriyi geri döndür
    return $veri;
}

// 5. Email adresinin geçerli olup olmadığını kontrol eden fonksiyon
function emailGecerliMi($email) {
    // filter_var() = PHP'nin hazır filtre fonksiyonu
    // FILTER_VALIDATE_EMAIL = email formatı kontrolü yapar
    // @ işareti var mı? .com gibi uzantı var mı? kontrol eder
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;  // Email formatı doğru
    } else {
        return false; // Email formatı yanlış
    }
}

// 6. Daha kısa email kontrol fonksiyonu (aynı işi yapar)
function emailKontrol($email) {
    // Direkt sonucu döndürür (true veya false)
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 7. Değişken içeriğini detaylı gösteren hata ayıklama fonksiyonu
function debug($degisken) {
    // <pre> etiketi = formatı korur, düzenli gösterir
    echo "<pre>";
    
    // print_r() = değişkenin tüm içeriğini gösterir
    // Diziler ve objeler için çok kullanışlı
    print_r($degisken);
    
    echo "</pre>";
}

// 8. Şifre yeterince güçlü mü kontrol eden fonksiyon
function sifreGucluMu($sifre) {
    // strlen() = metnin uzunluğunu (karakter sayısını) verir
    if (strlen($sifre) < 6) {
        return false; // 6 karakterden kısa, güçlü değil
    }
    
    // İleride ekstra kontroller ekleyebiliriz:
    // - Büyük harf var mı?
    // - Rakam var mı?
    // - Özel karakter var mı?
    
    return true; // Şifre yeterince güçlü
}

// 9. Rastgele güvenlik kodu üreten fonksiyon
function guvenlikKoduUret() {
    // rand() = rastgele sayı üretir
    // 100000 ile 999999 arası 6 haneli kod
    return rand(100000, 999999);
}

// 10. Tarih formatını Türkçe yapan fonksiyon
function tarihFormatla($tarih) {
    // strtotime() = metni zaman damgasına çevirir
    // date() = zaman damgasını istediğimiz formata çevirir
    // d.m.Y = gün.ay.yıl (01.12.2024 gibi)
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

?>