<?php
// Test sayfası başlığı
echo "<h1>Veritabanı Bağlantı Testi</h1>";

// Config dosyasını dahil et
echo "<p>1. Config dosyası dahil ediliyor...</p>";
require_once 'includes/config.php';

// Buraya geldiysek bağlantı başarılı!
echo "<p style='color: green;'>✅ 2. Bağlantı başarılı!</p>";

// Stil ekleyelim
echo "<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px;
        line-height: 1.6;
    }
    .info-box {
        background-color: #f0f0f0;
        padding: 15px;
        margin: 15px 0;
        border-radius: 5px;
        border-left: 4px solid #4CAF50;
    }
    .error-box {
        background-color: #ffebee;
        padding: 15px;
        margin: 15px 0;
        border-radius: 5px;
        border-left: 4px solid #f44336;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin: 15px 0;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #4CAF50;
        color: white;
    }
</style>";

// Bağlantı bilgilerini göster
echo "<div class='info-box'>";
echo "<h2>📊 Bağlantı Detayları:</h2>";
echo "<ul>";
echo "<li><strong>Sunucu:</strong> " . $host . "</li>";
echo "<li><strong>Veritabanı:</strong> " . $database . "</li>";
echo "<li><strong>Kullanıcı:</strong> " . $username . "</li>";
echo "<li><strong>Karakter Seti:</strong> UTF8MB4 (Türkçe destekli)</li>";
echo "</ul>";
echo "</div>";

// PHP ve MySQL versiyonları
echo "<div class='info-box'>";
echo "<h2>🖥️ Sistem Bilgileri:</h2>";
echo "<ul>";
echo "<li><strong>PHP Versiyonu:</strong> " . phpversion() . "</li>";

// MySQL versiyonunu öğren
try {
    $versiyon = $db->query('SELECT VERSION()')->fetchColumn();
    echo "<li><strong>MySQL Versiyonu:</strong> " . $versiyon . "</li>";
} catch(PDOException $e) {
    echo "<li><strong>MySQL Versiyonu:</strong> <span style='color: red;'>Alınamadı</span></li>";
}

echo "<li><strong>Sunucu:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li><strong>İşletim Sistemi:</strong> " . PHP_OS . "</li>";
echo "</ul>";
echo "</div>";

// Veritabanlarını listele
echo "<div class='info-box'>";
echo "<h2>🗄️ Mevcut Veritabanları:</h2>";

try {
    $veritabanlari = $db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach($veritabanlari as $vt) {
        if($vt == $database) {
            echo "<li><strong style='color: green;'>✅ " . $vt . " (Bizim veritabanımız)</strong></li>";
        } else {
            echo "<li>" . $vt . "</li>";
        }
    }
    echo "</ul>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>Veritabanları listelenemedi: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Users tablosu kontrolü
echo "<div class='info-box'>";
echo "<h2>📋 Tablo Kontrolü:</h2>";

try {
    // users tablosunu sorgula
    $kontrol = $db->query("SHOW TABLES LIKE 'users'");
    
    if ($kontrol->rowCount() > 0) {
        echo "<p style='color: green;'>✅ <strong>'users' tablosu bulundu!</strong></p>";
        
        // Tablo yapısını göster
        echo "<h3>Tablo Yapısı:</h3>";
        $kolonlar = $db->query("DESCRIBE users");
        
        echo "<table>";
        echo "<tr>";
        echo "<th>Sütun Adı</th>";
        echo "<th>Veri Tipi</th>";
        echo "<th>Null?</th>";
        echo "<th>Anahtar</th>";
        echo "<th>Varsayılan</th>";
        echo "<th>Ekstra</th>";
        echo "</tr>";
        
        foreach($kolonlar as $kolon) {
            echo "<tr>";
            echo "<td><strong>" . $kolon['Field'] . "</strong></td>";
            echo "<td>" . $kolon['Type'] . "</td>";
            echo "<td>" . $kolon['Null'] . "</td>";
            echo "<td>" . ($kolon['Key'] ? $kolon['Key'] : '-') . "</td>";
            echo "<td>" . ($kolon['Default'] ? $kolon['Default'] : '-') . "</td>";
            echo "<td>" . ($kolon['Extra'] ? $kolon['Extra'] : '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Kayıt sayısı
        $kayitSayisi = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "<p><strong>Toplam Kayıt Sayısı:</strong> " . $kayitSayisi . "</p>";
        
    } else {
        echo "<div class='error-box'>";
        echo "<p>❌ <strong>'users' tablosu bulunamadı!</strong></p>";
        echo "<p>Tabloyu oluşturmak için phpMyAdmin'de şu SQL kodunu çalıştırın:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
        echo "CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        echo "</pre>";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error-box'>";
    echo "<p>❌ Tablo kontrolünde hata: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";

// Test işlemleri
echo "<div class='info-box'>";
echo "<h2>🧪 Test İşlemleri:</h2>";

// Basit bir sorgu testi
try {
    $test = $db->query("SELECT 1+1 AS sonuc")->fetch();
    echo "<p>✅ Matematik testi: 1+1 = " . $test['sonuc'] . "</p>";
    
    // Tarih testi
    $tarih = $db->query("SELECT NOW() AS simdiki_zaman")->fetch();
    echo "<p>✅ Sunucu saati: " . $tarih['simdiki_zaman'] . "</p>";
    
    echo "<p style='color: green;'><strong>🎉 Tebrikler! Veritabanı bağlantınız sorunsuz çalışıyor!</strong></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Test başarısız: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Sonraki adımlar
echo "<div class='info-box' style='background-color: #e3f2fd; border-left-color: #2196F3;'>";
echo "<h2>📚 Sonraki Adımlar:</h2>";
echo "<ol>";
echo "<li>✓ Veritabanı bağlantısı çalışıyor</li>";
if ($kontrol && $kontrol->rowCount() > 0) {
    echo "<li>✓ Users tablosu hazır</li>";
    echo "<li>→ Artık kullanıcı kayıt sistemini yapabiliriz!</li>";
} else {
    echo "<li>→ Users tablosunu oluşturun</li>";
    echo "<li>→ Sonra kullanıcı kayıt sistemine geçebiliriz</li>";
}
echo "</ol>";
echo "</div>";

// Geri dön linki
echo "<p style='margin-top: 30px;'>";
echo "<a href='index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Ana Sayfaya Dön</a>";
echo "</p>";

// Hata ayıklama bilgisi (production'da kaldır)
if (isset($e)) {
    echo "<div class='error-box' style='margin-top: 30px;'>";
    echo "<h3>🐛 Hata Ayıklama Bilgisi:</h3>";
    echo "<pre>" . print_r($e, true) . "</pre>";
    echo "</div>";
}
?>