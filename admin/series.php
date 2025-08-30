<?php
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_series'])) {
        // Add new series
        $title_en = $_POST['title_en'];
        $title_my = $_POST['title_my'];
        $description_en = $_POST['description_en'];
        $description_my = $_POST['description_my'];
        $is_adult = isset($_POST['is_adult']) ? 1 : 0;
        
        // Handle cover image upload
        $cover_image = '';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $cover_image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], '../uploads/covers/' . $cover_image);
        }
        
        $stmt = $pdo->prepare("INSERT INTO series (title_en, title_my, description_en, description_my, cover_image, is_adult) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title_en, $title_my, $description_en, $description_my, $cover_image, $is_adult]);
        
        $success = getTranslation('Series added successfully!', 'စီးရီး ထည့်သွင်းခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['edit_series'])) {
        // Edit existing series
        $id = $_POST['id'];
        $title_en = $_POST['title_en'];
        $title_my = $_POST['title_my'];
        $description_en = $_POST['description_en'];
        $description_my = $_POST['description_my'];
        $is_adult = isset($_POST['is_adult']) ? 1 : 0;
        
        // Handle cover image update
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $cover_image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], '../uploads/covers/' . $cover_image);
            
            // Delete old cover image
            $stmt = $pdo->prepare("SELECT cover_image FROM series WHERE id = ?");
            $stmt->execute([$id]);
            $old_cover = $stmt->fetchColumn();
            if ($old_cover && file_exists('../uploads/covers/' . $old_cover)) {
                unlink('../uploads/covers/' . $old_cover);
            }
            
            $stmt = $pdo->prepare("UPDATE series SET title_en = ?, title_my = ?, description_en = ?, description_my = ?, cover_image = ?, is_adult = ? WHERE id = ?");
            $stmt->execute([$title_en, $title_my, $description_en, $description_my, $cover_image, $is_adult, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE series SET title_en = ?, title_my = ?, description_en = ?, description_my = ?, is_adult = ? WHERE id = ?");
            $stmt->execute([$title_en, $title_my, $description_en, $description_my, $is_adult, $id]);
        }
        
        $success = getTranslation('Series updated successfully!', 'စီးရီး ပြင်ဆင်ခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['delete_series'])) {
        // Delete series
        $id = $_POST['id'];
        
        // Delete cover image
        $stmt = $pdo->prepare("SELECT cover_image FROM series WHERE id = ?");
        $stmt->execute([$id]);
        $cover_image = $stmt->fetchColumn();
        if ($cover_image && file_exists('../uploads/covers/' . $cover_image)) {
            unlink('../uploads/covers/' . $cover_image);
        }
        
        $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
        $stmt->execute([$id]);
        
        $success = getTranslation('Series deleted successfully!', 'စီးရီး ဖျက်သိမ်းခြင်း အောင်မြင်ပါသည်!');
    }
}

// Get all series
$stmt = $pdo->query("SELECT * FROM series ORDER BY title_en");
$all_series = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Manage Series', 'စီးရီးများ စီမံခန့်ခွဲမှု'); ?></h2>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Add Series Form -->
    <div class="admin-form">
        <h3><?php echo getTranslation('Add New Series', 'စီးရီးအသစ် ထည့်သွင်းရန်'); ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="title_en"><?php echo getTranslation('Title (English)', 'ခေါင်းစဉ် (အင်္ဂလိပ်)'); ?></label>
                    <input type="text" id="title_en" name="title_en" required>
                </div>
                <div class="form-group">
                    <label for="title_my"><?php echo getTranslation('Title (Burmese)', 'ခေါင်းစဉ် (မြန်မာ)'); ?></label>
                    <input type="text" id="title_my" name="title_my">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="description_en"><?php echo getTranslation('Description (English)', 'ဖော်ပြချက် (အင်္ဂလိပ်)'); ?></label>
                    <textarea id="description_en" name="description_en" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="description_my"><?php echo getTranslation('Description (Burmese)', 'ဖော်ပြချက် (မြန်မာ)'); ?></label>
                    <textarea id="description_my" name="description_my" rows="3"></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cover_image"><?php echo getTranslation('Cover Image', 'မျက်နှာဖုံး ပုံ'); ?></label>
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="is_adult" name="is_adult">
                        <?php echo getTranslation('Adult Content', 'လူကြီးများအတွက် အကြောင်းအရာ'); ?>
                    </label>
                </div>
            </div>
            
            <button type="submit" name="add_series" class="btn"><?php echo getTranslation('Add Series', 'စီးရီး ထည့်သွင်းမည်'); ?></button>
        </form>
    </div>
    
    <!-- Series List -->
    <div class="admin-list">
        <h3><?php echo getTranslation('Existing Series', 'ရှိပြီးသား စီးရီးများ'); ?></h3>
        
        <?php if (!empty($all_series)): ?>
        <div class="series-list">
            <?php foreach ($all_series as $series): ?>
            <div class="series-item">
                <div class="series-info">
                    <img src="../uploads/covers/<?php echo $series['cover_image']; ?>" alt="<?php echo $series['title_en']; ?>" class="series-thumb">
                    <div class="series-details">
                        <h4><?php echo $series['title_en']; ?></h4>
                        <?php if (!empty($series['title_my'])): ?>
                        <p><?php echo $series['title_my']; ?></p>
                        <?php endif; ?>
                        <?php if ($series['is_adult']): ?>
                        <span class="badge badge-adult"><?php echo getTranslation('Adult', 'လူကြီးများအတွက်'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="series-actions">
                    <button class="btn btn-edit" onclick="openEditModal(<?php echo $series['id']; ?>, '<?php echo $series['title_en']; ?>', '<?php echo $series['title_my']; ?>', '<?php echo addslashes($series['description_en']); ?>', '<?php echo addslashes($series['description_my']); ?>', <?php echo $series['is_adult']; ?>)">
                        <?php echo getTranslation('Edit', 'ပြင်ဆင်မည်'); ?>
                    </button>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $series['id']; ?>">
                        <button type="submit" name="delete_series" class="btn btn-delete" onclick="return confirm('<?php echo getTranslation('Are you sure you want to delete this series?', 'ဤစီးရီးကို ဖျက်လိုသည်မှာ သေချာပါသလား?'); ?>')">
                            <?php echo getTranslation('Delete', 'ဖျက်မည်'); ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php echo getTranslation('No series found.', 'စီးရီးများ မတွေ့ရှိပါ။'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3><?php echo getTranslation('Edit Series', 'စီးရီး ပြင်ဆင်ရန်'); ?></h3>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="edit_series">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_title_en"><?php echo getTranslation('Title (English)', 'ခေါင်းစဉ် (အင်္ဂလိပ်)'); ?></label>
                    <input type="text" id="edit_title_en" name="title_en" required>
                </div>
                <div class="form-group">
                    <label for="edit_title_my"><?php echo getTranslation('Title (Burmese)', 'ခေါင်းစဉ် (မြန်မာ)'); ?></label>
                    <input type="text" id="edit_title_my" name="title_my">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_description_en"><?php echo getTranslation('Description (English)', 'ဖော်ပြချက် (အင်္ဂလိပ်)'); ?></label>
                    <textarea id="edit_description_en" name="description_en" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_description_my"><?php echo getTranslation('Description (Burmese)', 'ဖော်ပြချက် (မြန်မာ)'); ?></label>
                    <textarea id="edit_description_my" name="description_my" rows="3"></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_cover_image"><?php echo getTranslation('Cover Image', 'မျက်နှာဖုံး ပုံ'); ?></label>
                    <input type="file" id="edit_cover_image" name="cover_image" accept="image/*">
                    <small><?php echo getTranslation('Leave empty to keep current image', 'လက်ရှိပုံကို ထားရန်အတွက် ဗလာချန်ထားပါ'); ?></small>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_is_adult" name="is_adult">
                        <?php echo getTranslation('Adult Content', 'လူကြီးများအတွက် အကြောင်းအရာ'); ?>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn"><?php echo getTranslation('Update Series', 'စီးရီး ပြင်ဆင်မည်'); ?></button>
        </form>
    </div>
</div>

<script>
// Modal functionality
const modal = document.getElementById('editModal');
const closeBtn = document.querySelector('.close');

function openEditModal(id, title_en, title_my, description_en, description_my, is_adult) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_title_en').value = title_en;
    document.getElementById('edit_title_my').value = title_my;
    document.getElementById('edit_description_en').value = description_en;
    document.getElementById('edit_description_my').value = description_my;
    document.getElementById('edit_is_adult').checked = is_adult;
    
    modal.style.display = 'block';
}

closeBtn.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>