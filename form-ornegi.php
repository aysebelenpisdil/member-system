<?php
// Fonksiyonları dahil et
require_once 'includes/functions.php';

// Form gönderildi mi kontrol et
// $_SERVER['REQUEST_METHOD'] = Sayfaya nasıl gelindiğini gösterir
// POST = Form gönderildi, GET = Normal sayfa açılışı
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form gönderildiyse verileri al
    
    // GÜVENSİZ YOL - YAPMA!
    // $_POST = Formdan gelen veriler
    $ad_guvensiz = $_POST['ad'];
    
    // GÜVENLİ YOL - HER ZAMAN YAP!
    $ad_guvenli = temizle($_POST['ad']);
    
    // Email için de aynısı
    $email_guvensiz = $_POST['email'];
    $email_guvenli = temizle($_POST['email']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Form Güvenlik Örneği</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-kutu {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .form-grup {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4CAF50;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .sonuc-kutu {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
        }
        .tehlikeli {
            background-color: #ffebee;
            border: 2px solid #ffcdd2;
        }
        .guvenli {
            background-color: #e8f5e9;
            border: 2px solid #c8e6c9;
        }
        .ipucu {
            background-color: #e3f2fd;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #2196F3;
            font-size: 14px;
        }
        .kod-ornegi {
            background-color: #263238;
            color: #aed581;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        .detay-kutu {
            background-color: #f5f5f5;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        h3 {
            margin-top: 0;
            color: #333;
        }
        .emoji {
            font-size: 20px;
            vertical-align: middle;
        }
        .geri-don {
            display: inline-block;
            margin-bottom: 20px;
            color: #2196F3;
            text-decoration: none;
        }
        .geri-don:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="geri-don">← Ana Sayfaya Dön</a>
        
        <h1>Form Güvenlik Örneği</h1>
        <p>Bu sayfada form verilerinin nasıl güvenli hale getirildiğini göreceğiz.</p>
        
        <div class="form-kutu">
            <h2>Bilgilerinizi Girin</h2>
            <form method="POST" action="">
                <div class="form-grup">
                    <label for="ad">Adınız:</label>
                    <input type="text" 
                           id="ad" 
                           name="ad" 
                           placeholder="Adınızı yazın..." 
                           value="<?php echo isset($ad_guvenli) ? $ad_guvenli : ''; ?>">
                </div>
                
                <div class="form-grup">
                    <label for="email">Email:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="ornek@email.com"
                           value="<?php echo isset($email_guvenli) ? $email_guvenli : ''; ?>">
                </div>
                
                <button type="submit">Gönder</button>
            </form>
            
            <div class="ipucu">
                <span class="emoji">💡</span> <strong>Deneyin:</strong><br>
                Ad alanına şunları yazıp test edin:
                <ul style="margin: 10px 0;">
                    <li>Normal isim: <code>Ahmet</code></li>
                    <li>Boşluklu: <code>&nbsp;&nbsp;&nbsp;Mehmet&nbsp;&nbsp;&nbsp;</code></li>
                    <li>HTML kodu: <code>&lt;b&gt;Ali&lt;/b&gt;</code></li>
                    <li>Script kodu: <code>&lt;script&gt;alert('hack')&lt;/script&gt;</code></li>
                    <li>Stil kodu: <code>&lt;style&gt;body{background:red}&lt;/style&gt;</code></li>
                </ul>
            </div>
        </div>
        
        <?php if (isset($ad_guvenli)): ?>
        <!-- Form gönderildiyse sonuçları göster -->
        <div class="sonuc-kutu">
            <h2>📊 Sonuçlar</h2>
            
            <!-- GÜVENSİZ SONUÇ -->
            <div class="tehlikeli">
                <h3><span class="emoji">❌</span> Güvensiz (Temizlenmemiş)</h3>
                <p><strong>Ad:</strong> <?php echo $ad_guvensiz; ?></p>
                <p><strong>Email:</strong> <?php echo $email_guvensiz; ?></p>
                
                <div class="detay-kutu">
                    <strong>Sorunlar:</strong>
                    <ul style="margin: 5px 0;">
                        <li>HTML kodları çalışabilir</li>
                        <li>JavaScript çalışabilir</li>
                        <li>Sayfa düzeni bozulabilir</li>
                        <li>Gereksiz boşluklar var</li>
                    </ul>
                </div>
                
                <div class="kod-ornegi">
                    // ❌ YANLIŞ KULLANIM<br>
                    $ad = $_POST['ad'];<br>
                    echo $ad; // Tehlikeli!
                </div>
            </div>
            
            <!-- GÜVENLİ SONUÇ -->
            <div class="guvenli">
                <h3><span class="emoji">✅</span> Güvenli (Temizlenmiş)</h3>
                <p><strong>Ad:</strong> <?php echo $ad_guvenli; ?></p>
                <p><strong>Email:</strong> <?php echo $email_guvenli; ?></p>
                
                <div class="detay-kutu">
                    <strong>Avantajlar:</strong>
                    <ul style="margin: 5px 0;">
                        <li>HTML kodları etkisiz</li>
                        <li>JavaScript çalışmaz</li>
                        <li>Sayfa düzeni korunur</li>
                        <li>Boşluklar temizlendi</li>
                    </ul>
                </div>
                
                <div class="kod-ornegi">
                    // ✅ DOĞRU KULLANIM<br>
                    $ad = temizle($_POST['ad']);<br>
                    echo $ad; // Güvenli!
                </div>
            </div>
            
            <!-- DETAYLI ANALİZ -->
            <div class="form-kutu" style="margin-top: 20px;">
                <h3>📈 Detaylı Analiz</h3>
                
                <h4>Ad Alanı:</h4>
                <div class="detay-kutu">
                    <strong>Güvensiz:</strong><br>
                    - Karakter sayısı: <?php echo strlen($ad_guvensiz); ?><br>
                    - Ham veri: <code><?php echo htmlspecialchars($ad_guvensiz); ?></code>
                </div>
                
                <div class="detay-kutu">
                    <strong>Güvenli:</strong><br>
                    - Karakter sayısı: <?php echo strlen($ad_guvenli); ?><br>
                    - Temiz veri: <code><?php echo htmlspecialchars($ad_guvenli); ?></code>
                </div>
                
                <?php if (emailGecerliMi($email_guvenli)): ?>
                    <p style="color: green; margin-top: 15px;">
                        <span class="emoji">✅</span> Email adresi geçerli formatta!
                    </p>
                <?php else: ?>
                    <p style="color: red; margin-top: 15px;">
                        <span class="emoji">❌</span> Email adresi geçersiz!
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- VERİTABANI SİMÜLASYONU -->
            <div class="form-kutu" style="margin-top: 20px;">
                <h3>💾 Veritabanı Simülasyonu</h3>
                <p>Eğer bu veriyi veritabanına kaydetseydik:</p>
                
                <div class="kod-ornegi">
                    // Güvensiz kayıt (YAPMA!)<br>
                    $sql = "INSERT INTO users (name, email) VALUES ('$ad_guvensiz', '$email_guvensiz')";<br>
                    // ⚠️ SQL Injection tehlikesi!<br><br>
                    
                    // Güvenli kayıt (DOĞRU YOL)<br>
                    $ad_temiz = temizle($_POST['ad']);<br>
                    $email_temiz = temizle($_POST['email']);<br>
                    $sql = "INSERT INTO users (name, email) VALUES (?, ?)";<br>
                    // ✅ Prepared statement ile güvenli!
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- EĞİTİCİ BİLGİLER -->
        <div class="form-kutu" style="margin-top: 30px; background-color: #fff9c4;">
            <h3>📚 Öğrendiklerimiz</h3>
            <ol>
                <li><strong>Her zaman temizle():</strong> Kullanıcıdan gelen tüm verileri temizle</li>
                <li><strong>Güvenlik önce gelir:</strong> Performanstan çok güvenlik önemli</li>
                <li><strong>Test et:</strong> Zararlı kodları deneyerek test yap</li>
                <li><strong>Prepared statements kullan:</strong> SQL sorgularında parametre kullan</li>
                <li><strong>Email kontrolü yap:</strong> Email formatını kontrol et</li>
            </ol>
            
            <div class="ipucu" style="background-color: #ffeb3b; border-color: #f57f17;">
                <strong>🔐 Güvenlik Prensipleri:</strong><br>
                1. Kullanıcıya asla güvenme<br>
                2. Her veriyi kontrol et<br>
                3. En az yetki prensibi<br>
                4. Hataları logla, kullanıcıya gösterme<br>
                5. Güncel tut (PHP, kütüphaneler)
            </div>
        </div>
        
    </div>
</body>
</html>