<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

girisKontrol();

// Admin kontrolü
if (!adminMi()) {
    header("Location: ../dashboard.php");
    exit();
}

// Kullanıcı ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Kullanıcı bilgilerini getir
$sorgu = $db->prepare("SELECT * FROM users WHERE id = ?");
$sorgu->execute([$user_id]);
$kullanici = $sorgu->fetch();

if (!$kullanici) {
    header("Location: users.php");
    exit();
}

// Kullanıcı aktiviteleri
$aktiviteler = $db->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$aktiviteler->execute([$user_id]);

// Giriş istatistikleri
$giris_sayisi = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 1");
$giris_sayisi->execute([$kullanici['email']]);
$toplam_giris = $giris_sayisi->fetchColumn();

$basarisiz_giris = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0");
$basarisiz_giris->execute([$kullanici['email']]);
$basarisiz_giris_sayisi = $basarisiz_giris->fetchColumn();

// Son giriş denemeleri
$son_girisler = $db->prepare("SELECT * FROM login_attempts WHERE email = ? ORDER BY attempted_at DESC LIMIT 10");
$son_girisler->execute([$kullanici['email']]);

// Bildirimler
$bildirimler = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$bildirimler->execute([$user_id]);

// Profil doluluk oranı
$profil_doluluk = 20; // Temel bilgiler
if(!empty($kullanici['email_verified'])) $profil_doluluk += 20;
if(!empty($kullanici['phone'])) $profil_doluluk += 15;
if(!empty($kullanici['bio'])) $profil_doluluk += 15;
if(!empty($kullanici['profile_image'])) $profil_doluluk += 15;
if(!empty($kullanici['birth_date'])) $profil_doluluk += 15;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kullanici['name']); ?> - Kullanıcı Detayı</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libertinus+Mono&display=swap');
        
        body {
            font-family: 'Libertinus Mono', monospace;
            background-color: #f5e6d3;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .admin-header {
            background-color: #1a1a1a;
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 400;
            letter-spacing: 1px;
            display: inline-block;
        }
        
        .admin-badge {
            background-color: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 15px;
            text-transform: uppercase;
        }
        
        .admin-nav {
            background-color: #2d2d2d;
            padding: 0 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: inline-block;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }
        
        .admin-nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .logout-btn {
            float: right;
            background-color: #dc3545;
            color: white !important;
            padding: 15px 20px;
        }
        
        .logout-btn:hover {
            background-color: #c82333 !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .user-header {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e8d5c4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #1a1a1a;
            flex-shrink: 0;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-info h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
            color: #1a1a1a;
        }
        
        .user-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .meta-item {
            font-size: 14px;
            color: #666;
        }
        
        .meta-item strong {
            color: #1a1a1a;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-left: 10px;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-admin {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-user {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #1a1a1a;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .card h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 20px;
        }
        
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-size: 14px;
        }
        
        .info-value {
            color: #1a1a1a;
            font-weight: 500;
            font-size: 14px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #1a1a1a;
            transition: width 0.3s ease;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #e8d5c4;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #1a1a1a;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .user-header {
                flex-direction: column;
                text-align: center;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .user-meta {
                justify-content: center;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Kullanıcı Detayı</h1>
        <span class="admin-badge">Yönetici</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php">Genel Bakış</a>
        <a href="users.php">Kullanıcılar</a>
        <a href="activity-logs.php">Aktivite Kayıtları</a>
        <a href="settings.php">Sistem Ayarları</a>
        <a href="../dashboard.php">Ana Siteye Dön</a>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </nav>
    
    <div class="container">
        <a href="users.php" class="back-link">← Kullanıcı Listesine Dön</a>
        
        <div class="user-header">
            <div class="user-avatar">
                <?php if($kullanici['profile_image']): ?>
                    <img src="../uploads/profiles/<?php echo htmlspecialchars($kullanici['profile_image']); ?>" alt="Profil">
                <?php else: ?>
                    <?php echo strtoupper(substr($kullanici['name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            
            <div class="user-info">
                <h2>
                    <?php echo htmlspecialchars($kullanici['name']); ?>
                    <?php if($kullanici['role'] == 'admin'): ?>
                        <span class="badge badge-admin">Admin</span>
                    <?php else: ?>
                        <span class="badge badge-user">Kullanıcı</span>
                    <?php endif; ?>
                    
                    <?php
                    $durum_badge = [
                        'active' => '<span class="badge badge-success">Aktif</span>',
                        'inactive' => '<span class="badge badge-warning">Pasif</span>',
                        'banned' => '<span class="badge badge-danger">Engellenmiş</span>'
                    ];
                    echo $durum_badge[$kullanici['status']] ?? '';
                    ?>
                </h2>
                
                <div class="user-meta">
                    <div class="meta-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($kullanici['email']); ?>
                        <?php if($kullanici['email_verified']): ?>
                            <span style="color: #28a745;">✓</span>
                        <?php endif; ?>
                    </div>
                    <div class="meta-item">
                        <strong>Üye No:</strong> #<?php echo str_pad($kullanici['id'], 6, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div class="meta-item">
                        <strong>Kayıt:</strong> <?php echo tarihFormatla($kullanici['created_at']); ?>
                    </div>
                    <div class="meta-item">
                        <strong>Son Giriş:</strong> <?php echo $kullanici['last_login'] ? zamanFarki($kullanici['last_login']) : 'Hiç giriş yapmadı'; ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <?php if($kullanici['status'] == 'active'): ?>
                        <a href="users.php?action=deactivate&id=<?php echo $kullanici['id']; ?>" class="btn btn-warning">Pasif Yap</a>
                    <?php else: ?>
                        <a href="users.php?action=activate&id=<?php echo $kullanici['id']; ?>" class="btn btn-primary">Aktif Yap</a>
                    <?php endif; ?>
                    
                    <?php if($kullanici['status'] != 'banned'): ?>
                        <a href="users.php?action=ban&id=<?php echo $kullanici['id']; ?>" class="btn btn-danger">Engelle</a>
                    <?php endif; ?>
                    
                    <a href="send-notification.php?user_id=<?php echo $kullanici['id']; ?>" class="btn btn-primary">Bildirim Gönder</a>
                </div>
            </div>
        </div>
        
        <div class="content-grid">
            <!-- Profil Bilgileri -->
            <div class="card">
                <h3>Profil Bilgileri</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Telefon</span>
                        <span class="info-value"><?php echo htmlspecialchars($kullanici['phone'] ?: 'Belirtilmemiş'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Doğum Tarihi</span>
                        <span class="info-value"><?php echo $kullanici['birth_date'] ? tarihFormatla($kullanici['birth_date']) : 'Belirtilmemiş'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Adres</span>
                        <span class="info-value"><?php echo htmlspecialchars($kullanici['address'] ?: 'Belirtilmemiş'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Profil Doluluk</span>
                        <span class="info-value">
                            %<?php echo $profil_doluluk; ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $profil_doluluk; ?>%"></div>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Giriş İstatistikleri -->
            <div class="card">
                <h3>Giriş İstatistikleri</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Toplam Giriş</span>
                        <span class="info-value"><?php echo $kullanici['login_count'] ?? 0; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Başarılı Giriş Denemesi</span>
                        <span class="info-value"><?php echo $toplam_giris; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Başarısız Giriş Denemesi</span>
                        <span class="info-value" style="color: #dc3545;"><?php echo $basarisiz_giris_sayisi; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Başarı Oranı</span>
                        <span class="info-value">
                            <?php 
                            $toplam_deneme = $toplam_giris + $basarisiz_giris_sayisi;
                            echo $toplam_deneme > 0 ? round(($toplam_giris / $toplam_deneme) * 100, 1) . '%' : 'N/A';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Son Aktiviteler -->
            <div class="card full-width">
                <h3>Son Aktiviteler</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tarih/Saat</th>
                            <th>Aktivite</th>
                            <th>Açıklama</th>
                            <th>IP Adresi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($aktivite = $aktiviteler->fetch()): ?>
                        <tr>
                            <td><?php echo tarihSaatFormatla($aktivite['created_at']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $aktivite['action'])); ?></td>
                            <td><?php echo htmlspecialchars($aktivite['description'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($aktivite['ip_address']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Son Giriş Denemeleri -->
            <div class="card">
                <h3>Son Giriş Denemeleri</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tarih/Saat</th>
                            <th>Durum</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($giris = $son_girisler->fetch()): ?>
                        <tr>
                            <td><?php echo tarihSaatFormatla($giris['attempted_at']); ?></td>
                            <td>
                                <?php if($giris['success']): ?>
                                    <span style="color: #28a745;">Başarılı</span>
                                <?php else: ?>
                                    <span style="color: #dc3545;">Başarısız</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($giris['ip_address']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Son Bildirimler -->
            <div class="card">
                <h3>Son Bildirimler</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Başlık</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($bildirim = $bildirimler->fetch()): ?>
                        <tr>
                            <td><?php echo tarihSaatFormatla($bildirim['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($bildirim['title']); ?></td>
                            <td>
                                <?php if($bildirim['is_read']): ?>
                                    <span style="color: #6c757d;">Okundu</span>
                                <?php else: ?>
                                    <span style="color: #007bff;">Okunmadı</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>