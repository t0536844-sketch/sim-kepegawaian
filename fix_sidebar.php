<?php
// fix_sidebar.php - Script untuk memperbaiki error sidebar
$files = [
    'dashboard.php',
    'pegawai.php', 
    'tambah_pegawai.php',
    'import.php',
    'export.php',
    'laporan.php',
    'users.php'
];

$sidebar_code = <<<'HTML'
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4>RSUD MIMIKA</h4>
            <small>Sistem Kepegawaian</small>
        </div>
        <div class="px-3 mb-4">
            <div class="bg-white rounded-pill p-2 text-dark text-center">
                <i class="bi bi-person-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION[\'nama_lengkap\']); ?>
                <span class="badge bg-primary ms-2"><?php echo $_SESSION[\'role\']; ?></span>
            </div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'dashboard.php\' ? \'active\' : \'\'; ?>" 
               href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'pegawai.php\' ? \'active\' : \'\'; ?>" 
               href="pegawai.php">
                <i class="bi bi-people me-2"></i> Data Pegawai
            </a>
            <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'tambah_pegawai.php\' ? \'active\' : \'\'; ?>" 
               href="tambah_pegawai.php">
                <i class="bi bi-person-plus me-2"></i> Tambah Pegawai
            </a>
            <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'import.php\' ? \'active\' : \'\'; ?>" 
               href="import.php">
                <i class="bi bi-upload me-2"></i> Import Data
            </a>
            <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'export.php\' ? \'active\' : \'\'; ?>" 
               href="export.php">
                <i class="bi bi-download me-2"></i> Export Data
            </a>
            <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'laporan.php\' ? \'active\' : \'\'; ?>" 
               href="laporan.php">
                <i class="bi bi-file-earmark-text me-2"></i> Laporan
            </a>
            <?php if ($_SESSION[\'role\'] == \'admin\'): ?>
                <a class="nav-link <?php echo basename($_SERVER[\'PHP_SELF\']) == \'users.php\' ? \'active\' : \'\'; ?>" 
                   href="users.php">
                    <i class="bi bi-shield-lock me-2"></i> Manajemen User
                </a>
            <?php endif; ?>
            <div class="mt-5 pt-5"></div>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>
    <!-- END SIDEBAR -->
HTML;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Ganti include sidebar dengan kode sidebar
        $new_content = preg_replace(
            '/<\?php\s*include\s*[\'"]sidebar\.php[\'"]\s*;\s*\?>/i',
            $sidebar_code,
            $content
        );
        
        // Jika masih ada baris include yang terlewat
        $new_content = str_replace("include 'sidebar.php';", $sidebar_code, $new_content);
        $new_content = str_replace('include "sidebar.php";', $sidebar_code, $new_content);
        $new_content = str_replace("<?php include 'sidebar.php'; ?>", $sidebar_code, $new_content);
        $new_content = str_replace('<?php include "sidebar.php"; ?>', $sidebar_code, $new_content);
        
        // Simpan file
        file_put_contents($file, $new_content);
        echo "Fixed: $file<br>";
    }
}

echo "<h3>✅ Semua file sudah diperbaiki!</h3>";
echo "<p>Jalankan <a href='login.php'>login.php</a> untuk menguji aplikasi.</p>";
?>