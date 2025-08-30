<?php
require_once '../includes/functions.php';

if (!isset($_GET['series'])) {
    header('Location: ../index.php');
    exit;
}

$series_id = $_GET['series'];
$stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
$stmt->execute([$series_id]);
$series = $stmt->fetch();

if (!$series) {
    header('Location: ../index.php');
    exit;
}

// Check if adult content and safe mode
if ($series['is_adult'] && getUserPreference('safe_mode')) {
    header('Location: ../index.php');
    exit;
}

// Get all chapters for this series
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE series_id = ? ORDER BY chapter_number DESC");
$stmt->execute([$series_id]);
$chapters = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <!-- Series Header -->
    <div class="series-header">
        <div class="series-cover-large">
            <img src="../uploads/covers/<?php echo $series['cover_image']; ?>" alt="<?php echo getTranslation($series['title_en'], $series['title_my']); ?>">
        </div>
        <div class="series-details">
            <h1><?php echo getTranslation($series['title_en'], $series['title_my']); ?></h1>
            <div class="series-description">
                <?php echo getTranslation($series['description_en'], $series['description_my']); ?>
            </div>
            <div class="series-meta">
                <span class="chapters-count"><?php echo count($chapters) . ' ' . getTranslation('Chapters', 'အခန်းများ'); ?></span>
                <?php if ($series['is_adult']): ?>
                <span class="adult-warning"><?php echo getTranslation('Adult Content', 'လူကြီးများအတွက် အကြောင်းအရာ'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Chapters List -->
    <div class="chapters-container">
        <h2><?php echo getTranslation('Chapters', 'အခန်းများ'); ?></h2>
        
        <?php if (!empty($chapters)): ?>
        <div class="chapters-list">
            <?php foreach ($chapters as $chapter): ?>
            <div class="chapter-item">
                <div class="chapter-info">
                    <span class="chapter-number"><?php echo getTranslation('Chapter', 'အခန်း'); ?> <?php echo $chapter['chapter_number']; ?></span>
                    <?php if (!empty($chapter['title_en']) || !empty($chapter['title_my'])): ?>
                    <span class="chapter-title">- <?php echo getTranslation($chapter['title_en'], $chapter['title_my']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="chapter-actions">
                    <a href="reader.php?chapter=<?php echo $chapter['id']; ?>" class="btn btn-read">
                        <?php echo getTranslation('Read', 'ဖတ်မည်'); ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-chapters">
            <p><?php echo getTranslation('No chapters available yet.', 'ယခုဦးထုတ်ဝေရန် အခန်းများ မရှိသေးပါ။'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>