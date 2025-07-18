<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

girisKontrol();

// Admin kontrolü
if (!adminMi()) {
    header("Location: ../dashboard.php");
    exit();
}

// İstatistikler
$toplam_kullanici = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$aktif_kullanici = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$bugunki_giris = $db->query("SELECT COUNT(*) FROM login_attempts WHERE success = 1 AND DATE(attempted_at) = CURDATE()")->fetchColumn();
$son_24_saat_aktivite = $db->query("SELECT COUNT(*) FROM activity_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();

// Son kayıtlar
$son_kayitlar = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Son aktiviteler
$son_aktiviteler = $db->query("
    SELECT a.*, u.name as user_name 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Üye Sistemi</title>
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
        
        .admin-nav a.active {
            background-color: #1a1a1a;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .stat-icon.blue {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }
        
        .stat-icon.green {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .stat-icon.orange {
            background-color: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }
        
        .stat-icon.purple {
            background-color: rgba(156, 39, 176, 0.1);
            color: #9C27B0;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
        }
        
        .card h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
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
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
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
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .action-link {
            color: #2196F3;
            text-decoration: none;
            font-size: 13px;
            margin-right: 10px;
        }
        
        .action-link:hover {
            text-decoration: underline;
        }
        
        .quick-actions {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 30px;
        }
        
        .quick-actions h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
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
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-nav {
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Admin Paneli</h1>
        <span class="admin-badge">Yönetici</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php" class="active">Genel Bakış</a>
        <a href="users.php">Kullanıcılar</a>
        <a href="activity-logs.php">Aktivite Kayıtları</a>
        <a href="settings.php">Sistem Ayarları</a>
        <a href="../dashboard.php">Ana Siteye Dön</a>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </nav>
    
    <div class="container">
        <div class="quick-actions">
            <h2>Hızlı İşlemler</h2>
            <div class="action-buttons">
                <a href="users.php?action=add" class="btn btn-primary">Yeni Kullanıcı Ekle</a>
                <a href="send-notification.php" class="btn btn-primary">Toplu Bildirim Gönder</a>
                <a href="backup.php" class="btn btn-primary">Yedekleme Al</a>
                <a href="reports.php" class="btn btn-primary">Rapor Oluştur</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-value"><?php echo $toplam_kullanici; ?></div>
                <div class="stat-label">Toplam Kullanıcı</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-value"><?php echo $aktif_kullanici; ?></div>
                <div class="stat-label">Aktif Kullanıcı</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">🔑</div>
                <div class="stat-value"><?php echo $bugunki_giris; ?></div>
                <div class="stat-label">Bugünkü Giriş</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">📊</div>
                <div class="stat-value"><?php echo $son_24_saat_aktivite; ?></div>
                <div class="stat-label">24 Saatlik Aktivite</div>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="card">
                <h2>Son Kayıtlar</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>Email</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($son_kayitlar as $kayit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($kayit['name']); ?></td>
                            <td><?php echo htmlspecialchars($kayit['email']); ?></td>
                            <td><?php echo tarihFormatla($kayit['created_at']); ?></td>
                            <td>
                                <a href="user-detail.php?id=<?php echo $kayit['id']; ?>" class="action-link">Detay</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Son Aktiviteler</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Aktivite</th>
                            <th>Zaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($son_aktiviteler as $aktivite): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($aktivite['user_name']); ?></td>
                            <td>
                                <?php
                                $aktivite_tipi = [
                                    'login' => '<span class="badge badge-success">Giriş</span>',
                                    'logout' => '<span class="badge badge-danger">Çıkış</span>',
                                    'profile_update' => '<span class="badge badge-info">Profil Güncelleme</span>',
                                    'password_change' => '<span class="badge badge-warning">Şifre Değişimi</span>',
                                ];
                                echo $aktivite_tipi[$aktivite['action']] ?? '<span class="badge">' . $aktivite['action'] . '</span>';
                                ?>
                            </td>
                            <td><?php echo zamanFarki($aktivite['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>