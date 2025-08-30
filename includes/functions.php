<?php
session_start();
require_once 'config/database.php';

// Language support
function getTranslation($en, $my) {
    if (isset($_SESSION['language']) && $_SESSION['language'] == 'my' && !empty($my)) {
        return $my;
    }
    return $en;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

// Get user preference
function getUserPreference($pref) {
    if (isLoggedIn()) {
        // Get from database
        global $pdo;
        $stmt = $pdo->prepare("SELECT $pref FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        return $result[$pref];
    }
    
    // Get from session or default
    if ($pref == 'dark_mode') {
        return isset($_SESSION['dark_mode']) ? $_SESSION['dark_mode'] : false;
    } elseif ($pref == 'safe_mode') {
        return isset($_SESSION['safe_mode']) ? $_SESSION['safe_mode'] : true;
    } elseif ($pref == 'language') {
        return isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    }
    
    return null;
}

// Get popular series
function getPopularSeries($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.*, COUNT(rh.id) as read_count 
        FROM series s 
        LEFT JOIN chapters c ON s.id = c.series_id 
        LEFT JOIN reading_history rh ON c.id = rh.chapter_id 
        WHERE (s.is_adult = FALSE OR ? = FALSE)
        GROUP BY s.id 
        ORDER BY read_count DESC 
        LIMIT ?
    ");
    $safe_mode = getUserPreference('safe_mode');
    $stmt->execute([$safe_mode, $limit]);
    return $stmt->fetchAll();
}

// Get latest updates
function getLatestUpdates($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.*, c.chapter_number, c.title_en as chapter_title_en, c.title_my as chapter_title_my, c.created_at 
        FROM chapters c 
        JOIN series s ON c.series_id = s.id 
        WHERE (s.is_adult = FALSE OR ? = FALSE)
        ORDER BY c.created_at DESC 
        LIMIT ?
    ");
    $safe_mode = getUserPreference('safe_mode');
    $stmt->execute([$safe_mode, $limit]);
    return $stmt->fetchAll();
}
?>