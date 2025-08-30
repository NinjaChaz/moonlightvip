<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user's reading history
$stmt = $pdo->prepare("
    SELECT rh.*, c.chapter_number, c.title_en as chapter_title_en, c.title_my as chapter_title_my, 
           s.title_en as series_title_en, s.title_my as series_title_my, s.cover_image, s.id as series_id
    FROM reading_history rh
    JOIN chapters c ON rh.chapter_id = c.id
    JOIN series s ON c.series_id = s.id
    WHERE rh.user_id = ?
    ORDER BY rh.read_at DESC
    LIMIT 50
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Recently Read', 'မကြာသေးမီ ဖတ်ရှုခဲ့သော'); ?></h2>
    
    <?php if (!empty($history)): ?>
    <div class="history-list">
        <?php foreach ($history as $item): ?>
        <div class="history-item">
            <div class="history-cover">
                <img src="../uploads/covers/<?php echo $item['cover_image']; ?>" alt="<?php echo getTranslation($item['series_title_en'], $item['series_title_my']); ?>">
            </div>
            <div class="history-details">
                <a href="chapter.php?series=<?php echo $item['series_id']; ?>" class="history-series">
                    <?php echo getTranslation($item['series_title_en'], $item['series_title_my']); ?>
                </a>
                <div class="history-chapter">
                    <?php echo getTranslation('Chapter', 'အခန်း'); ?> <?php echo $item['chapter_number']; ?>
                    <?php if (!empty($item['chapter_title_en']) || !empty($item['chapter_title_my'])): ?>
                    - <?php echo getTranslation($item['chapter_title_en'], $item['chapter_title_my']); ?>
                    <?php endif; ?>
                </div>
                <div class="history-time">
                    <?php 
                    $read_time = strtotime($item['read_at']);
                    $time_ago = time() - $read_time;
                    
                    if ($time_ago < 3600) {
                        $mins = floor($time_ago / 60);
                        echo $mins . ' ' . getTranslation('minutes ago', 'မိနစ်က');
                    } elseif ($time_ago < 86400) {
                        $hours = floor($time_ago / 3600);
                        echo $hours . ' ' . getTranslation('hours ago', 'နာရီက');
                    } elseif ($time_ago < 2592000) {
                        $days = floor($time_ago / 86400);
                        echo $days . ' ' . getTranslation('days ago', 'ရက်က');
                    } else {
                        echo date('M j, Y', $read_time);
                    }
                    ?>
                </div>
            </div>
            <div class="history-actions">
                <a href="reader.php?chapter=<?php echo $item['chapter_id']; ?>" class="btn btn-read">
                    <?php echo getTranslation('Read Again', 'ထပ်ဖတ်မည်'); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-history">
        <p><?php echo getTranslation('You have no reading history yet.', 'သင့်တွင် ဖတ်ရှုမှု မှတ်တမ်း မရှိသေးပါ။'); ?></p>
        <a href="series.php" class="btn"><?php echo getTranslation('Browse Series', 'စီးရီးများ ရှာဖွေရန်'); ?></a>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>