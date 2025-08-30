<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = TRUE");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_banned']) {
            $error = getTranslation('Your account has been banned', 'သင့်အကောင့်ကို တားမြစ်ထားသည်');
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['language'] = $user['language'];
            $_SESSION['dark_mode'] = $user['dark_mode'];
            $_SESSION['safe_mode'] = $user['safe_mode'];
            
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = getTranslation('Invalid email or password', 'အီးမေးလ် သို့မဟုတ် စကားဝှက်မှားယွင်းနေသည်');
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2><?php echo getTranslation('Admin Login', 'စီမံခန့်ခွဲသူ ဝင်ရောက်မည်'); ?></h2>
        
        <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email"><?php echo getTranslation('Email', 'အီးမေးလ်'); ?></label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password"><?php echo getTranslation('Password', 'စကားဝှက်'); ?></label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn"><?php echo getTranslation('Login', 'ဝင်ရောက်မည်'); ?></button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>