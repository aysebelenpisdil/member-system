<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];
$mesaj = '';
$hata = '';

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $konu = temizle($_POST['subject']);
    $kategori = $_POST['category'];
    $mesaj_metni = temizle($_POST['message']);
    
    if (empty($konu) || empty($mesaj_metni)) {
        $hata = "Lütfen tüm alanları doldurun!";
    } else {
        // Burada normalde destek talebi veritabanına kaydedilir veya email gönderilir
        // Demo için sadece başarı mesajı gösteriyoruz
        
        aktiviteKaydet($db, $kullanici_id, 'support_ticket', "Destek talebi gönderildi: $konu");
        bildirimEkle($db, $kullanici_id, 'Destek Talebi Alındı', 'Destek talebiniz başarıyla alındı. En kısa sürede size dönüş yapılacaktır.', 'info');
        
        $mesaj = "Destek talebiniz başarıyla gönderildi! En kısa sürede size dönüş yapacağız.";
        
        // Formu temizle
        $konu = '';
        $mesaj_metni = '';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destek - Üye Sistemi</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .support-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .support-form-card {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .support-form-card h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 25px;
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
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
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1a1a1a;
            background-color: white;
            box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background-color: #1a1a1a;
            color: white;
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
        
        .btn-primary:hover {
            background-color: #000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary::before {
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
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .info-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .info-card {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .info-card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #1a1a1a;
            font-size: 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card .icon {
            font-size: 24px;
        }
        
        .info-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .info-card a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s ease;
        }
        
        .info-card a:hover {
            border-bottom-color: #1a1a1a;
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
        
        .faq-section {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .faq-section h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
        }
        
        .faq-item {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .faq-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .faq-question {
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 10px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-question:hover {
            color: #555;
        }
        
        .faq-answer {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            display: none;
            padding-top: 10px;
        }
        
        .faq-answer.active {
            display: block;
        }
        
        .faq-toggle {
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        
        .faq-toggle.active {
            transform: rotate(45deg);
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .support-grid {
                grid-template-columns: 1fr;
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
        <h1>💬 DESTEK</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <a href="support.php" class="active">Destek</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="support-grid">
            <div>
                <div class="support-form-card">
                    <h2>Destek Talebi Oluştur</h2>
                    
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
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <select id="category" name="category" required>
                                <option value="">Kategori Seçin</option>
                                <option value="technical">Teknik Destek</option>
                                <option value="account">Hesap Sorunları</option>
                                <option value="billing">Ödeme ve Faturalama</option>
                                <option value="feature">Özellik Önerisi</option>
                                <option value="bug">Hata Bildirimi</option>
                                <option value="other">Diğer</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Konu</label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   placeholder="Destek talebinizin konusu..." 
                                   value="<?php echo isset($konu) ? htmlspecialchars($konu) : ''; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mesajınız</label>
                            <textarea id="message" 
                                      name="message" 
                                      placeholder="Sorununuzu veya talebinizi detaylı olarak açıklayın..." 
                                      required><?php echo isset($mesaj_metni) ? htmlspecialchars($mesaj_metni) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">Destek Talebi Gönder</button>
                    </form>
                </div>
            </div>
            
            <div class="info-cards">
                <div class="info-card">
                    <h3><span class="icon">📧</span> Email Desteği</h3>
                    <p>Bize email gönderin:</p>
                    <p><a href="mailto:destek@example.com">destek@example.com</a></p>
                    <p style="margin-top: 10px; font-size: 13px;">24 saat içinde dönüş yapıyoruz</p>
                </div>
                
                <div class="info-card">
                    <h3><span class="icon">📞</span> Telefon Desteği</h3>
                    <p>Hafta içi 09:00 - 18:00</p>
                    <p><strong>0850 123 45 67</strong></p>
                </div>
                
                <div class="info-card">
                    <h3><span class="icon">⏰</span> Ortalama Yanıt Süresi</h3>
                    <p>Email: 24 saat</p>
                    <p>Telefon: Anında</p>
                    <p>Destek Talebi: 2-4 saat</p>
                </div>
                
                <div class="info-card">
                    <h3><span class="icon">📚</span> Yardım Merkezi</h3>
                    <p>Sık sorulan sorular ve kullanım kılavuzları için yardım merkezimizi ziyaret edin.</p>
                    <p style="margin-top: 10px;"><a href="#faq">SSS'leri Görüntüle →</a></p>
                </div>
            </div>
        </div>
        
        <div class="faq-section" id="faq">
            <h2>Sık Sorulan Sorular</h2>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(1)">
                    Şifremi nasıl değiştirebilirim?
                    <span class="faq-toggle" id="toggle1">+</span>
                </div>
                <div class="faq-answer" id="answer1">
                    Şifrenizi değiştirmek için:
                    <ol style="margin: 10px 0; padding-left: 20px;">
                        <li>Ana sayfada "Şifre Değiştir" butonuna tıklayın</li>
                        <li>Mevcut şifrenizi girin</li>
                        <li>Yeni şifrenizi iki kez girin</li>
                        <li>"Şifreyi Değiştir" butonuna tıklayın</li>
                    </ol>
                    Güvenlik için şifreniz en az 8 karakter olmalı ve büyük/küçük harf ile rakam içermelidir.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(2)">
                    Email adresimi değiştirebilir miyim?
                    <span class="faq-toggle" id="toggle2">+</span>
                </div>
                <div class="faq-answer" id="answer2">
                    Güvenlik nedeniyle email adresi değişikliği şu anda desteklenmemektedir. Email adresinizi değiştirmeniz gerekiyorsa, lütfen destek ekibimizle iletişime geçin.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(3)">
                    Hesabımı nasıl silebilirim?
                    <span class="faq-toggle" id="toggle3">+</span>
                </div>
                <div class="faq-answer" id="answer3">
                    Hesabınızı silmek için:
                    <ol style="margin: 10px 0; padding-left: 20px;">
                        <li>Ayarlar sayfasına gidin</li>
                        <li>"Hesap Ayarları" bölümünü seçin</li>
                        <li>Sayfanın altındaki "Hesabı Sil" butonuna tıklayın</li>
                        <li>Şifrenizi girerek işlemi onaylayın</li>
                    </ol>
                    <strong>Dikkat:</strong> Bu işlem geri alınamaz ve tüm verileriniz kalıcı olarak silinir.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(4)">
                    İki faktörlü doğrulama nasıl aktif edilir?
                    <span class="faq-toggle" id="toggle4">+</span>
                </div>
                <div class="faq-answer" id="answer4">
                    İki faktörlü doğrulama özelliği şu anda geliştirme aşamasındadır ve yakında kullanıma sunulacaktır. Bu özellik aktif olduğunda, hesabınıza giriş yaparken telefon numaranıza gönderilen kodu girmeniz gerekecektir.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(5)">
                    Profil fotoğrafımı nasıl yüklerim?
                    <span class="faq-toggle" id="toggle5">+</span>
                </div>
                <div class="faq-answer" id="answer5">
                    Profil fotoğrafı yüklemek için:
                    <ol style="margin: 10px 0; padding-left: 20px;">
                        <li>Profil sayfanıza gidin</li>
                        <li>"Fotoğraf Değiştir" butonuna tıklayın</li>
                        <li>Bilgisayarınızdan bir fotoğraf seçin (JPG, PNG, GIF - Max 5MB)</li>
                        <li>Fotoğraf otomatik olarak yüklenecektir</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleFaq(id) {
            const answer = document.getElementById('answer' + id);
            const toggle = document.getElementById('toggle' + id);
            
            answer.classList.toggle('active');
            toggle.classList.toggle('active');
        }
    </script>
</body>
</html>