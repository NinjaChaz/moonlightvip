<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['preference'])) {
    $preference = $_POST['preference'];
    $response = ['success' => false];
    
    if (isLoggedIn()) {
        // Update in database
        $user_id = $_SESSION['user_id'];
        
        if ($preference === 'dark_mode') {
            $new_value = !getUserPreference('dark_mode');
            $stmt = $pdo->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
            $stmt->execute([$new_value, $user_id]);
            $_SESSION['dark_mode'] = $new_value;
            $response['success'] = true;
        }
        elseif ($preference === 'language') {
            $new_value = getUserPreference('language') === 'en' ? 'my' : 'en';
            $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
            $stmt->execute([$new_value, $user_id]);
            $_SESSION['language'] = $new_value;
            $response['success'] = true;
        }
        elseif ($preference === 'safe_mode') {
            $new_value = !getUserPreference('safe_mode');
            $stmt = $pdo->prepare("UPDATE users SET safe_mode = ? WHERE id = ?");
            $stmt->execute([$new_value, $user_id]);
            $_SESSION['safe_mode'] = $new_value;
            $response['success'] = true;
        }
    } else {
        // Update in session only
        if ($preference === 'dark_mode') {
            $_SESSION['dark_mode'] = !isset($_SESSION['dark_mode']) ? true : !$_SESSION['dark_mode'];
            $response['success'] = true;
        }
        elseif ($preference === 'language') {
            $_SESSION['language'] = !isset($_SESSION['language']) || $_SESSION['language'] === 'en' ? 'my' : 'en';
            $response['success'] = true;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

header('Location: ../index.php');
exit;