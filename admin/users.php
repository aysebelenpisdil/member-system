<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

girisKontrol();

// Admin kontrolü
if (!adminMi()) {
    header("Location: ../dashboard.php");
    exit();
}

$mesaj = '';
$hata = '';

// Kullanıcı durumunu güncelle
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    
    switch($action) {
        case 'activate':
            $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$user_id]);
            $mesaj = "Kullanıcı aktif edildi.";
            break;
            
        case 'deactivate':
            $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$user_id]);
            $mesaj = "Kullanıcı pasif edildi.";
            break;
            
        case 'ban':
            $db->prepare("UPDATE users SET status = 'banned' WHERE id = ?")->execute([$user_id]);
            $mesaj = "Kullanıcı engellendi.";
            break;
            
        case 'delete':
            if ($user_id != $_SESSION['kullanici_id']) {
                // İlişkili kayıtları sil
                $db->prepare("DELETE FROM activity_logs WHERE user_id = ?")->execute([$user_id]);
                $db->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$user_id]);
                $db->prepare("DELETE FROM user_sessions WHERE user_id = ?")->execute([$user_id]);
                $db->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                $mesaj = "Kullanıcı silindi.";
            } else {
                $hata = "Kendi hesabınızı silemezsiniz!";
            }
            break;
            
        case 'make_admin':
            $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$user_id]);
            $mesaj = "Kullanıcı admin yapıldı.";
            break;
            
        case 'remove_admin':
            if ($user_id != $_SESSION['kullanici_id']) {
                $db->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$user_id]);
                $mesaj = "Admin yetkisi kaldırıldı.";
            } else {
                $hata = "Kendi admin yetkinizi kaldıramazsınız!";
            }
            break;
    }
}

// Arama ve filtreleme
$search = isset($_GET['search']) ? temizle($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';

// Sayfalama
$sayfa = isset($_GET['sayfa']) ? intval($_GET['sayfa']) : 1;
$limit = 20;
$offset = ($sayfa - 1) * $limit;

// Sorgu oluştur
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($filter_role) {
    $where_conditions[] = "role = ?";
    $params[] = $filter_role;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Toplam kullanıcı sayısı
$count_query = "SELECT COUNT(*) FROM users $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$toplam_kullanici = $stmt->fetchColumn();
$toplam_sayfa = ceil($toplam_kullanici / $limit);

// Kullanıcıları getir
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $db->prepare($query);
$stmt->execute($params);
$kullanicilar = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Admin Paneli</title>
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
        
        .search-bar {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 30px;
        }
        
        .search-form {
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
        
        .users-table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
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
        }
        
        .table td {
            padding: 15px;
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
        
        .badge-admin {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-user {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .action-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .action-btn {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .action-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 180px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
            border-radius: 4px;
            z-index: 1000;
        }
        
        .action-dropdown:hover .action-menu {
            display: block;
        }
        
        .action-menu a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.2s ease;
        }
        
        .action-menu a:hover {
            background-color: #f8f9fa;
        }
        
        .action-menu a.danger {
            color: #dc3545;
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
        
        .stats-info {
            text-align: right;
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
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
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .table {
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 10px;
            }
            
            .action-menu {
                left: auto;
                right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Kullanıcı Yönetimi</h1>
        <span class="admin-badge">Yönetici</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php">Genel Bakış</a>
        <a href="users.php" class="active">Kullanıcılar</a>
        <a href="activity-logs.php">Aktivite Kayıtları</a>
        <a href="settings.php">Sistem Ayarları</a>
        <a href="../dashboard.php">Ana Siteye Dön</a>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </nav>
    
    <div class="container">
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
        
        <div class="search-bar">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="search">Ara</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           placeholder="İsim veya email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Durum</label>
                    <select id="status" name="status">
                        <option value="">Tümü</option>
                        <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                        <option value="banned" <?php echo $filter_status == 'banned' ? 'selected' : ''; ?>>Engellenmiş</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="role">Rol</label>
                    <select id="role" name="role">
                        <option value="">Tümü</option>
                        <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo $filter_role == 'user' ? 'selected' : ''; ?>>Kullanıcı</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filtrele</button>
                <a href="users.php" class="btn btn-secondary">Temizle</a>
            </form>
        </div>
        
        <div class="stats-info">
            Toplam <?php echo $toplam_kullanici; ?> kullanıcı bulundu.
        </div>
        
        <div class="users-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>İsim</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Durum</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Giriş</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($kullanicilar as $kullanici): ?>
                    <tr>
                        <td>#<?php echo str_pad($kullanici['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($kullanici['name']); ?></td>
                        <td><?php echo htmlspecialchars($kullanici['email']); ?></td>
                        <td>
                            <?php if($kullanici['role'] == 'admin'): ?>
                                <span class="badge badge-admin">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-user">Kullanıcı</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $durum_badge = [
                                'active' => '<span class="badge badge-success">Aktif</span>',
                                'inactive' => '<span class="badge badge-warning">Pasif</span>',
                                'banned' => '<span class="badge badge-danger">Engellenmiş</span>'
                            ];
                            echo $durum_badge[$kullanici['status']] ?? $kullanici['status'];
                            ?>
                        </td>
                        <td><?php echo tarihFormatla($kullanici['created_at']); ?></td>
                        <td><?php echo $kullanici['last_login'] ? zamanFarki($kullanici['last_login']) : 'Hiç giriş yapmadı'; ?></td>
                        <td>
                            <div class="action-dropdown">
                                <button class="action-btn">İşlemler ▼</button>
                                <div class="action-menu">
                                    <a href="user-detail.php?id=<?php echo $kullanici['id']; ?>">Detayları Gör</a>
                                    
                                    <?php if($kullanici['status'] == 'active'): ?>
                                        <a href="?action=deactivate&id=<?php echo $kullanici['id']; ?>">Pasif Yap</a>
                                    <?php else: ?>
                                        <a href="?action=activate&id=<?php echo $kullanici['id']; ?>">Aktif Yap</a>
                                    <?php endif; ?>
                                    
                                    <?php if($kullanici['status'] != 'banned'): ?>
                                        <a href="?action=ban&id=<?php echo $kullanici['id']; ?>" class="danger">Engelle</a>
                                    <?php endif; ?>
                                    
                                    <?php if($kullanici['role'] == 'user'): ?>
                                        <a href="?action=make_admin&id=<?php echo $kullanici['id']; ?>">Admin Yap</a>
                                    <?php elseif($kullanici['id'] != $_SESSION['kullanici_id']): ?>
                                        <a href="?action=remove_admin&id=<?php echo $kullanici['id']; ?>">Admin Yetkisini Kaldır</a>
                                    <?php endif; ?>
                                    
                                    <?php if($kullanici['id'] != $_SESSION['kullanici_id']): ?>
                                        <a href="?action=delete&id=<?php echo $kullanici['id']; ?>" 
                                           class="danger" 
                                           onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">Sil</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($toplam_sayfa > 1): ?>
            <div class="pagination">
                <?php if($sayfa > 1): ?>
                    <a href="?sayfa=1<?php echo $search ? '&search='.$search : ''; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?><?php echo $filter_role ? '&role='.$filter_role : ''; ?>">İlk</a>
                    <a href="?sayfa=<?php echo $sayfa - 1; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?><?php echo $filter_role ? '&role='.$filter_role : ''; ?>">Önceki</a>
                <?php endif; ?>
                
                <?php for($i = max(1, $sayfa - 2); $i <= min($toplam_sayfa, $sayfa + 2); $i++): ?>
                    <?php if($i == $sayfa): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?sayfa=<?php echo $i; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?><?php echo $filter_role ? '&role='.$filter_role : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($sayfa < $toplam_sayfa): ?>
                    <a href="?sayfa=<?php echo $sayfa + 1; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?><?php echo $filter_role ? '&role='.$filter_role : ''; ?>">Sonraki</a>
                    <a href="?sayfa=<?php echo $toplam_sayfa; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?><?php echo $filter_role ? '&role='.$filter_role : ''; ?>">Son</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>