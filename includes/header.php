<?php
$dark_mode = getUserPreference('dark_mode');
$language = getUserPreference('language');
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manhwa Reader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($dark_mode): ?>
    <link rel="stylesheet" href="assets/css/dark.css">
    <?php endif; ?>
</head>
<body class="<?php echo $dark_mode ? 'dark-mode' : 'light-mode'; ?>">
    <header>
        <nav>
            <div class="logo">
                <a href="index.php">Manhwa Reader</a>
            </div>
            <div class="nav-links">
                <a href="index.php"><?php echo getTranslation('Home', 'ပင်မစာမျက်နှာ'); ?></a>
                <a href="user/history.php"><?php echo getTranslation('History', 'ဖတ်ခဲ့သည်များ'); ?></a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/"><?php echo getTranslation('Admin Panel', 'စီမံခန့်ခွဲမှုပanel'); ?></a>
                    <?php endif; ?>
                    <a href="user/logout.php"><?php echo getTranslation('Logout', 'ထွက်မည်'); ?></a>
                <?php else: ?>
                    <a href="user/login.php"><?php echo getTranslation('Login', 'ဝင်မည်'); ?></a>
                <?php endif; ?>
            </div>
            <div class="preferences">
                <button id="dark-mode-toggle"><?php echo $dark_mode ? getTranslation('Light', 'အလင်း') : getTranslation('Dark', 'အမှောင်'); ?></button>
                <button id="language-toggle"><?php echo $language == 'en' ? 'MY' : 'EN'; ?></button>
                <?php if (isLoggedIn()): ?>
                    <button id="safe-mode-toggle"><?php echo getUserPreference('safe_mode') ? getTranslation('Safe: ON', 'လုံခြုံမှု: ဖွင့်') : getTranslation('Safe: OFF', 'လုံခြုံမှု: ပိတ်'); ?></button>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>