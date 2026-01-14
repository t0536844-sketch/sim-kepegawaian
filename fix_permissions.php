<?php
// fix_permissions.php
echo "<h3>Mengatur Permission Folder Session</h3>";

// Cek session save path
$session_path = session_save_path();
if (empty($session_path) || $session_path == '') {
    $session_path = sys_get_temp_dir();
}

echo "Session path: " . $session_path . "<br>";

// Coba buat folder jika tidak ada
if (!is_dir($session_path)) {
    if (mkdir($session_path, 0777, true)) {
        echo "✅ Folder session dibuat<br>";
    } else {
        echo "❌ Gagal membuat folder session<br>";
    }
}

// Cek dan perbaiki permission
if (is_dir($session_path)) {
    if (is_writable($session_path)) {
        echo "✅ Folder session writable<br>";
    } else {
        // Coba ubah permission
        if (chmod($session_path, 0777)) {
            echo "✅ Permission folder session diperbaiki<br>";
        } else {
            echo "❌ Gagal mengubah permission<br>";
        }
    }
    
    // Test menulis file
    $test_file = $session_path . '/test.txt';
    if (file_put_contents($test_file, 'test')) {
        echo "✅ Bisa menulis ke folder session<br>";
        unlink($test_file);
    } else {
        echo "❌ Tidak bisa menulis ke folder session<br>";
    }
}

// Alternatif: set session path ke folder aplikasi
echo "<h4>Alternatif: Set session path ke folder aplikasi</h4>";
$app_session_path = __DIR__ . '/sessions';
if (!is_dir($app_session_path)) {
    mkdir($app_session_path, 0777);
}

// Set session save path
session_save_path($app_session_path);
echo "Session path baru: " . session_save_path() . "<br>";

// Test session baru
session_start();
$_SESSION['test'] = 'Hello World';
echo "Session test value: " . $_SESSION['test'] . "<br>";

echo "<hr><h4>Cara Manual:</h4>";
echo "1. Buka php.ini<br>";
echo "2. Cari: session.save_path<br>";
echo "3. Ubah menjadi: session.save_path = \"" . $app_session_path . "\"<br>";
echo "4. Restart Apache<br>";

// Link ke test session
echo "<br><a href='session_test.php' class='btn btn-primary'>Test Session</a>";
?>