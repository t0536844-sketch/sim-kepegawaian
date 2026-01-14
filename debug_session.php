<?php
// debug_session.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Session Problem</h2>";

// Test 1: Cek session config
echo "<h3>1. PHP Session Configuration:</h3>";
echo "session.save_handler: " . ini_get('session.save_handler') . "<br>";
echo "session.save_path: " . ini_get('session.save_path') . "<br>";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "<br>";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "<br>";
echo "session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "<br>";

// Test 2: Cek apakah session bisa disimpan
session_start();
$_SESSION['debug_test'] = time();
$session_id = session_id();

echo "<h3>2. Session Test:</h3>";
echo "Session ID: " . $session_id . "<br>";
echo "Debug test value: " . ($_SESSION['debug_test'] ?? 'NOT SET') . "<br>";

// Test 3: Simulate page navigation
echo "<h3>3. Simulate Navigation:</h3>";
echo "<a href='debug_session.php?page=2'>Go to page 2</a><br>";

if (isset($_GET['page'])) {
    echo "<strong>Navigated to page 2</strong><br>";
    echo "Session data after navigation: ";
    echo isset($_SESSION['debug_test']) ? "✅ Masih ada" : "❌ Hilang!";
}

// Test 4: Check cookies
echo "<h3>4. Cookie Status:</h3>";
if (isset($_COOKIE[session_name()])) {
    echo "✅ Session cookie found<br>";
    echo "Cookie name: " . session_name() . "<br>";
    echo "Cookie value: " . $_COOKIE[session_name()] . "<br>";
    
    // Compare with current session
    if ($_COOKIE[session_name()] == $session_id) {
        echo "✅ Cookie matches session ID<br>";
    } else {
        echo "❌ Cookie MISMATCH!<br>";
    }
} else {
    echo "❌ NO session cookie!<br>";
}

// Test 5: Manual cookie test
echo "<h3>5. Manual Cookie Test:</h3>";
setcookie('manual_test', 'test_value', time() + 3600, '/');
echo "Manual cookie set<br>";
echo "Manual cookie value: " . ($_COOKIE['manual_test'] ?? 'Not received') . "<br>";

// Test 6: Browser check
echo "<h3>6. Browser/Server Info:</h3>";
echo "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "<br>";
echo "IP Address: " . $_SERVER['REMOTE_ADDR'] . "<br>";
echo "Server time: " . date('Y-m-d H:i:s') . "<br>";

// Solutions
echo "<hr><h2>Possible Solutions:</h2>";
echo "<ol>";
echo "<li>Check session.save_path permissions</li>";
echo "<li>Clear browser cookies</li>";
echo "<li>Try incognito/private mode</li>";
echo "<li>Check if you have multiple XAMPP instances running</li>";
echo "<li>Disable browser extensions that block cookies</li>";
echo "</ol>";
?>