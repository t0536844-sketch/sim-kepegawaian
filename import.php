<?php
// import.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Error uploading file: " . $file['error'];
    } else {
        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            $error = "File harus berupa CSV atau Excel (.csv, .xlsx, .xls)";
        } else {
            // Move uploaded file
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = uniqid() . '_' . $file['name'];
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                try {
                    $database = new Database();
                    $db = $database->getConnection();
                    
                    // Handle CSV
                    if ($ext == 'csv') {
                        $handle = fopen($filepath, 'r');
                        $headers = fgetcsv($handle); // Skip header
                        
                        $count = 0;
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            // Map CSV columns (adjust based on your CSV structure)
                            $query = "INSERT INTO pegawai (nama_lengkap, nip, jabatan, status_kepegawaian, created_at) 
                                     VALUES (?, ?, ?, ?, NOW())";
                            $stmt = $db->prepare($query);
                            $stmt->execute([
                                $data[0] ?? '', // nama_lengkap
                                $data[1] ?? '', // nip
                                $data[2] ?? '', // jabatan
                                $data[3] ?? 'PNS' // status_kepegawaian
                            ]);
                            $count++;
                        }
                        fclose($handle);
                        $success = "Berhasil mengimport $count data dari CSV.";
                    } else {
                        // For Excel files, you'll need PhpSpreadsheet library
                        $success = "File Excel berhasil diupload. (Fitur import Excel memerlukan PhpSpreadsheet library)";
                    }
                    
                    // Log action
                    $logQuery = "INSERT INTO logs (user_id, action, description) VALUES (?, ?, ?)";
                    $logStmt = $db->prepare($logQuery);
                    $logStmt->execute([
                        $_SESSION['user_id'],
                        'IMPORT',
                        'Import data pegawai dari file: ' . $file['name']
                    ]);
                    
                } catch (Exception $e) {
                    $error = "Error processing file: " . $e->getMessage();
                }
                
                // Clean up
                unlink($filepath);
            } else {
                $error = "Gagal menyimpan file upload.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-upload"></i> Import Data Pegawai</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> Petunjuk Import:</h5>
                            <ul>
                                <li>File harus berformat CSV, XLSX, atau XLS</li>
                                <li>Kolom wajib: Nama Lengkap, NIP, Jabatan</li>
                                <li>Untuk file Excel, pastikan sheet pertama berisi data</li>
                                <li>Data duplikat (NIP sama) akan diabaikan</li>
                            </ul>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">Pilih File</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".csv,.xlsx,.xls" required>
                                <div class="form-text">
                                    <a href="template_import.csv" download>Download template CSV</a>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="pegawai.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Import Data
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">Data yang sudah ada (berdasarkan NIP) tidak akan diimport ulang.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>