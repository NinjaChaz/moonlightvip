<?php
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Get all series for dropdown
$stmt = $pdo->query("SELECT id, title_en, title_my FROM series ORDER BY title_en");
$series_list = $stmt->fetchAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_chapter'])) {
        // Add new chapter
        $series_id = $_POST['series_id'];
        $chapter_number = $_POST['chapter_number'];
        $title_en = $_POST['title_en'];
        $title_my = $_POST['title_my'];
        $ouo_link = $_POST['ouo_link'];
        
        $stmt = $pdo->prepare("INSERT INTO chapters (series_id, chapter_number, title_en, title_my, ouo_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$series_id, $chapter_number, $title_en, $title_my, $ouo_link]);
        
        $chapter_id = $pdo->lastInsertId();
        
        // Handle chapter images upload
        if (isset($_FILES['chapter_images']) && !empty($_FILES['chapter_images']['name'][0])) {
            $image_count = count($_FILES['chapter_images']['name']);
            
            for ($i = 0; $i < $image_count; $i++) {
                if ($_FILES['chapter_images']['error'][$i] == UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['chapter_images']['name'][$i], PATHINFO_EXTENSION);
                    $image_name = uniqid() . '.' . $ext;
                    $upload_path = '../uploads/chapters/' . $image_name;
                    
                    if (move_uploaded_file($_FILES['chapter_images']['tmp_name'][$i], $upload_path)) {
                        $stmt = $pdo->prepare("INSERT INTO chapter_images (chapter_id, image_path, sort_order) VALUES (?, ?, ?)");
                        $stmt->execute([$chapter_id, $image_name, $i + 1]);
                    }
                }
            }
        }
        
        $success = getTranslation('Chapter added successfully!', 'အခန်း ထည့်သွင်းခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['delete_chapter'])) {
        // Delete chapter
        $chapter_id = $_POST['chapter_id'];
        
        // Get images to delete
        $stmt = $pdo->prepare("SELECT image_path FROM chapter_images WHERE chapter_id = ?");
        $stmt->execute([$chapter_id]);
        $images = $stmt->fetchAll();
        
        // Delete image files
        foreach ($images as $image) {
            if (file_exists('../uploads/chapters/' . $image['image_path'])) {
                unlink('../uploads/chapters/' . $image['image_path']);
            }
        }
        
        // Delete chapter images records
        $stmt = $pdo->prepare("DELETE FROM chapter_images WHERE chapter_id = ?");
        $stmt->execute([$chapter_id]);
        
        // Delete chapter
        $stmt = $pdo->prepare("DELETE FROM chapters WHERE id = ?");
        $stmt->execute([$chapter_id]);
        
        $success = getTranslation('Chapter deleted successfully!', 'အခန်း ဖျက်သိမ်းခြင်း အောင်မြင်ပါသည်!');
    }
}

// Get all chapters with series info
$stmt = $pdo->query("
    SELECT c.*, s.title_en as series_title_en, s.title_my as series_title_my, 
           COUNT(ci.id) as image_count
    FROM chapters c
    JOIN series s ON c.series_id = s.id
    LEFT JOIN chapter_images ci ON c.id = ci.chapter_id
    GROUP BY c.id
    ORDER BY s.title_en, c.chapter_number DESC
");
$chapters = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Manage Chapters', 'အခန်းများ စီမံခန့်ခွဲမှု'); ?></h2>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Add Chapter Form -->
    <div class="admin-form">
        <h3><?php echo getTranslation('Add New Chapter', 'အခန်းအသစ် ထည့်သွင်းရန်'); ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="series_id"><?php echo getTranslation('Series', 'စီးရီး'); ?></label>
                    <select id="series_id" name="series_id" required>
                        <option value=""><?php echo getTranslation('Select Series', 'စီးရီး ရွေးချယ်ရန်'); ?></option>
                        <?php foreach ($series_list as $series): ?>
                        <option value="<?php echo $series['id']; ?>">
                            <?php echo $series['title_en']; ?>
                            <?php if (!empty($series['title_my'])): ?>
                            (<?php echo $series['title_my']; ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="chapter_number"><?php echo getTranslation('Chapter Number', 'အခန်းနံပါတ်'); ?></label>
                    <input type="number" id="chapter_number" name="chapter_number" step="0.1" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title_en"><?php echo getTranslation('Title (English)', 'ခေါင်းစဉ် (အင်္ဂလိပ်)'); ?></label>
                    <input type="text" id="title_en" name="title_en">
                </div>
                
                <div class="form-group">
                    <label for="title_my"><?php echo getTranslation('Title (Burmese)', 'ခေါင်းစဉ် (မြန်မာ)'); ?></label>
                    <input type="text" id="title_my" name="title_my">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ouo_link"><?php echo getTranslation('Ouo Link', 'Ouo လင့်ခ်'); ?></label>
                    <input type="url" id="ouo_link" name="ouo_link" placeholder="https://ouo.io/xyz123">
                </div>
                
                <div class="form-group">
                    <label for="chapter_images"><?php echo getTranslation('Chapter Images', 'အခန်းပုံများ'); ?></label>
                    <input type="file" id="chapter_images" name="chapter_images[]" multiple accept="image/*" required>
                    <small><?php echo getTranslation('Select multiple images (they will be sorted alphabetically)', 'ပုံများစွာ ရွေးချယ်ပါ (အက္ခရာစဉ်အလိုက် စီစဉ်သွားမည်)'); ?></small>
                </div>
            </div>
            
            <button type="submit" name="add_chapter" class="btn"><?php echo getTranslation('Add Chapter', 'အခန်း ထည့်သွင်းမည်'); ?></button>
        </form>
    </div>
    
    <!-- Chapters List -->
    <div class="admin-list">
        <h3><?php echo getTranslation('Existing Chapters', 'ရှိပြီးသား အခန်းများ'); ?></h3>
        
        <?php if (!empty($chapters)): ?>
        <div class="chapters-list">
            <?php foreach ($chapters as $chapter): ?>
            <div class="chapter-item">
                <div class="chapter-info">
                    <h4>
                        <?php echo $chapter['series_title_en']; ?>
                        <?php if (!empty($chapter['series_title_my'])): ?>
                        (<?php echo $chapter['series_title_my']; ?>)
                        <?php endif; ?>
                    </h4>
                    <p>
                        <?php echo getTranslation('Chapter', 'အခန်း'); ?> <?php echo $chapter['chapter_number']; ?>
                        <?php if (!empty($chapter['title_en'])): ?>
                        - <?php echo $chapter['title_en']; ?>
                        <?php endif; ?>
                    </p>
                    <p class="image-count">
                        <?php echo $chapter['image_count']; ?> <?php echo getTranslation('images', 'ပုံများ'); ?>
                    </p>
                    <?php if (!empty($chapter['ouo_link'])): ?>
                    <p class="ouo-link">
                        <a href="<?php echo $chapter['ouo_link']; ?>" target="_blank"><?php echo getTranslation('Ouo Link', 'Ouo လင့်ခ်'); ?></a>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="chapter-actions">
                    <a href="../user/reader.php?chapter=<?php echo $chapter['id']; ?>" target="_blank" class="btn btn-view">
                        <?php echo getTranslation('View', 'ကြည့်ရန်'); ?>
                    </a>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="chapter_id" value="<?php echo $chapter['id']; ?>">
                        <button type="submit" name="delete_chapter" class="btn btn-delete" onclick="return confirm('<?php echo getTranslation('Are you sure you want to delete this chapter?', 'ဤအခန်းကို ဖျက်လိုသည်မှာ သေချာပါသလား?'); ?>')">
                            <?php echo getTranslation('Delete', 'ဖျက်မည်'); ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php echo getTranslation('No chapters found.', 'အခန်းများ မတွေ့ရှိပါ။'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>