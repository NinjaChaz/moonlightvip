<?php
require_once '../includes/functions.php';

if (!isset($_GET['chapter'])) {
    header('Location: ../index.php');
    exit;
}

$chapter_id = $_GET['chapter'];
$stmt = $pdo->prepare("
    SELECT c.*, s.title_en, s.title_my, s.is_adult 
    FROM chapters c 
    JOIN series s ON c.series_id = s.id 
    WHERE c.id = ?
");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch();

if (!$chapter) {
    header('Location: ../index.php');
    exit;
}

// Check if adult content and safe mode
if ($chapter['is_adult'] && getUserPreference('safe_mode')) {
    header('Location: ../index.php');
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to Ouo ad link
    header('Location: ' . $chapter['ouo_link']);
    exit;
}

// Get chapter images
$stmt = $pdo->prepare("SELECT * FROM chapter_images WHERE chapter_id = ? ORDER BY sort_order");
$stmt->execute([$chapter_id]);
$images = $stmt->fetchAll();

// Add to reading history
$stmt = $pdo->prepare("INSERT INTO reading_history (user_id, chapter_id) VALUES (?, ?)");
$stmt->execute([$_SESSION['user_id'], $chapter_id]);

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation($chapter['title_en'], $chapter['title_my']); ?> - 
        <?php echo getTranslation('Chapter', 'အခန်း'); ?> <?php echo $chapter['chapter_number']; ?></h2>
    
    <div class="reader-container">
        <?php foreach ($images as $image): ?>
        <img src="uploads/chapters/<?php echo $image['image_path']; ?>" 
             alt="<?php echo getTranslation('Page', 'စာမျက်နှာ'); ?> <?php echo $image['sort_order']; ?>" 
             class="reader-image" loading="lazy">
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>