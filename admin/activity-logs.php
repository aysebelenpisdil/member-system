<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

girisKontrol();

// Admin kontrolü
if (!adminMi()) {
    header("Location: ../dashboard.php");
    exit();
}

// Filtreleme parametreleri
$filter_user = isset($_GET['user']) ? intval($_GET['user']) : 0;
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Sayfalama
$sayfa = isset($_GET['sayfa']) ? intval($_GET['sayfa']) : 1;
$limit = 50;
$offset = ($sayfa - 1) * $limit;

// Sorgu oluştur
$where_conditions = [];
$params = [];

if ($filter_user) {
    $where_conditions[] = "a.user_id = ?";
    $params[] = $filter_user;
}

if ($filter_action) {
    $where_conditions[] = "a.action = ?";
    $params[] = $filter_action;
}

if ($filter_date) {
    $where_conditions[] = "DATE(a.created_at) = ?";
    $params[] = $filter_date;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Toplam kayıt sayısı
$count_query = "SELECT COUNT(*) FROM activity_logs a $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$toplam_kayit = $stmt->fetchColumn();
$toplam_sayfa = ceil($toplam_kayit / $limit);

// Aktiviteleri getir
$query = "
    SELECT a.*, u.name as user_name, u.email as user_email 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    $where_clause 
    ORDER BY a.created_at DESC 
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$stmt = $db->prepare($query);
$stmt->execute($params);
$aktiviteler = $stmt->fetchAll();

// Benzersiz aksiyonları al
$aksiyon_listesi = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

// Kullanıcı listesi (dropdown için)
$kullanicilar = $db->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Kayıtları - Admin Paneli</title>
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .filter-bar {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
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
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Libertinus Mono', monospace;
            background-color: #fafafa;
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
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .logs-table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #e8d5c4;
            white-space: nowrap;
        }
        
        .table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .user-link {
            color: #2196F3;
            text-decoration: none;
            font-weight: 500;
        }
        
        .user-link:hover {
            text-decoration: underline;
        }
        
        .action-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .action-login {
            background-color: #d4edda;
            color: #155724;
        }
        
        .action-logout {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-update {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .action-password {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .action-default {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .ip-address {
            font-family: monospace;
            font-size: 13px;
            color: #666;
        }
        
        .device-info {
            font-size: 12px;
            color: #999;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 15px;
            background-color: white;
            border: 1px solid #e8d5c4;
            border-radius: 4px;
            text-decoration: none;
            color: #1a1a1a;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: #1a1a1a;
            color: white;
        }
        
        .pagination .current {
            background-color: #1a1a1a;
            color: white;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            text-align: center;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .export-btn {
            float: right;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .table {
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Aktivite Kayıtları</h1>
        <span class="admin-badge">Yönetici</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php">Genel Bakış</a>
        <a href="users.php">Kullanıcılar</a>
        <a href="activity-logs.php" class="active">Aktivite Kayıtları</a>
        <a href="settings.php">Sistem Ayarları</a>
        <a href="../dashboard.php">Ana Siteye Dön</a>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </nav>
    
    <div class="container">
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($toplam_kayit); ?></div>
                <div class="stat-label">Toplam Aktivite</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $db->query("SELECT COUNT(DISTINCT user_id) FROM activity_logs")->fetchColumn(); ?></div>
                <div class="stat-label">Aktif Kullanıcı</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $db->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn(); ?></div>
                <div class="stat-label">Bugünkü Aktivite</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $db->query("SELECT COUNT(*) FROM activity_logs WHERE action = 'login' AND DATE(created_at) = CURDATE()")->fetchColumn(); ?></div>
                <div class="stat-label">Bugünkü Giriş</div>
            </div>
        </div>
        
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="user">Kullanıcı</label>
                    <select id="user" name="user">
                        <option value="">Tüm Kullanıcılar</option>
                        <?php foreach($kullanicilar as $kullanici): ?>
                            <option value="<?php echo $kullanici['id']; ?>" <?php echo $filter_user == $kullanici['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kullanici['name']); ?> (<?php echo htmlspecialchars($kullanici['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="action">Aktivite Türü</label>
                    <select id="action" name="action">
                        <option value="">Tüm Aktiviteler</option>
                        <?php foreach($aksiyon_listesi as $aksiyon): ?>
                            <option value="<?php echo $aksiyon; ?>" <?php echo $filter_action == $aksiyon ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $aksiyon)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">Tarih</label>
                    <input type="date" id="date" name="date" value="<?php echo $filter_date; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Filtrele</button>
                <a href="activity-logs.php" class="btn btn-secondary">Temizle</a>
            </form>
        </div>
        
        <button class="btn btn-primary export-btn" onclick="alert('Export özelliği yakında eklenecek!')">Excel'e Aktar</button>
        
        <div class="logs-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tarih/Saat</th>
                        <th>Kullanıcı</th>
                        <th>Aktivite</th>
                        <th>Açıklama</th>
                        <th>IP Adresi</th>
                        <th>Cihaz Bilgisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($aktiviteler as $aktivite): ?>
                    <tr>
                        <td>#<?php echo $aktivite['id']; ?></td>
                        <td><?php echo tarihSaatFormatla($aktivite['created_at']); ?></td>
                        <td>
                            <a href="user-detail.php?id=<?php echo $aktivite['user_id']; ?>" class="user-link">
                                <?php echo htmlspecialchars($aktivite['user_name']); ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $action_class = 'action-default';
                            if (strpos($aktivite['action'], 'login') !== false) $action_class = 'action-login';
                            elseif (strpos($aktivite['action'], 'logout') !== false) $action_class = 'action-logout';
                            elseif (strpos($aktivite['action'], 'update') !== false) $action_class = 'action-update';
                            elseif (strpos($aktivite['action'], 'password') !== false) $action_class = 'action-password';
                            ?>
                            <span class="action-badge <?php echo $action_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $aktivite['action'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($aktivite['description'] ?: '-'); ?></td>
                        <td class="ip-address"><?php echo htmlspecialchars($aktivite['ip_address']); ?></td>
                        <td class="device-info" title="<?php echo htmlspecialchars($aktivite['user_agent']); ?>">
                            <?php
                            $ua = $aktivite['user_agent'];
                            if (strpos($ua, 'Mobile') !== false) echo 'Mobil Cihaz';
                            elseif (strpos($ua, 'Tablet') !== false) echo 'Tablet';
                            elseif (strpos($ua, 'Windows') !== false) echo 'Windows PC';
                            elseif (strpos($ua, 'Mac') !== false) echo 'Mac';
                            elseif (strpos($ua, 'Linux') !== false) echo 'Linux';
                            else echo 'Bilinmeyen';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($toplam_sayfa > 1): ?>
            <div class="pagination">
                <?php 
                $url_params = [];
                if ($filter_user) $url_params[] = "user=$filter_user";
                if ($filter_action) $url_params[] = "action=$filter_action";
                if ($filter_date) $url_params[] = "date=$filter_date";
                $url_suffix = $url_params ? '&' . implode('&', $url_params) : '';
                ?>
                
                <?php if($sayfa > 1): ?>
                    <a href="?sayfa=1<?php echo $url_suffix; ?>">İlk</a>
                    <a href="?sayfa=<?php echo $sayfa - 1; ?><?php echo $url_suffix; ?>">Önceki</a>
                <?php endif; ?>
                
                <?php for($i = max(1, $sayfa - 2); $i <= min($toplam_sayfa, $sayfa + 2); $i++): ?>
                    <?php if($i == $sayfa): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?sayfa=<?php echo $i; ?><?php echo $url_suffix; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($sayfa < $toplam_sayfa): ?>
                    <a href="?sayfa=<?php echo $sayfa + 1; ?><?php echo $url_suffix; ?>">Sonraki</a>
                    <a href="?sayfa=<?php echo $toplam_sayfa; ?><?php echo $url_suffix; ?>">Son</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>