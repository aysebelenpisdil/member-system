<?php
// Fonksiyon dosyamızı dahil edelim
// require_once = dosyayı bir kere dahil et, yoksa hata ver
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Fonksiyon Testleri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .test-kutu {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .sonuc {
            background-color: #e7f3ff;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
        }
        code {
            background-color: #f1f1f1;
            padding: 2px 4px;
            color: #d14;
        }
    </style>
</head>
<body>
    <h1>PHP Fonksiyonlarını Test Ediyoruz</h1>
    <p><a href="index.php">← Ana Sayfaya Dön</a></p>
    
    <div class="test-kutu">
        <h2>Test 1: Basit Fonksiyon</h2>
        <p>Kod: <code>merhaba();</code></p>
        <div class="sonuc">
            <?php
            // Fonksiyonu çağır
            merhaba();
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 2: Parametreli Fonksiyon</h2>
        <p>Kod: <code>selamla("İsim");</code></p>
        <div class="sonuc">
            <?php
            // Farklı isimlerle dene
            selamla("Ayşe");
            echo "<br>";
            selamla("Mehmet");
            echo "<br>";
            selamla("Fatma");
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 3: Değer Döndüren Fonksiyon</h2>
        <p>Kod: <code>$sonuc = topla(5, 3);</code></p>
        <div class="sonuc">
            <?php
            // Fonksiyondan dönen değeri al
            $sonuc1 = topla(5, 3);
            echo "5 + 3 = " . $sonuc1 . "<br>";
            
            $sonuc2 = topla(10, 15);
            echo "10 + 15 = " . $sonuc2 . "<br>";
            
            // Direkt kullanım
            echo "100 + 250 = " . topla(100, 250);
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 4: Güvenlik - Veri Temizleme</h2>
        <p>Kod: <code>temizle($veri);</code></p>
        <div class="sonuc">
            <?php
            // Test verileri
            $test1 = "  Boşluklu metin  ";
            $test2 = "<script>alert('Hack!')</script>";
            $test3 = "<h1>Başlık</h1>";
            
            echo "Test 1 - Boşluklar:<br>";
            echo "Orijinal: '[" . $test1 . "]'<br>";
            echo "Temiz: '[" . temizle($test1) . "]'<br><br>";
            
            echo "Test 2 - Script kodu:<br>";
            echo "Orijinal: " . htmlspecialchars($test2) . "<br>";
            echo "Temiz: " . temizle($test2) . "<br><br>";
            
            echo "Test 3 - HTML kodu:<br>";
            echo "Orijinal: " . htmlspecialchars($test3) . "<br>";
            echo "Temiz: " . temizle($test3);
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 5: Email Kontrolü</h2>
        <p>Kod: <code>emailGecerliMi($email);</code></p>
        <div class="sonuc">
            <?php
            // Test emailleri
            $emails = array(
                "test@example.com",
                "yanlis.email",
                "ali@",
                "@gmail.com",
                "mehmet@firma.com.tr"
            );
            
            foreach ($emails as $email) {
                if (emailGecerliMi($email)) {
                    echo $email . " → ✅ Geçerli<br>";
                } else {
                    echo $email . " → ❌ Geçersiz<br>";
                }
            }
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 6: Debug (Hata Ayıklama)</h2>
        <p>Kod: <code>debug($degisken);</code></p>
        <div class="sonuc">
            <?php
            // Test için bir dizi oluştur
            $kullanici = array(
                "isim" => "Ali Veli",
                "email" => "ali@test.com",
                "yas" => 30,
                "sehir" => "İstanbul"
            );
            
            echo "Normal echo ile dizi gösterimi: ";
            echo $kullanici; // Sadece "Array" yazar
            
            echo "<br><br>Debug fonksiyonu ile:";
            debug($kullanici); // Tüm içeriği gösterir
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 7: Şifre Kontrolü</h2>
        <p>Kod: <code>sifreGucluMu($sifre);</code></p>
        <div class="sonuc">
            <?php
            // Test şifreleri
            $sifreler = array(
                "123",
                "12345",
                "123456",
                "güçlüşifre123"
            );
            
            foreach ($sifreler as $sifre) {
                if (sifreGucluMu($sifre)) {
                    echo $sifre . " → ✅ Güçlü (Kabul edildi)<br>";
                } else {
                    echo $sifre . " → ❌ Zayıf (En az 6 karakter olmalı)<br>";
                }
            }
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 8: Güvenlik Kodu</h2>
        <p>Kod: <code>guvenlikKoduUret();</code></p>
        <div class="sonuc">
            <?php
            echo "Üretilen güvenlik kodları:<br>";
            for ($i = 1; $i <= 5; $i++) {
                echo $i . ". kod: " . guvenlikKoduUret() . "<br>";
            }
            ?>
        </div>
    </div>
    
    <div class="test-kutu">
        <h2>Test 9: Tarih Formatlama</h2>
        <p>Kod: <code>tarihFormatla($tarih);</code></p>
        <div class="sonuc">
            <?php
            $tarihler = array(
                "2024-12-15",
                "2024-01-01",
                "15 December 2024",
                "next Monday"
            );
            
            foreach ($tarihler as $tarih) {
                echo $tarih . " → " . tarihFormatla($tarih) . "<br>";
            }
            ?>
        </div>
    </div>
    
</body>
</html>