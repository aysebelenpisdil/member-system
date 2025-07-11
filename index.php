<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PHP Öğreniyorum - Ana Sayfa</title>
</head>
<body>
    <h1>İlk PHP Sayfam!</h1>
    
    <?php
    // PHP kodu <?php ile başlar
    // echo komutu ile ekrana yazı yazdırırız
    echo "<p>Merhaba, ben PHP ile yazıldım!</p>";
    
    // Değişken tanımlama
    // $ işareti ile başlar
    $isim = "belen";
    $yas = 22;
    
    // Değişkenleri kullanma
    echo "<p>Benim adım " . $isim . " ve " . $yas . " yaşındayım.</p>";
    ?>
    
    <hr>
    
    <h2>PHP'de Tarih ve Saat</h2>
    <?php
    // date() fonksiyonu tarih ve saat gösterir
    echo "<p>Bugün: " . date("d.m.Y") . "</p>";
    echo "<p>Saat: " . date("H:i:s") . "</p>";
    
    // Farklı tarih formatları
    echo "<p>Uzun format: " . date("d F Y, l") . "</p>";
    ?>
    
    <hr>
    
    <h2>Test Sayfaları</h2>
    <ul>
        <li><a href="test-fonksiyon.php">Fonksiyon Testleri</a></li>
        <li><a href="test-guvenlik.php">Güvenlik Testleri</a></li>
        <li><a href="form-ornegi.php">Form Örneği</a></li>
    </ul>
    
    <hr>
    
    <h2>PHP Bilgileri</h2>
    <?php
    // PHP versiyonunu göster
    echo "<p>PHP Versiyonu: " . phpversion() . "</p>";
    
    // İşletim sistemi
    echo "<p>Sistem: " . PHP_OS . "</p>";
    
    // Sunucu bilgisi
    echo "<p>Sunucu: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
    ?>
    
</body>
</html>