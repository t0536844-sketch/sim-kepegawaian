<?php
// sidebar.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<div class="sidebar">
    <div class="text-center mb-4">
        <h4>RSUD MIMIKA</h4>
        <small>Sistem Kepegawaian</small>
    </div>
    <div class="px-3 mb-4">
        <div class="bg-white rounded-pill p-2 text-dark text-center">
            <i class="bi bi-person-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?>
            <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></span>
        </div>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
           href="dashboard.php">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pegawai.php' ? 'active' : ''; ?>" 
           href="pegawai.php">
            <i class="bi bi-people me-2"></i> Data Pegawai
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tambah_pegawai.php' ? 'active' : ''; ?>" 
           href="tambah_pegawai.php">
            <i class="bi bi-person-plus me-2"></i> Tambah Pegawai
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'import.php' ? 'active' : ''; ?>" 
           href="import.php">
            <i class="bi bi-upload me-2"></i> Import Data
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'export.php' ? 'active' : ''; ?>" 
           href="export.php">
            <i class="bi bi-download me-2"></i> Export Data
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" 
           href="laporan.php">
            <i class="bi bi-file-earmark-text me-2"></i> Laporan
        </a>
        <?php if (($_SESSION['role'] ?? '') == 'admin'): ?>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
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