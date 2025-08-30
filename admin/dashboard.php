<?php
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Admin Dashboard', 'စီမံခန့်ခွဲမှု ပင်မစာမျက်နှာ'); ?></h2>
    
    <div class="admin-links">
        <a href="series.php" class="btn"><?php echo getTranslation('Manage Series', 'စီးရီးများ စီမံခန့်ခွဲမှု'); ?></a>
        <a href="chapters.php" class="btn"><?php echo getTranslation('Manage Chapters', 'အခန်းများ စီမံခန့်ခွဲမှု'); ?></a>
        <a href="banners.php" class="btn"><?php echo getTranslation('Manage Banners', 'ဆိုင်းဘုတ်များ စီမံခန့်ခွဲမှု'); ?></a>
        <a href="notifications.php" class="btn"><?php echo getTranslation('Manage Notifications', 'အသိပေးချက်များ စီမံခန့်ခွဲမှု'); ?></a>
        <a href="users.php" class="btn"><?php echo getTranslation('Manage Users', 'အသုံးပြုသူများ စီမံခန့်ခွဲမှု'); ?></a>
    </div>
    
    <!-- Quick Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3><?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            echo $stmt->fetchColumn();
            ?></h3>
            <p><?php echo getTranslation('Total Users', 'စုစုပေါင်းအသုံးပြုသူများ'); ?></p>
        </div>
        
        <div class="stat-card">
            <h3><?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM series");
            echo $stmt->fetchColumn();
            ?></h3>
            <p><?php echo getTranslation('Total Series', 'စုစုပေါင်းစီးရီးများ'); ?></p>
        </div>
        
        <div class="stat-card">
            <h3><?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM chapters");
            echo $stmt->fetchColumn();
            ?></h3>
            <p><?php echo getTranslation('Total Chapters', 'စုစုပေါင်းအခန်းများ'); ?></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>