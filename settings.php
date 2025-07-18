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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch($action) {
        case 'delete_account':
            // Hesap silme
            $password = $_POST['password'];
            
            if (password_verify($password, $kullanici['password'])) {
                try {
                    // Önce ilişkili kayıtları sil
                    $db->prepare("DELETE FROM activity_logs WHERE user_id = ?")->execute([$kullanici_id]);
                    $db->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$kullanici_id]);
                    $db->prepare("DELETE FROM user_sessions WHERE user_id = ?")->execute([$kullanici_id]);
                    
                    // Kullanıcıyı sil
                    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$kullanici_id]);
                    
                    // Oturumu sonlandır
                    session_destroy();
                    header("Location: login.php");
                    exit();
                } catch(PDOException $e) {
                    $hata = "Hesap silinirken bir hata oluştu!";
                }
            } else {
                $hata = "Şifre yanlış!";
            }
            break;
            
        case 'deactivate_account':
            // Hesap dondurma
            try {
                $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$kullanici_id]);
                aktiviteKaydet($db, $kullanici_id, 'account_deactivated', 'Hesap geçici olarak donduruldu');
                session_destroy();
                header("Location: login.php");
                exit();
            } catch(PDOException $e) {
                $hata = "İşlem sırasında bir hata oluştu!";
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Üye Sistemi</title>
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
        
        .settings-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        
        .settings-menu {
            background-color: white;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            height: fit-content;
            overflow: hidden;
        }
        
        .menu-item {
            display: block;
            padding: 20px 25px;
            color: #1a1a1a;
            text-decoration: none;
            border-bottom: 1px solid #e8d5c4;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .menu-item:last-child {
            border-bottom: none;
        }
        
        .menu-item:hover {
            background-color: #f5e6d3;
            padding-left: 30px;
        }
        
        .menu-item.active {
            background-color: #1a1a1a;
            color: white;
        }
        
        .settings-content {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .settings-section {
            display: none;
        }
        
        .settings-section.active {
            display: block;
        }
        
        .settings-section h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
        }
        
        .settings-item {
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .settings-item:last-child {
            border-bottom: none;
        }
        
        .settings-item h3 {
            margin: 0 0 10px 0;
            color: #1a1a1a;
            font-size: 16px;
            font-weight: 500;
        }
        
        .settings-item p {
            margin: 0 0 15px 0;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Libertinus Mono', monospace;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #1a1a1a;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #000;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #1a1a1a;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-1px);
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #1a1a1a;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
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
        
        .danger-zone {
            background-color: #fff5f5;
            padding: 30px;
            border-radius: 8px;
            border: 2px solid #ffcccc;
            margin-top: 30px;
        }
        
        .danger-zone h3 {
            color: #dc3545;
            margin-top: 0;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 40px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            animation: slideDown 0.3s ease;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #1a1a1a;
            font-size: 20px;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
        }
        
        .close:hover {
            color: #1a1a1a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d2d2d;
            font-weight: 400;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Libertinus Mono', monospace;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-menu {
                display: flex;
                overflow-x: auto;
                border-radius: 8px;
                white-space: nowrap;
            }
            
            .menu-item {
                border-bottom: none;
                border-right: 1px solid #e8d5c4;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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
        <h1>⚙️ AYARLAR</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <a href="notifications.php">Bildirimler</a>
            <a href="settings.php" class="active">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <?php if($hata): ?>
            <div class="alert alert-error">
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>
        
        <div class="settings-grid">
            <div class="settings-menu">
                <a class="menu-item active" onclick="showSection('account')">Hesap Ayarları</a>
                <a class="menu-item" onclick="showSection('security')">Güvenlik</a>
                <a class="menu-item" onclick="showSection('privacy')">Gizlilik</a>
                <a class="menu-item" onclick="showSection('notifications')">Bildirimler</a>
                <a class="menu-item" onclick="showSection('appearance')">Görünüm</a>
            </div>
            
            <div class="settings-content">
                <!-- Hesap Ayarları -->
                <div class="settings-section active" id="account">
                    <h2>Hesap Ayarları</h2>
                    
                    <div class="settings-item">
                        <h3>Email Adresi</h3>
                        <p><?php echo htmlspecialchars($kullanici['email']); ?></p>
                        <button class="btn btn-primary" disabled>Email değiştirilemez</button>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Hesap Türü</h3>
                        <p>Hesap türünüz: <strong><?php echo ucfirst($kullanici['role'] ?? 'user'); ?></strong></p>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Hesap Durumu</h3>
                        <p>Hesabınız şu anda <strong><?php echo $kullanici['status'] === 'active' ? 'Aktif' : 'Pasif'; ?></strong></p>
                    </div>
                    
                    <div class="danger-zone">
                        <h3>Tehlikeli Bölge</h3>
                        <div class="settings-item">
                            <h3>Hesabı Dondur</h3>
                            <p>Hesabınızı geçici olarak dondurabilirsiniz. Tekrar giriş yaptığınızda hesabınız aktif olacaktır.</p>
                            <button class="btn btn-warning" onclick="showModal('deactivate')">Hesabı Dondur</button>
                        </div>
                        
                        <div class="settings-item">
                            <h3>Hesabı Sil</h3>
                            <p>Hesabınızı kalıcı olarak silmek istiyorsanız, bu işlem geri alınamaz.</p>
                            <button class="btn btn-danger" onclick="showModal('delete')">Hesabı Sil</button>
                        </div>
                    </div>
                </div>
                
                <!-- Güvenlik -->
                <div class="settings-section" id="security">
                    <h2>Güvenlik Ayarları</h2>
                    
                    <div class="settings-item">
                        <h3>Şifre</h3>
                        <p>Şifrenizi düzenli olarak değiştirmenizi öneririz.</p>
                        <a href="change-password.php" class="btn btn-primary">Şifre Değiştir</a>
                    </div>
                    
                    <div class="settings-item">
                        <h3>İki Faktörlü Doğrulama</h3>
                        <p>Hesabınızın güvenliğini artırmak için iki faktörlü doğrulama kullanın.</p>
                        <label class="toggle-switch">
                            <input type="checkbox" disabled>
                            <span class="slider"></span>
                        </label>
                        <p style="color: #999; font-size: 12px; margin-top: 10px;">Bu özellik yakında aktif olacak</p>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Oturum Yönetimi</h3>
                        <p>Aktif oturumlarınızı görüntüleyin ve yönetin.</p>
                        <button class="btn btn-primary" disabled>Oturumları Görüntüle</button>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Giriş Geçmişi</h3>
                        <p>Son giriş aktivitelerinizi görüntüleyin.</p>
                        <a href="activity.php" class="btn btn-primary">Aktiviteleri Görüntüle</a>
                    </div>
                </div>
                
                <!-- Gizlilik -->
                <div class="settings-section" id="privacy">
                    <h2>Gizlilik Ayarları</h2>
                    
                    <div class="settings-item">
                        <h3>Profil Görünürlüğü</h3>
                        <p>Profilinizin kimler tarafından görülebileceğini ayarlayın.</p>
                        <select style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                            <option>Herkese Açık</option>
                            <option>Sadece Üyeler</option>
                            <option>Gizli</option>
                        </select>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Aktivite Durumu</h3>
                        <p>Son görülme zamanınızın gösterilmesini kontrol edin.</p>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Veri İndirme</h3>
                        <p>Tüm verilerinizi indirin.</p>
                        <button class="btn btn-primary" disabled>Veri İndir</button>
                    </div>
                </div>
                
                <!-- Bildirimler -->
                <div class="settings-section" id="notifications">
                    <h2>Bildirim Ayarları</h2>
                    
                    <div class="settings-item">
                        <h3>Email Bildirimleri</h3>
                        <p>Önemli güncellemeler için email bildirimleri alın.</p>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Güvenlik Uyarıları</h3>
                        <p>Şüpheli aktiviteler için anında bildirim alın.</p>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Pazarlama Bildirimleri</h3>
                        <p>Yeni özellikler ve güncellemeler hakkında bilgi alın.</p>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <!-- Görünüm -->
                <div class="settings-section" id="appearance">
                    <h2>Görünüm Ayarları</h2>
                    
                    <div class="settings-item">
                        <h3>Tema</h3>
                        <p>Arayüz temasını seçin.</p>
                        <select style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                            <option>Açık Tema</option>
                            <option disabled>Koyu Tema (Yakında)</option>
                        </select>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Dil</h3>
                        <p>Arayüz dilini seçin.</p>
                        <select style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                            <option>Türkçe</option>
                            <option disabled>English (Soon)</option>
                        </select>
                    </div>
                    
                    <div class="settings-item">
                        <h3>Saat Dilimi</h3>
                        <p>Yerel saat diliminizi ayarlayın.</p>
                        <select style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                            <option>İstanbul (UTC+3)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hesap Dondurma Modal -->
    <div id="deactivateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('deactivate')">&times;</span>
                <h3>Hesabı Dondur</h3>
            </div>
            <p>Hesabınızı dondurmak istediğinizden emin misiniz? Tekrar giriş yaparak hesabınızı aktif edebilirsiniz.</p>
            <form method="POST">
                <input type="hidden" name="action" value="deactivate_account">
                <button type="submit" class="btn btn-warning" style="width: 100%;">Evet, Hesabı Dondur</button>
            </form>
        </div>
    </div>
    
    <!-- Hesap Silme Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('delete')">&times;</span>
                <h3>Hesabı Kalıcı Olarak Sil</h3>
            </div>
            <p style="color: #dc3545; font-weight: bold;">DİKKAT: Bu işlem geri alınamaz!</p>
            <p>Hesabınızı silmek için şifrenizi girin:</p>
            <form method="POST">
                <input type="hidden" name="action" value="delete_account">
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-danger" style="width: 100%;">Hesabı Kalıcı Olarak Sil</button>
            </form>
        </div>
    </div>
    
    <script>
        function showSection(sectionId) {
            // Tüm sectionları gizle
            document.querySelectorAll('.settings-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Tüm menü itemlarından active class'ı kaldır
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // İlgili section'ı göster
            document.getElementById(sectionId).classList.add('active');
            
            // İlgili menü item'ına active class ekle
            event.target.classList.add('active');
        }
        
        function showModal(type) {
            document.getElementById(type + 'Modal').style.display = 'block';
        }
        
        function closeModal(type) {
            document.getElementById(type + 'Modal').style.display = 'none';
        }
        
        // Modal dışına tıklanınca kapat
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>