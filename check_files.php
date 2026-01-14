<?php
// check_files.php
$required_files = [
    'config.php',
    'login.php',
    'dashboard.php',
    'pegawai.php',
    'tambah_pegawai.php',
    'logout.php'
];

echo "<h3>Checking Required Files</h3>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<span style='color:green;'>✅ $file - OK</span><br>";
    } else {
        echo "<span style='color:red;'>❌ $file - MISSING!</span><br>";
    }
}
?>