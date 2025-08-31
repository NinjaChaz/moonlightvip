<?php
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user
        $email = $_POST['email'];
        $password = $_POST['password'];
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = getTranslation('Invalid email format', 'အီးမေးလ် ဖော်မတ် မှားယွင်းနေသည်');
        } elseif (strlen($password) < 6) {
            $error = getTranslation('Password must be at least 6 characters', 'စကားဝှက်သည် အနည်းဆုံး စာလုံး ၆ လုံး ရှိရမည်');
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = getTranslation('Email already exists', 'ဤအီးမေးလ်ဖြင့် အကောင့်ရှိပြီးသားဖြစ်သည်');
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password, is_admin) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$email, $hashed_password, $is_admin])) {
                    $success = getTranslation('User added successfully!', 'အသုံးပြုသူ ထည့်သွင်းခြင်း အောင်မြင်ပါသည်!');
                } else {
                    $error = getTranslation('Failed to add user. Please try again.', 'အသုံးပြုသူ ထည့်သွင်းခြင်း မအောင်မြင်ပါ။ ထပ်ကြိုးစားပါ။');
                }
            }
        }
    }
    elseif (isset($_POST['edit_user'])) {
        // Edit user
        $id = $_POST['id'];
        $email = $_POST['email'];
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $is_banned = isset($_POST['is_banned']) ? 1 : 0;
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = getTranslation('Invalid email format', 'အီးမေးလ် ဖော်မတ် မှားယွင်းနေသည်');
        } else {
            // Check if email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            
            if ($stmt->fetch()) {
                $error = getTranslation('Email already exists', 'ဤအီးမေးလ်ဖြင့် အကောင့်ရှိပြီးသားဖြစ်သည်');
            } else {
                // Update user
                $stmt = $pdo->prepare("UPDATE users SET email = ?, is_admin = ?, is_banned = ? WHERE id = ?");
                
                if ($stmt->execute([$email, $is_admin, $is_banned, $id])) {
                    $success = getTranslation('User updated successfully!', 'အသုံးပြုသူ ပြင်ဆင်ခြင်း အောင်မြင်ပါသည်!');
                } else {
                    $error = getTranslation('Failed to update user. Please try again.', 'အသုံးပြုသူ ပြင်ဆင်ခြင်း မအောင်မြင်ပါ။ ထပ်ကြိုးစားပါ။');
                }
            }
        }
    }
    elseif (isset($_POST['delete_user'])) {
        // Delete user (cannot delete own account)
        $id = $_POST['id'];
        
        if ($id == $_SESSION['user_id']) {
            $error = getTranslation('You cannot delete your own account!', 'သင့်ကိုယ်ပိုင်အကောင့်ကို ဖျက်လို့မရပါ!');
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            
            if ($stmt->execute([$id])) {
                $success = getTranslation('User deleted successfully!', 'အသုံးပြုသူ ဖျက်သိမ်းခြင်း အောင်မြင်ပါသည်!');
            } else {
                $error = getTranslation('Failed to delete user. Please try again.', 'အသုံးပြုသူ ဖျက်သိမ်းခြင်း မအောင်မြင်ပါ။ ထပ်ကြိုးစားပါ။');
            }
        }
    }
    elseif (isset($_POST['reset_password'])) {
        // Reset user password
        $id = $_POST['id'];
        $password = $_POST['password'];
        
        if (strlen($password) < 6) {
            $error = getTranslation('Password must be at least 6 characters', 'စကားဝှက်သည် အနည်းဆုံး စာလုံး ၆ လုံး ရှိရမည်');
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $id])) {
                $success = getTranslation('Password reset successfully!', 'စကားဝှက် ပြန်လည်သတ်မှတ်ခြင်း အောင်မြင်ပါသည်!');
            } else {
                $error = getTranslation('Failed to reset password. Please try again.', 'စကားဝှက် ပြန်လည်သတ်မှတ်ခြင်း မအောင်မြင်ပါ။ ထပ်ကြိုးစားပါ။');
            }
        }
    }
}

// Get all users (excluding current user for deletion)
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('Manage Users', 'အသုံးပြုသူများ စီမံခန့်ခွဲမှု'); ?></h2>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Add User Form -->
    <div class="admin-form">
        <h3><?php echo getTranslation('Add New User', 'အသုံးပြုသူအသစ် ထည့်သွင်းရန်'); ?></h3>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="email"><?php echo getTranslation('Email', 'အီးမေးလ်'); ?></label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo getTranslation('Password', 'စကားဝှက်'); ?></label>
                    <input type="password" id="password" name="password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="is_admin" name="is_admin">
                    <?php echo getTranslation('Admin User', 'စီမံခန့်ခွဲသူ အသုံးပြုသူ'); ?>
                </label>
            </div>
            
            <button type="submit" name="add_user" class="btn"><?php echo getTranslation('Add User', 'အသုံးပြုသူ ထည့်သွင်းမည်'); ?></button>
        </form>
    </div>
    
    <!-- Users List -->
    <div class="admin-list">
        <h3><?php echo getTranslation('Existing Users', 'ရှိပြီးသား အသုံးပြုသူများ'); ?></h3>
        
        <?php if (!empty($users)): ?>
        <div class="users-list">
            <?php foreach ($users as $user): ?>
            <div class="user-item <?php echo $user['is_banned'] ? 'banned' : ''; ?>">
                <div class="user-info">
                    <h4><?php echo $user['email']; ?></h4>
                    <p class="user-meta">
                        <span class="badge <?php echo $user['is_admin'] ? 'badge-admin' : 'badge-user'; ?>">
                            <?php echo $user['is_admin'] ? getTranslation('Admin', 'စီမံခန့်ခွဲသူ') : getTranslation('User', 'အသုံးပြုသူ'); ?>
                        </span>
                        • 
                        <span class="status <?php echo $user['is_banned'] ? 'banned' : 'active'; ?>">
                            <?php echo $user['is_banned'] ? getTranslation('Banned', 'တားမြစ်ထားသည်') : getTranslation('Active', 'အသက်ဝင်သည်'); ?>
                        </span>
                        • 
                        <span class="joined-date">
                            <?php echo getTranslation('Joined', 'ဝင်ရောက်သည့်ရက်စွဲ'); ?>: 
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </span>
                    </p>
                </div>
                <div class="user-actions">
                    <button class="btn btn-edit" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo $user['email']; ?>', <?php echo $user['is_admin']; ?>, <?php echo $user['is_banned']; ?>)">
                        <?php echo getTranslation('Edit', 'ပြင်ဆင်မည်'); ?>
                    </button>
                    
                    <button class="btn btn-reset" onclick="openResetModal(<?php echo $user['id']; ?>, '<?php echo $user['email']; ?>')">
                        <?php echo getTranslation('Reset Password', 'စကားဝှက် ပြန်သတ်မှတ်မည်'); ?>
                    </button>
                    
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-delete" onclick="return confirm('<?php echo getTranslation('Are you sure you want to delete this user?', 'ဤအသုံးပြုသူကို ဖျက်လိုသည်မှာ သေချာပါသလား?'); ?>')">
                            <?php echo getTranslation('Delete', 'ဖျက်မည်'); ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php echo getTranslation('No users found.', 'အသုံးပြုသူများ မတွေ့ရှိပါ။'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3><?php echo getTranslation('Edit User', 'အသုံးပြုသူ ပြင်ဆင်ရန်'); ?></h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="edit_user">
            
            <div class="form-group">
                <label for="edit_email"><?php echo getTranslation('Email', 'အီးမေးလ်'); ?></label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_is_admin" name="is_admin">
                        <?php echo getTranslation('Admin User', 'စီမံခန့်ခွဲသူ အသုံးပြုသူ'); ?>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_is_banned" name="is_banned">
                        <?php echo getTranslation('Banned', 'တားမြစ်ထားသည်'); ?>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn"><?php echo getTranslation('Update User', 'အသုံးပြုသူ ပြင်ဆင်မည်'); ?></button>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3><?php echo getTranslation('Reset Password', 'စကားဝှက် ပြန်သတ်မှတ်ရန်'); ?></h3>
        <form method="POST" id="resetForm">
            <input type="hidden" name="id" id="reset_id">
            <input type="hidden" name="reset_password">
            
            <div class="form-group">
                <label for="reset_email"><?php echo getTranslation('User', 'အသုံးပြုသူ'); ?></label>
                <input type="text" id="reset_email" readonly>
            </div>
            
            <div class="form-group">
                <label for="reset_password"><?php echo getTranslation('New Password', 'စကားဝှက်အသစ်'); ?></label>
                <input type="password" id="reset_password" name="password" required>
            </div>
            
            <button type="submit" class="btn"><?php echo getTranslation('Reset Password', 'စကားဝှက် ပြန်သတ်မှတ်မည်'); ?></button>
        </form>
    </div>
</div>

<script>
// Modal functionality
const editModal = document.getElementById('editModal');
const resetModal = document.getElementById('resetModal');
const closeBtns = document.querySelectorAll('.close');

function openEditModal(id, email, is_admin, is_banned) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_is_admin').checked = is_admin;
    document.getElementById('edit_is_banned').checked = is_banned;
    
    editModal.style.display = 'block';
}

function openResetModal(id, email) {
    document.getElementById('reset_id').value = id;
    document.getElementById('reset_email').value = email;
    
    resetModal.style.display = 'block';
}

closeBtns.forEach(btn => {
    btn.onclick = function() {
        editModal.style.display = 'none';
        resetModal.style.display = 'none';
    }
});

window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
    if (event.target == resetModal) {
        resetModal.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>