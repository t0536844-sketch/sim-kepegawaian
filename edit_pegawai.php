<?php
// edit_pegawai.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: pegawai.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

// Get existing data
$query = "SELECT * FROM pegawai WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$pegawai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pegawai) {
    header("Location: pegawai.php");
    exit();
}

// Konfigurasi upload file
$base_upload_dir = 'uploads/';
$allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$max_size = 10 * 1024 * 1024; // 10MB

// Buat folder base uploads jika belum ada
if (!file_exists($base_upload_dir)) {
    mkdir($base_upload_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Sanitize nama untuk folder
        $nama_pegawai = preg_replace('/[^a-zA-Z0-9\s]/', '', $_POST['nama_lengkap']);
        $nama_folder = str_replace(' ', '_', strtoupper($nama_pegawai));
        
        // Array untuk menyimpan nama file yang diupload
        $uploaded_files = [];
        
        // Mapping field ke folder khusus (sama seperti tambah_pegawai.php)
        $field_folders = [
            'link_sk' => 'SK',
            'link_ktp' => 'KTP',
            'link_kartu_keluarga' => 'KARTU_KELUARGA',
            'link_ijazah' => 'IJAZAH',
            'link_str' => 'STR',
            'link_sip' => 'SIP',
            'link_npwp' => 'NPWP',
            'link_foto' => 'FOTO',
            'link_akta_lahir' => 'AKTA_LAHIR',
            'link_akta_nikah' => 'AKTA_NIKAH',
            'link_skp' => 'SKP',
            'link_sk_kenaikan_pangkat' => 'KENAIKAN_PANGKAT',
            'link_sk_jabatan' => 'SK_JABATAN',
            'link_sk_mutasi' => 'SK_MUTASI',
            'link_sk_pensiun' => 'SK_PENSIUN',
            'link_sertifikat' => 'SERTIFIKAT'
        ];
        
        // Tentukan folder pegawai (dengan ID)
        $pegawai_dir = $base_upload_dir . $nama_folder . '_' . $id . '/';
        
        // Buat folder utama jika belum ada
        if (!file_exists($pegawai_dir)) {
            mkdir($pegawai_dir, 0777, true);
        }
        
        // Proses upload untuk setiap field file
        foreach ($field_folders as $field => $folder_name) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                
                // Validasi tipe file
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_types)) {
                    throw new Exception("Tipe file tidak diperbolehkan untuk {$field}. Hanya boleh: " . implode(', ', $allowed_types));
                }
                
                // Validasi ukuran file
                if ($file['size'] > $max_size) {
                    throw new Exception("Ukuran file terlalu besar untuk {$field}. Maksimal 10MB");
                }
                
                // Buat sub-folder berdasarkan tipe dokumen
                $type_dir = $pegawai_dir . $folder_name . '/';
                if (!file_exists($type_dir)) {
                    mkdir($type_dir, 0777, true);
                }
                
                // Hapus file lama jika ada
                $old_file = $pegawai[$field];
                if (!empty($old_file) && file_exists($old_file)) {
                    unlink($old_file);
                }
                
                // Generate nama file
                $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
                $sanitized_name = preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $original_name);
                $new_filename = $sanitized_name . '_' . date('Ymd_His') . '.' . $file_ext;
                $destination = $type_dir . $new_filename;
                
                // Pindahkan file ke folder
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $uploaded_files[$field] = $destination;
                } else {
                    throw new Exception("Gagal mengupload file untuk {$field}");
                }
            } else {
                // Jika tidak diupload, gunakan file lama
                $uploaded_files[$field] = $pegawai[$field];
            }
        }
        
        // Query untuk update data
        $query = "UPDATE pegawai SET 
            nama_lengkap = ?,
            tempat_lahir = ?,
            tanggal_lahir = ?,
            agama = ?,
            jenis_kelamin = ?,
            nip = ?,
            pangkat_golongan = ?,
            pendidikan = ?,
            status_pernikahan = ?,
            jabatan = ?,
            status_kepegawaian = ?,
            link_sk = ?,
            jumlah_keluarga = ?,
            alamat_rumah = ?,
            link_ktp = ?,
            link_kartu_keluarga = ?,
            link_ijazah = ?,
            link_str = ?,
            masa_berlaku_str = ?,
            link_sip = ?,
            masa_berlaku_sip = ?,
            nomor_kartu_pegawai = ?,
            link_npwp = ?,
            link_foto = ?,
            link_akta_lahir = ?,
            link_akta_nikah = ?,
            link_skp = ?,
            link_sk_kenaikan_pangkat = ?,
            link_sk_jabatan = ?,
            link_sk_mutasi = ?,
            link_sk_pensiun = ?,
            link_sertifikat = ?
        WHERE id = ?";
        
        $stmt = $db->prepare($query);
        
        // Convert empty strings to NULL for dates
        $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $masa_berlaku_str = !empty($_POST['masa_berlaku_str']) ? $_POST['masa_berlaku_str'] : null;
        $masa_berlaku_sip = !empty($_POST['masa_berlaku_sip']) ? $_POST['masa_berlaku_sip'] : null;
        
        $stmt->execute([
            $_POST['nama_lengkap'],
            $_POST['tempat_lahir'],
            $tanggal_lahir,
            $_POST['agama'],
            $_POST['jenis_kelamin'],
            $_POST['nip'],
            $_POST['pangkat_golongan'],
            $_POST['pendidikan'],
            $_POST['status_pernikahan'],
            $_POST['jabatan'],
            $_POST['status_kepegawaian'],
            $uploaded_files['link_sk'],
            $_POST['jumlah_keluarga'],
            $_POST['alamat_rumah'],
            $uploaded_files['link_ktp'],
            $uploaded_files['link_kartu_keluarga'],
            $uploaded_files['link_ijazah'],
            $uploaded_files['link_str'],
            $masa_berlaku_str,
            $uploaded_files['link_sip'],
            $masa_berlaku_sip,
            $_POST['nomor_kartu_pegawai'],
            $uploaded_files['link_npwp'],
            $uploaded_files['link_foto'],
            $uploaded_files['link_akta_lahir'],
            $uploaded_files['link_akta_nikah'],
            $uploaded_files['link_skp'],
            $uploaded_files['link_sk_kenaikan_pangkat'],
            $uploaded_files['link_sk_jabatan'],
            $uploaded_files['link_sk_mutasi'],
            $uploaded_files['link_sk_pensiun'],
            $uploaded_files['link_sertifikat'],
            $id
        ]);
        
        // Jika nama berubah, rename folder
        $old_nama = preg_replace('/[^a-zA-Z0-9\s]/', '', $pegawai['nama_lengkap']);
        $old_nama_folder = str_replace(' ', '_', strtoupper($old_nama));
        $old_pegawai_dir = $base_upload_dir . $old_nama_folder . '_' . $id . '/';
        
        if ($old_nama_folder !== $nama_folder && file_exists($old_pegawai_dir)) {
            // Rename folder
            rename($old_pegawai_dir, $pegawai_dir);
            
            // Update semua path file di database dengan path baru
            foreach ($field_folders as $field => $folder_name) {
                if (!empty($uploaded_files[$field]) && strpos($uploaded_files[$field], $old_pegawai_dir) !== false) {
                    $new_path = str_replace($old_pegawai_dir, $pegawai_dir, $uploaded_files[$field]);
                    
                    // Update path di database
                    $updateQuery = "UPDATE pegawai SET {$field} = ? WHERE id = ?";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->execute([$new_path, $id]);
                }
            }
        }
        
        // Log action
        $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) VALUES (?, ?, ?, ?, ?)";
        $logStmt = $db->prepare($logQuery);
        $logStmt->execute([
            $_SESSION['user_id'],
            'UPDATE',
            'pegawai',
            $id,
            'Mengupdate data pegawai: ' . $_POST['nama_lengkap']
        ]);
        
        $_SESSION['success'] = "Data pegawai berhasil diperbarui!";
        header("Location: detail_pegawai.php?id=" . $id);
        exit();
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pegawai - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-section h5 {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .file-info {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        .current-file {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 5px;
            font-size: 0.9rem;
        }
        .current-file a {
            color: #0d6efd;
            text-decoration: none;
        }
        .current-file a:hover {
            text-decoration: underline;
        }
        .folder-structure {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .folder-structure h6 {
            color: #495057;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4>RSUD MIMIKA</h4>
            <small>Sistem Kepegawaian</small>
        </div>
        <div class="px-3 mb-4">
            <div class="bg-white rounded-pill p-2 text-dark text-center">
                <i class="bi bi-person-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                <span class="badge bg-primary ms-2"><?php echo $_SESSION['role']; ?></span>
            </div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link" href="pegawai.php">
                <i class="bi bi-people me-2"></i> Data Pegawai
            </a>
            <a class="nav-link" href="tambah_pegawai.php">
                <i class="bi bi-person-plus me-2"></i> Tambah Pegawai
            </a>
            <div class="mt-5 pt-5"></div>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Data Pegawai</h2>
            <div>
                <a href="detail_pegawai.php?id=<?php echo $id; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i> Lihat Detail
                </a>
                <a href="pegawai.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Info Struktur Folder -->
        <div class="folder-structure">
            <h6><i class="bi bi-folder-fill"></i> Struktur Penyimpanan File</h6>
            <p class="mb-2">File disimpan dalam folder: <strong>uploads/<?php echo str_replace(' ', '_', strtoupper($pegawai['nama_lengkap'])) . '_' . $id; ?>/</strong></p>
            <p class="mb-0">Upload file baru akan menggantikan file lama dengan nama yang sama.</p>
        </div>

        <!-- PERUBAHAN: Tambah enctype="multipart/form-data" -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Data Pribadi -->
            <div class="form-section">
                <h5><i class="bi bi-person me-2"></i> Data Pribadi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" 
                               value="<?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>" required
                               oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" name="tempat_lahir"
                               value="<?php echo htmlspecialchars($pegawai['tempat_lahir']); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir"
                               value="<?php echo $pegawai['tanggal_lahir']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Agama</label>
                        <select class="form-select" name="agama">
                            <option value="">Pilih Agama</option>
                            <option value="Islam" <?php echo $pegawai['agama'] == 'Islam' ? 'selected' : ''; ?>>Islam</option>
                            <option value="Kristen" <?php echo $pegawai['agama'] == 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                            <option value="Katolik" <?php echo $pegawai['agama'] == 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                            <option value="Hindu" <?php echo $pegawai['agama'] == 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                            <option value="Buddha" <?php echo $pegawai['agama'] == 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                            <option value="Konghucu" <?php echo $pegawai['agama'] == 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin">
                            <option value="Pria" <?php echo $pegawai['jenis_kelamin'] == 'Pria' ? 'selected' : ''; ?>>Pria</option>
                            <option value="Wanita" <?php echo $pegawai['jenis_kelamin'] == 'Wanita' ? 'selected' : ''; ?>>Wanita</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Pernikahan</label>
                        <select class="form-select" name="status_pernikahan">
                            <option value="Menikah" <?php echo $pegawai['status_pernikahan'] == 'Menikah' ? 'selected' : ''; ?>>Menikah</option>
                            <option value="Belum Menikah" <?php echo $pegawai['status_pernikahan'] == 'Belum Menikah' ? 'selected' : ''; ?>>Belum Menikah</option>
                            <option value="Cerai" <?php echo $pegawai['status_pernikahan'] == 'Cerai' ? 'selected' : ''; ?>>Cerai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah Keluarga</label>
                        <input type="number" class="form-control" name="jumlah_keluarga" 
                               value="<?php echo $pegawai['jumlah_keluarga']; ?>" min="0">
                    </div>
                </div>
            </div>

            <!-- Data Kepegawaian -->
            <div class="form-section">
                <h5><i class="bi bi-briefcase me-2"></i> Data Kepegawaian</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" name="nip"
                               value="<?php echo $pegawai['nip']; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pangkat/Golongan</label>
                        <select class="form-select" name="pangkat_golongan">
                            <option value="">Pilih Pangkat</option>
                            <?php
                            $pangkat_options = ['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 
                                               'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e'];
                            foreach ($pangkat_options as $pangkat) {
                                $selected = $pegawai['pangkat_golongan'] == $pangkat ? 'selected' : '';
                                echo "<option value='$pangkat' $selected>$pangkat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Kepegawaian</label>
                        <select class="form-select" name="status_kepegawaian">
                            <option value="PNS" <?php echo $pegawai['status_kepegawaian'] == 'PNS' ? 'selected' : ''; ?>>PNS</option>
                            <option value="CPNS" <?php echo $pegawai['status_kepegawaian'] == 'CPNS' ? 'selected' : ''; ?>>CPNS</option>
                            <option value="Honorer" <?php echo $pegawai['status_kepegawaian'] == 'Honorer' ? 'selected' : ''; ?>>Honorer</option>
                            <option value="Kontrak" <?php echo $pegawai['status_kepegawaian'] == 'Kontrak' ? 'selected' : ''; ?>>Kontrak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pendidikan</label>
                        <input type="text" class="form-control" name="pendidikan"
                               value="<?php echo htmlspecialchars($pegawai['pendidikan']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control" name="jabatan"
                               value="<?php echo htmlspecialchars($pegawai['jabatan']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Kartu Pegawai</label>
                        <input type="text" class="form-control" name="nomor_kartu_pegawai"
                               value="<?php echo $pegawai['nomor_kartu_pegawai']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat Rumah</label>
                        <textarea class="form-control" name="alamat_rumah" rows="2"><?php echo htmlspecialchars($pegawai['alamat_rumah']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Dokumen dan Links -->
            <div class="form-section">
                <h5><i class="bi bi-file-earmark me-2"></i> Dokumen dan Files</h5>
                <div class="row g-3">
                    <!-- SK -->
                    <div class="col-md-6">
                        <label class="form-label">SK (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sk'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sk']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sk']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- KTP -->
                    <div class="col-md-6">
                        <label class="form-label">KTP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_ktp" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_ktp'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_ktp']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_ktp']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Kartu Keluarga -->
                    <div class="col-md-6">
                        <label class="form-label">Kartu Keluarga (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_kartu_keluarga" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_kartu_keluarga'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_kartu_keluarga']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_kartu_keluarga']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Ijazah -->
                    <div class="col-md-6">
                        <label class="form-label">Ijazah (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_ijazah" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_ijazah'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_ijazah']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_ijazah']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- STR -->
                    <div class="col-md-6">
                        <label class="form-label">STR (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_str" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_str'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_str']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_str']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Masa Berlaku STR -->
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku STR</label>
                        <input type="date" class="form-control" name="masa_berlaku_str"
                               value="<?php echo $pegawai['masa_berlaku_str']; ?>">
                    </div>
                    
                    <!-- SIP -->
                    <div class="col-md-6">
                        <label class="form-label">SIP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sip" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sip'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sip']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sip']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Masa Berlaku SIP -->
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku SIP</label>
                        <input type="date" class="form-control" name="masa_berlaku_sip"
                               value="<?php echo $pegawai['masa_berlaku_sip']; ?>">
                    </div>
                    
                    <!-- NPWP -->
                    <div class="col-md-6">
                        <label class="form-label">NPWP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_npwp" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_npwp'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_npwp']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_npwp']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Foto -->
                    <div class="col-md-6">
                        <label class="form-label">Pas Foto (Image)</label>
                        <input type="file" class="form-control" name="link_foto" accept=".jpg,.jpeg,.png">
                        <div class="file-info">Maksimal 10MB. Format: JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_foto'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_foto']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_foto']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Akta Lahir -->
                    <div class="col-md-6">
                        <label class="form-label">Akta Lahir (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_akta_lahir" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_akta_lahir'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_akta_lahir']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_akta_lahir']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Akta Nikah -->
                    <div class="col-md-6">
                        <label class="form-label">Akta Nikah (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_akta_nikah" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_akta_nikah'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_akta_nikah']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_akta_nikah']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- SKP -->
                    <div class="col-md-6">
                        <label class="form-label">SKP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_skp" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_skp'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_skp']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_skp']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- SK Kenaikan Pangkat -->
                    <div class="col-md-6">
                        <label class="form-label">SK Kenaikan Pangkat (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_kenaikan_pangkat" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sk_kenaikan_pangkat'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sk_kenaikan_pangkat']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sk_kenaikan_pangkat']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- SK Jabatan -->
                    <div class="col-md-6">
                        <label class="form-label">SK Jabatan (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_jabatan" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sk_jabatan'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sk_jabatan']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sk_jabatan']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- SK Mutasi -->
                    <div class="col-md-6">
                        <label class="form-label">SK Mutasi (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_mutasi" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sk_mutasi'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sk_mutasi']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sk_mutasi']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- SK Pensiun -->
                    <div class="col-md-6">
                        <label class="form-label">SK Pensiun (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_pensiun" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sk_pensiun'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sk_pensiun']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sk_pensiun']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sertifikat -->
                    <div class="col-md-6">
                        <label class="form-label">Sertifikat (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sertifikat" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG. Kosongkan jika tidak diubah.</div>
                        <?php if (!empty($pegawai['link_sertifikat'])): ?>
                            <div class="current-file">
                                <i class="bi bi-file-earmark"></i> File saat ini: 
                                <a href="<?php echo $pegawai['link_sertifikat']; ?>" target="_blank">
                                    <?php echo basename($pegawai['link_sertifikat']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a href="detail_pegawai.php?id=<?php echo $id; ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-uppercase untuk nama lengkap
        document.querySelector('input[name="nama_lengkap"]').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
        
        // Preview untuk file yang dipilih
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    const fileName = this.files[0].name;
                    const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                    const infoDiv = this.nextElementSibling;
                    
                    // Update info
                    const currentInfo = infoDiv.textContent;
                    const newInfo = currentInfo.replace('Kosongkan jika tidak diubah.', 
                        `File terpilih: ${fileName} (${fileSize} MB)`);
                    infoDiv.textContent = newInfo;
                }
            });
        });
    </script>
</body>
</html>