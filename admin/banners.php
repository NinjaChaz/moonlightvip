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
    if (isset($_POST['add_banner'])) {
        // Add new banner
        $series_id = $_POST['series_id'];
        $sort_order = $_POST['sort_order'];
        
        // Handle banner image upload
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $banner_image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['banner_image']['tmp_name'], '../uploads/banners/' . $banner_image);
            
            $stmt = $pdo->prepare("INSERT INTO banners (image_path, series_id, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$banner_image, $series_id, $sort_order]);
            
            $success = getTranslation('Banner added successfully!', 'ဆိုင်းဘုတ် ထည့်သွင်းခြင်း အောင်မြင်ပါသည်!');
        } else {
            $error = getTranslation('Please select a banner image.', 'ကျေးဇူးပြု၍ ဆိုင်းဘုတ်ပုံကို ရွေးချယ်ပါ။');
        }
    }
    elseif (isset($_POST['delete_banner'])) {
        // Delete banner
        $banner_id = $_POST['banner_id'];
        
        // Get banner image to delete
        $stmt = $pdo->prepare("SELECT image_path FROM banners WHERE id = ?");
        $stmt->execute([$banner_id]);
        $banner_image = $stmt->fetchColumn();
        
        // Delete image file
        if ($banner_image && file_exists('../uploads/banners/' . $banner_image)) {
            unlink('../uploads/banners/' . $banner_image);
        }
        
        // Delete banner record
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->execute([$banner_id]);
        
        $success = getTranslation('Banner deleted successfully!', 'ဆိုင်းဘုတ် ဖျက်သိမ်းခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['update_order'])) {
        // Update banner order
        foreach ($_POST['order'] as $banner_id => $order) {
            $stmt = $pdo->prepare("UPDATE banners SET sort_order = ? WHERE id = ?");
            $stmt->execute([$order, $banner_id]);
        }
        
        $success = getTranslation('Banner order updated successfully!', 'ဆိုင်းဘုတ် အစဉ်လိုက် ပြင်ဆင်ခြင်း အောင်မြင်ပါသည်!');
    }
}

// Get all banners with series info
$stmt = $pdo->query("
    SELECT b.*, s.title_en, s.title_my 
    FROM banners b
    LEFT JOIN series s ON b.series_id = s.id
    ORDER BY b.sort_order ASC
");
$banners = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Manage Banners', 'ဆိုင်းဘုတ်များ စီမံခန့်ခွဲမှု'); ?></h2>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Add Banner Form -->
    <div class="admin-form">
        <h3><?php echo getTranslation('Add New Banner', 'ဆိုင်းဘုတ်အသစ် ထည့်သွင်းရန်'); ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="series_id"><?php echo getTranslation('Series (Optional)', 'စီးရီး (ချန်လှပ်ထားနိုင်သည်)'); ?></label>
                    <select id="series_id" name="series_id">
                        <option value=""><?php echo getTranslation('No Series Link', 'စီးရီးလင့်ခ် မပါရှိ'); ?></option>
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
                    <label for="sort_order"><?php echo getTranslation('Sort Order', 'အစဉ်လိုက်'); ?></label>
                    <input type="number" id="sort_order" name="sort_order" min="1" value="1" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="banner_image"><?php echo getTranslation('Banner Image', 'ဆိုင်းဘုတ်ပုံ'); ?></label>
                <input type="file" id="banner_image" name="banner_image" accept="image/*" required>
                <small><?php echo getTranslation('Recommended size: 1200x400px', 'အကြံပြုအရွယ်အစား: 1200x400px'); ?></small>
            </div>
            
            <button type="submit" name="add_banner" class="btn"><?php echo getTranslation('Add Banner', 'ဆိုင်းဘုတ် ထည့်သွင်းမည်'); ?></button>
        </form>
    </div>
    
    <!-- Banners List -->
    <div class="admin-list">
        <h3><?php echo getTranslation('Existing Banners', 'ရှိပြီးသား ဆိုင်းဘုတ်များ'); ?></h3>
        
        <?php if (!empty($banners)): ?>
        <form method="POST">
            <input type="hidden" name="update_order">
            
            <div class="banners-list">
                <?php foreach ($banners as $banner): ?>
                <div class="banner-item">
                    <div class="banner-image">
                        <img src="../uploads/banners/<?php echo $banner['image_path']; ?>" alt="Banner">
                    </div>
                    <div class="banner-info">
                        <p>
                            <strong><?php echo getTranslation('Sort Order', 'အစဉ်လိုက်'); ?>:</strong>
                            <input type="number" name="order[<?php echo $banner['id']; ?>]" value="<?php echo $banner['sort_order']; ?>" min="1" class="order-input">
                        </p>
                        <?php if ($banner['series_id']): ?>
                        <p>
                            <strong><?php echo getTranslation('Linked Series', 'ချိတ်ဆက်ထားသော စီးရီး'); ?>:</strong>
                            <?php echo $banner['title_en']; ?>
                            <?php if (!empty($banner['title_my'])): ?>
                            (<?php echo $banner['title_my']; ?>)
                            <?php endif; ?>
                        </p>
                        <?php else: ?>
                        <p><strong><?php echo getTranslation('No Series Link', 'စီးရီးလင့်ခ် မပါရှိ'); ?></strong></p>
                        <?php endif; ?>
                    </div>
                    <div class="banner-actions">
                        <button type="submit" name="update_order" class="btn btn-update"><?php echo getTranslation('Update Order', 'အစဉ်လိုက် ပြင်ဆင်မည်'); ?></button>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                            <button type="submit" name="delete_banner" class="btn btn-delete" onclick="return confirm('<?php echo getTranslation('Are you sure you want to delete this banner?', 'ဤဆိုင်းဘုတ်ကို ဖျက်လိုသည်မှာ သေချာပါသလား?'); ?>')">
                                <?php echo getTranslation('Delete', 'ဖျက်မည်'); ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
        <?php else: ?>
        <p><?php echo getTranslation('No banners found.', 'ဆိုင်းဘုတ်များ မတွေ့ရှိပါ။'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>