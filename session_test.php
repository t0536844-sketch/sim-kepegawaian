<?php
// session_test.php - Untuk debugging session
echo "<h3>Session Debug Information</h3>";

// Cek apakah session berjalan
if (session_status() === PHP_SESSION_NONE) {
    echo "❌ Session belum dimulai<br>";
    session_start();
    echo "✅ Session dimulai sekarang<br>";
} else {
    echo "✅ Session sudah aktif<br>";
}

// Cek session ID
echo "Session ID: " . session_id() . "<br>";

// Cek session save path
echo "Session Save Path: " . session_save_path() . "<br>";

// Cek permission folder session
$session_path = session_save_path();
if (!empty($session_path)) {
    if (is_writable($session_path)) {
        echo "✅ Session path writable<br>";
    } else {
        echo "❌ Session path NOT writable!<br>";
        echo "Try: chmod 777 " . $session_path . "<br>";
    }
}

// Cek cookie
echo "<h4>Cookie Information:</h4>";
if (isset($_COOKIE[session_name()])) {
    echo "✅ Session cookie ditemukan: " . $_COOKIE[session_name()] . "<br>";
} else {
    echo "❌ Session cookie TIDAK ditemukan<br>";
}

// Cek session variables
echo "<h4>Session Variables:</h4>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test set session variable
$_SESSION['test_time'] = time();
echo "Test session variable set: " . $_SESSION['test_time'] . "<br>";

// Link untuk refresh
echo "<br><a href='session_test.php'>Refresh page</a> | ";
echo "<a href='session_test.php?destroy=1'>Destroy Session</a>";

// Destroy session jika diminta
if (isset($_GET['destroy'])) {
    session_destroy();
    echo "<br>✅ Session destroyed!";
}
?>