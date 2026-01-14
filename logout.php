<?php
// logout.php - PERBAIKAN
require_once 'config.php';

// Log logout activity jika user login
if (isset($_SESSION['user_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $logQuery = "INSERT INTO logs (user_id, action, description, ip_address) 
                VALUES (:user_id, 'LOGOUT', 'User logout', :ip)";
    $logStmt = $db->prepare($logQuery);
    $logStmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
}

// Hancurkan session
$_SESSION = array();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke login
header("Location: login.php?logout=1");
exit();
?>