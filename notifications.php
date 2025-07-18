<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

girisKontrol();

$kullanici_id = $_SESSION['kullanici_id'];

// Tüm bildirimleri okundu olarak işaretle
if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'all') {
    $update = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $update->execute([$kullanici_id]);
    header("Location: notifications.php");
    exit();
}

// Tek bir bildirimi okundu olarak işaretle
if (isset($_GET['read'])) {
    $bildirim_id = intval($_GET['read']);
    $update = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $update->execute([$bildirim_id, $kullanici_id]);
}

// Bildirim sil
if (isset($_GET['delete'])) {
    $bildirim_id = intval($_GET['delete']);
    $delete = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $delete->execute([$bildirim_id, $kullanici_id]);
    header("Location: notifications.php");
    exit();
}

// Bildirimleri getir
$sorgu = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$sorgu->execute([$kullanici_id]);
$bildirimler = $sorgu->fetchAll();

$okunmamis_sayisi = okunmamisBildirimSayisi($db, $kullanici_id);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirimler - Üye Sistemi</title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        
        .notifications-header {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notifications-header h2 {
            margin: 0;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 24px;
        }
        
        .mark-read-btn {
            background-color: #1a1a1a;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .mark-read-btn:hover {
            background-color: #000;
            transform: translateY(-1px);
        }
        
        .notification-item {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e8d5c4;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .notification-item.unread {
            border-left: 4px solid #1a1a1a;
        }
        
        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(26,26,26,0.05) 0%, transparent 10%);
            pointer-events: none;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .notification-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }
        
        .notification-time {
            font-size: 13px;
            color: #999;
        }
        
        .notification-message {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin: 10px 0;
        }
        
        .notification-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .notification-actions a {
            color: #666;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s ease;
        }
        
        .notification-actions a:hover {
            color: #1a1a1a;
        }
        
        .notification-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .type-info {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .type-success {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .type-warning {
            background-color: #fff3e0;
            color: #f57c00;
        }
        
        .type-error {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .empty-state {
            background-color: white;
            padding: 60px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e8d5c4;
            text-align: center;
        }
        
        .empty-state h3 {
            color: #1a1a1a;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 16px;
        }
        
        .unread-badge {
            background-color: #e53e3e;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .notifications-header {
                flex-direction: column;
                gap: 20px;
            }
            
            .notification-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🔔 BİLDİRİMLER</h1>
        <div>
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="profile.php">Profilim</a>
            <a href="notifications.php" class="active">Bildirimler</a>
            <a href="settings.php">Ayarlar</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="notifications-header">
            <h2>
                Bildirimler
                <?php if($okunmamis_sayisi > 0): ?>
                    <span class="unread-badge"><?php echo $okunmamis_sayisi; ?> yeni</span>
                <?php endif; ?>
            </h2>
            <?php if(count($bildirimler) > 0 && $okunmamis_sayisi > 0): ?>
                <a href="?mark_read=all" class="mark-read-btn">Tümünü Okundu İşaretle</a>
            <?php endif; ?>
        </div>
        
        <?php if(count($bildirimler) > 0): ?>
            <?php foreach($bildirimler as $bildirim): ?>
                <div class="notification-item <?php echo !$bildirim['is_read'] ? 'unread' : ''; ?>">
                    <div class="notification-header">
                        <h3 class="notification-title">
                            <?php echo htmlspecialchars($bildirim['title']); ?>
                            <span class="notification-type type-<?php echo $bildirim['type']; ?>">
                                <?php echo $bildirim['type']; ?>
                            </span>
                        </h3>
                        <span class="notification-time">
                            <?php echo zamanFarki($bildirim['created_at']); ?>
                        </span>
                    </div>
                    
                    <p class="notification-message">
                        <?php echo nl2br(htmlspecialchars($bildirim['message'])); ?>
                    </p>
                    
                    <div class="notification-actions">
                        <?php if(!$bildirim['is_read']): ?>
                            <a href="?read=<?php echo $bildirim['id']; ?>">Okundu İşaretle</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $bildirim['id']; ?>" onclick="return confirm('Bu bildirimi silmek istediğinizden emin misiniz?')">Sil</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Henüz bildiriminiz yok</h3>
                <p>Yeni bildirimler burada görünecek.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>