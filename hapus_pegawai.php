<?php
// hapus_pegawai.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: pegawai.php");
    exit();
}

$id = $_GET['id'];

// Konfirmasi dari form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get employee name before deletion for logging
    $query = "SELECT nama_lengkap FROM pegawai WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete the employee
    $delete_query = "DELETE FROM pegawai WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $id);
    
    if ($delete_stmt->execute()) {
        // Log action
        $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) VALUES (?, ?, ?, ?, ?)";
        $logStmt = $db->prepare($logQuery);
        $logStmt->execute([
            $_SESSION['user_id'],
            'DELETE',
            'pegawai',
            $id,
            'Menghapus data pegawai: ' . $pegawai['nama_lengkap']
        ]);
        
        $_SESSION['success'] = "Data pegawai berhasil dihapus!";
        header("Location: pegawai.php");
        exit();
    } else {
        $error = "Gagal menghapus data pegawai.";
    }
} else {
    // Redirect to confirmation page
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT nama_lengkap FROM pegawai WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pegawai) {
        header("Location: pegawai.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Pegawai - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Penghapusan</h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-octagon" style="font-size: 48px;"></i>
                            <h4 class="mt-3">Apakah Anda yakin?</h4>
                            <p>Anda akan menghapus data pegawai:</p>
                            <h5 class="text-danger"><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></h5>
                            <p class="text-muted">Data yang telah dihapus tidak dapat dikembalikan.</p>
                        </div>
                        
                        <form method="POST">
                            <div class="d-flex justify-content-center gap-3">
                                <a href="pegawai.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Ya, Hapus Data
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted text-center">
                        <small>ID Pegawai: <?php echo $id; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>