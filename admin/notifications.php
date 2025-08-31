<?php
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_notification'])) {
        // Add new notification
        $text_en = $_POST['text_en'];
        $text_my = $_POST['text_my'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO notifications (text_en, text_my, is_active) VALUES (?, ?, ?)");
        $stmt->execute([$text_en, $text_my, $is_active]);
        
        $success = getTranslation('Notification added successfully!', 'အသိပေးချက် ထည့်သွင်းခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['edit_notification'])) {
        // Edit notification
        $id = $_POST['id'];
        $text_en = $_POST['text_en'];
        $text_my = $_POST['text_my'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE notifications SET text_en = ?, text_my = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$text_en, $text_my, $is_active, $id]);
        
        $success = getTranslation('Notification updated successfully!', 'အသိပေးချက် ပြင်ဆင်ခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['delete_notification'])) {
        // Delete notification
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$id]);
        
        $success = getTranslation('Notification deleted successfully!', 'အသိပေးချက် ဖျက်သိမ်းခြင်း အောင်မြင်ပါသည်!');
    }
    elseif (isset($_POST['toggle_status'])) {
        // Toggle notification status
        $id = $_POST['id'];
        $is_active = $_POST['is_active'] ? 0 : 1;
        
        $stmt = $pdo->prepare("UPDATE notifications SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $id]);
        
        header('Location: notifications.php');
        exit;
    }
}

// Get all notifications
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Manage Notifications', 'အသိပေးချက်များ စီမံခန့်ခွဲမှု'); ?></h2>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Add Notification Form -->
    <div class="admin-form">
        <h3><?php echo getTranslation('Add New Notification', 'အသိပေးချက်အသစ် ထည့်သွင်းရန်'); ?></h3>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="text_en"><?php echo getTranslation('Text (English)', 'စာသား (အင်္ဂလိပ်)'); ?></label>
                    <textarea id="text_en" name="text_en" rows="2" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="text_my"><?php echo getTranslation('Text (Burmese)', 'စာသား (မြန်မာ)'); ?></label>
                    <textarea id="text_my" name="text_my" rows="2"></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    <?php echo getTranslation('Active', 'အသက်ဝင်သည်'); ?>
                </label>
            </div>
            
            <button type="submit" name="add_notification" class="btn"><?php echo getTranslation('Add Notification', 'အသိပေးချက် ထည့်သွင်းမည်'); ?></button>
        </form>
    </div>
    
    <!-- Notifications List -->
    <div class="admin-list">
        <h3><?php echo getTranslation('Existing Notifications', 'ရှိပြီးသား အသိပေးချက်များ'); ?></h3>
        
        <?php if (!empty($notifications)): ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
            <div class="notification-item <?php echo $notification['is_active'] ? 'active' : 'inactive'; ?>">
                <div class="notification-content">
                    <p><strong><?php echo getTranslation('English', 'အင်္ဂလိပ်'); ?>:</strong> <?php echo $notification['text_en']; ?></p>
                    <?php if (!empty($notification['text_my'])): ?>
                    <p><strong><?php echo getTranslation('Burmese', 'မြန်မာ'); ?>:</strong> <?php echo $notification['text_my']; ?></p>
                    <?php endif; ?>
                    <p class="notification-meta">
                        <strong><?php echo getTranslation('Status', 'အခြေအနေ'); ?>:</strong> 
                        <span class="status <?php echo $notification['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $notification['is_active'] ? getTranslation('Active', 'အသက်ဝင်သည်') : getTranslation('Inactive', 'အသက်မဝင်သည်'); ?>
                        </span>
                        • 
                        <strong><?php echo getTranslation('Created', 'ဖန်တီးသည့်ရက်စွဲ'); ?>:</strong> 
                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                    </p>
                </div>
                <div class="notification-actions">
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                        <input type="hidden" name="is_active" value="<?php echo $notification['is_active']; ?>">
                        <button type="submit" name="toggle_status" class="btn btn-status">
                            <?php echo $notification['is_active'] ? getTranslation('Deactivate', 'ပိတ်မည်') : getTranslation('Activate', 'ဖွင့်မည်'); ?>
                        </button>
                    </form>
                    
                    <button class="btn btn-edit" onclick="openEditModal(<?php echo $notification['id']; ?>, '<?php echo addslashes($notification['text_en']); ?>', '<?php echo addslashes($notification['text_my']); ?>', <?php echo $notification['is_active']; ?>)">
                        <?php echo getTranslation('Edit', 'ပြင်ဆင်မည်'); ?>
                    </button>
                    
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                        <button type="submit" name="delete_notification" class="btn btn-delete" onclick="return confirm('<?php echo getTranslation('Are you sure you want to delete this notification?', 'ဤအသိပေးချက်ကို ဖျက်လိုသည်မှာ သေချာပါသလား?'); ?>')">
                            <?php echo getTranslation('Delete', 'ဖျက်မည်'); ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php echo getTranslation('No notifications found.', 'အသိပေးချက်များ မတွေ့ရှိပါ။'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3><?php echo getTranslation('Edit Notification', 'အသိပေးချက် ပြင်ဆင်ရန်'); ?></h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="edit_notification">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_text_en"><?php echo getTranslation('Text (English)', 'စာသား (အင်္ဂလိပ်)'); ?></label>
                    <textarea id="edit_text_en" name="text_en" rows="2" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_text_my"><?php echo getTranslation('Text (Burmese)', 'စာသား (မြန်မာ)'); ?></label>
                    <textarea id="edit_text_my" name="text_my" rows="2"></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="edit_is_active" name="is_active">
                    <?php echo getTranslation('Active', 'အသက်ဝင်သည်'); ?>
                </label>
            </div>
            
            <button type="submit" class="btn"><?php echo getTranslation('Update Notification', 'အသိပေးချက် ပြင်ဆင်မည်'); ?></button>
        </form>
    </div>
</div>

<script>
// Modal functionality
const modal = document.getElementById('editModal');
const closeBtn = document.querySelector('.close');

function openEditModal(id, text_en, text_my, is_active) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_text_en').value = text_en;
    document.getElementById('edit_text_my').value = text_my;
    document.getElementById('edit_is_active').checked = is_active;
    
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