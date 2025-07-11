<?php
// Fonksiyon dosyamızı dahil et
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Güvenlik Fonksiyonu Detaylı Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .test-kutu {
            border: 2px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .tehlikeli {
            background-color: #ffe6e6;
            border-color: #ff9999;
        }
        .guvenli {
            background-color: #e6ffe6;
            border-color: #99ff99;
        }
        .bilgi {
            background-color: #e6f3ff;
            border-color: #99ccff;
        }
        .kod {
            background-color: #f4f4f4;
            padding: 10px;
            border-left: 4px solid #666;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .uyari {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .emoji {
            font-size: 24px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Güvenlik Fonksiyonu Detaylı Test</h1>
        <p><a href="index.php">← Ana Sayfaya Dön</a></p>
        
        <div class="uyari">
            <span class="emoji">⚠️</span> <strong>Neden Güvenlik Önemli?</strong><br>
            Web sitelerinde kullanıcılar form doldurup veri gönderir. 
            Kötü niyetli kullanıcılar zararlı kod göndererek:
            <ul>
                <li>Başkalarının bilgilerini çalabilir</li>
                <li>Sitenizi bozabilir</li>
                <li>Veritabanınıza zarar verebilir</li>
            </ul>
        </div>
        
        <!-- TEST 1: BOŞLUK TEMİZLEME -->
        <h2>Test 1: Boşlukları Temizleme</h2>
        <?php
        $veri1 = "   Mehmet   ";  // Başında 3, sonunda 3 boşluk var
        ?>
        
        <div class="test-kutu tehlikeli">
            <h3>❌ Temizlenmemiş Veri</h3>
            <div class="kod">
                $veri = "<?php echo $veri1; ?>"
            </div>
            <p>
                Görünen: [<?php echo $veri1; ?>]<br>
                Karakter sayısı: <?php echo strlen($veri1); ?><br>
                <small>Not: Boşluklar veritabanında sorun yaratabilir!</small>
            </p>
        </div>
        
        <div class="test-kutu guvenli">
            <h3>✅ Temizlenmiş Veri</h3>
            <?php $temiz1 = temizle($veri1); ?>
            <div class="kod">
                $temiz = temizle($veri);
            </div>
            <p>
                Görünen: [<?php echo $temiz1; ?>]<br>
                Karakter sayısı: <?php echo strlen($temiz1); ?><br>
                <small>Not: Baştaki ve sondaki boşluklar temizlendi!</small>
            </p>
        </div>
        
        <!-- TEST 2: HTML KODU -->
        <h2>Test 2: HTML Kodlarını Etkisiz Hale Getirme</h2>
        <?php
        $veri2 = "<h1>Ben kocaman bir başlık!</h1>";
        ?>
        
        <div class="test-kutu tehlikeli">
            <h3>❌ Temizlenmemiş (HTML çalışıyor)</h3>
            <div class="kod">
                echo "<?php echo htmlspecialchars($veri2); ?>";
            </div>
            <p>Tarayıcıda görünen:</p>
            <div style="border: 1px solid red; padding: 10px;">
                <?php echo $veri2; // HTML olarak çalışır! ?>
            </div>
        </div>
        
        <div class="test-kutu guvenli">
            <h3>✅ Temizlenmiş (HTML çalışmıyor)</h3>
            <?php $temiz2 = temizle($veri2); ?>
            <div class="kod">
                echo temizle($veri);
            </div>
            <p>Tarayıcıda görünen:</p>
            <div style="border: 1px solid green; padding: 10px;">
                <?php echo $temiz2; // Sadece metin olarak görünür ?>
            </div>
            <p><small>Kaynak kodda: <?php echo htmlspecialchars($temiz2); ?></small></p>
        </div>
        
        <!-- TEST 3: JAVASCRİPT SALDIRISI -->
        <h2>Test 3: JavaScript Saldırı Önleme</h2>
        <?php
        $veri3 = "<script>alert('Siteniz hacklendi!');</script>";
        ?>
        
        <div class="test-kutu tehlikeli">
            <h3>🚨 Çok Tehlikeli! JavaScript Kodu</h3>
            <div class="kod">
                Kullanıcı şunu gönderdi: <?php echo htmlspecialchars($veri3); ?>
            </div>
            <p>
                <strong>Eğer temizlemeseydik:</strong><br>
                - Bu kod çalışırdı<br>
                - Kullanıcılara pop-up gösterirdi<br>
                - Daha kötüsü: Çerez çalabilir, başka siteye yönlendirebilirdi!
            </p>
        </div>
        
        <div class="test-kutu guvenli">
            <h3>✅ Temizlenmiş (Artık zararsız)</h3>
            <?php $temiz3 = temizle($veri3); ?>
            <p>Tarayıcıda görünen:</p>
            <div style="border: 1px solid green; padding: 10px;">
                <?php echo $temiz3; // JavaScript çalışmaz, sadece metin ?>
            </div>
        </div>
        
        <!-- TEST 4: GERÇEK FORM ÖRNEĞİ -->
        <h2>Test 4: Gerçek Hayat Senaryosu</h2>
        <?php
        // Kullanıcının forma yazdığını varsayalım
        $kullanici_adi = "  <b>admin</b>  ";
        $yorum = "Harika site! <script>location.href='virus.com'</script>";
        ?>
        
        <div class="test-kutu tehlikeli">
            <h3>❌ Form Verisi - Temizlenmemiş</h3>
            <p><strong>Kullanıcı Adı:</strong> <?php echo $kullanici_adi; ?></p>
            <p><strong>Yorum:</strong> <?php echo $yorum; ?></p>
            <p style="color: red;">
                ⚠️ Dikkat: Kullanıcı adı kalın yazıldı ve yorum tehlikeli kod içeriyor!
            </p>
        </div>
        
        <div class="test-kutu guvenli">
            <h3>✅ Form Verisi - Temizlenmiş</h3>
            <p><strong>Kullanıcı Adı:</strong> <?php echo temizle($kullanici_adi); ?></p>
            <p><strong>Yorum:</strong> <?php echo temizle($yorum); ?></p>
            <p style="color: green;">
                ✓ Güvenli! HTML ve JavaScript kodları etkisiz hale geldi.
            </p>
        </div>
        
        <!-- ADIM ADIM AÇIKLAMA -->
        <h2>Fonksiyon Nasıl Çalışıyor?</h2>
        <?php
        $ornek = "  <strong>Merhaba</strong>  ";
        ?>
        
        <div class="test-kutu bilgi">
            <h3>temizle() Fonksiyonunun Adımları</h3>
            
            <p><strong>Başlangıç verisi:</strong> <code>"<?php echo htmlspecialchars($ornek); ?>"</code></p>
            
            <?php
            // Her adımı göster
            $adim1 = trim($ornek);
            $adim2 = stripslashes($adim1);
            $adim3 = htmlspecialchars($adim2);
            ?>
            
            <div class="kod">
                <strong>1. Adım - trim():</strong><br>
                Öncesi: "<?php echo htmlspecialchars($ornek); ?>"<br>
                Sonrası: "<?php echo htmlspecialchars($adim1); ?>"<br>
                <em>→ Baştaki ve sondaki boşluklar gitti</em>
            </div>
            
            <div class="kod">
                <strong>2. Adım - stripslashes():</strong><br>
                Öncesi: "<?php echo htmlspecialchars($adim1); ?>"<br>
                Sonrası: "<?php echo htmlspecialchars($adim2); ?>"<br>
                <em>→ Bu örnekte ters slash yok, değişim olmadı</em>
            </div>
            
            <div class="kod">
                <strong>3. Adım - htmlspecialchars():</strong><br>
                Öncesi: "<?php echo htmlspecialchars($adim2); ?>"<br>
                Sonrası: "<?php echo $adim3; ?>"<br>
                <em>→ HTML kodları güvenli hale geldi</em>
            </div>
            
            <p><strong>Son hali:</strong> <code>"<?php echo $adim3; ?>"</code></p>
        </div>
        
        <!-- ÖZET -->
        <div class="uyari">
            <h3>📌 Özet: Neden Her Zaman temizle() Kullanmalıyız?</h3>
            <ol>
                <li><strong>Veritabanı Güvenliği:</strong> Temiz veri = Sorunsuz kayıt</li>
                <li><strong>XSS Koruması:</strong> JavaScript saldırılarını engeller</li>
                <li><strong>HTML Injection:</strong> Sayfa düzenini bozan kodları engeller</li>
                <li><strong>Veri Tutarlılığı:</strong> Gereksiz boşluklar temizlenir</li>
            </ol>
            
            <p style="background-color: yellow; padding: 10px; margin-top: 15px;">
                <strong>🔥 ALTIN KURAL:</strong> 
                Kullanıcıdan gelen HER VERİYİ mutlaka temizle() fonksiyonundan geçir!
            </p>
        </div>
        
        <!-- EK BİLGİ -->
        <div class="test-kutu bilgi">
            <h3>💡 İpucu: Diğer Güvenlik Önlemleri</h3>
            <ul>
                <li>Şifreleri asla düz metin olarak saklama (hash kullan)</li>
                <li>SQL sorgularında prepared statements kullan</li>
                <li>Dosya yüklemelerinde uzantı kontrolü yap</li>
                <li>Oturum güvenliği için HTTPS kullan</li>
                <li>Hata mesajlarında sistem bilgisi verme</li>
            </ul>
        </div>
        
    </div>
</body>
</html>