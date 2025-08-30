<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get banners
$stmt = $pdo->query("
    SELECT b.*, s.title_en, s.title_my, s.cover_image 
    FROM banners b 
    LEFT JOIN series s ON b.series_id = s.id 
    ORDER BY b.sort_order
");
$banners = $stmt->fetchAll();

// Get active notification
$stmt = $pdo->query("SELECT text_en, text_my FROM notifications WHERE is_active = TRUE ORDER BY created_at DESC LIMIT 1");
$notification = $stmt->fetch();

// Get popular series
$popularSeries = getPopularSeries(10);

// Get latest updates
$latestUpdates = getLatestUpdates(10);
?>

<div class="container">
    <!-- Slideshow Banner -->
    <div class="slideshow">
        <?php foreach ($banners as $index => $banner): ?>
        <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" 
             style="background-image: url('uploads/banners/<?php echo $banner['image_path']; ?>')">
            <div class="slide-content">
                <h2><?php echo getTranslation($banner['title_en'], $banner['title_my']); ?></h2>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Notification Bar -->
    <?php if ($notification): ?>
    <div class="notification-bar">
        <?php echo getTranslation($notification['text_en'], $notification['text_my']); ?>
    </div>
    <?php endif; ?>
    
    <!-- Popular Manhwa Section -->
    <h2><?php echo getTranslation('Popular Manhwa', 'လူကြိုက်များသော Manhwa များ'); ?></h2>
    <div class="series-grid">
        <?php foreach ($popularSeries as $series): ?>
        <div class="series-card">
            <img src="uploads/covers/<?php echo $series['cover_image']; ?>" alt="<?php echo getTranslation($series['title_en'], $series['title_my']); ?>" class="series-cover">
            <div class="series-info">
                <div class="series-title"><?php echo getTranslation($series['title_en'], $series['title_my']); ?></div>
                <div class="chapter-info">
                    <span><?php echo getTranslation('Latest', 'နောက်ဆုံး'); ?>: Ch. <?php echo $series['chapter_number']; ?></span>
                    <a href="user/chapter.php?series=<?php echo $series['id']; ?>" class="btn"><?php echo getTranslation('Read', 'ဖတ်မည်'); ?></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Latest Updates Section -->
    <h2><?php echo getTranslation('Latest Updates', 'နောက်ဆုံးရသတင်း'); ?></h2>
    <div class="series-grid">
        <?php foreach ($latestUpdates as $update): ?>
        <div class="series-card">
            <img src="uploads/covers/<?php echo $update['cover_image']; ?>" alt="<?php echo getTranslation($update['title_en'], $update['title_my']); ?>" class="series-cover">
            <div class="series-info">
                <div class="series-title"><?php echo getTranslation($update['title_en'], $update['title_my']); ?></div>
                <div class="chapter-info">
                    <span>Ch. <?php echo $update['chapter_number']; ?></span>
                    <a href="user/chapter.php?series=<?php echo $update['id']; ?>" class="btn"><?php echo getTranslation('Read', 'ဖတ်မည်'); ?></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>