<?php
// tambah_pegawai.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Konfigurasi upload file
$upload_dir = 'uploads/';
$allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$max_size = 10 * 1024 * 1024; // 10MB

// Buat folder uploads jika belum ada
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Array untuk menyimpan nama file yang diupload
        $uploaded_files = [];
        
        // Array field yang merupakan file upload
        $file_fields = [
            'link_sk', 'link_ktp', 'link_kartu_keluarga', 'link_ijazah',
            'link_str', 'link_sip', 'link_npwp', 'link_foto',
            'link_akta_lahir', 'link_akta_nikah', 'link_skp',
            'link_sk_kenaikan_pangkat', 'link_sk_jabatan', 'link_sk_mutasi',
            'link_sk_pensiun', 'link_sertifikat'
        ];
        
        // Proses upload untuk setiap field file
        foreach ($file_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                
                // Validasi tipe file
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_types)) {
                    throw new Exception("Tipe file tidak diperbolehkan untuk field {$field}. Hanya boleh: " . implode(', ', $allowed_types));
                }
                
                // Validasi ukuran file
                if ($file['size'] > $max_size) {
                    throw new Exception("Ukuran file terlalu besar untuk field {$field}. Maksimal 10MB");
                }
                
                // Generate nama file unik
                $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file['name']);
                $destination = $upload_dir . $new_filename;
                
                // Pindahkan file ke folder uploads
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $uploaded_files[$field] = $destination;
                } else {
                    throw new Exception("Gagal mengupload file untuk field {$field}");
                }
            } else {
                // Jika tidak diupload, set sebagai string kosong
                $uploaded_files[$field] = '';
            }
        }
        
        // Query untuk insert data
        $query = "INSERT INTO pegawai (
            nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
            pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian,
            link_sk, jumlah_keluarga, alamat_rumah, link_ktp, link_kartu_keluarga,
            link_ijazah, link_str, masa_berlaku_str, link_sip, masa_berlaku_sip,
            nomor_kartu_pegawai, link_npwp, link_foto, link_akta_lahir, link_akta_nikah,
            link_skp, link_sk_kenaikan_pangkat, link_sk_jabatan, link_sk_mutasi,
            link_sk_pensiun, link_sertifikat
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        
        // Eksekusi dengan data dari form dan file yang diupload
        $stmt->execute([
            $_POST['nama_lengkap'],
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
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
            $_POST['masa_berlaku_str'],
            $uploaded_files['link_sip'],
            $_POST['masa_berlaku_sip'],
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
            $uploaded_files['link_sertifikat']
        ]);
        
        $last_id = $db->lastInsertId();
        
        // Log action
        $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) VALUES (?, ?, ?, ?, ?)";
        $logStmt = $db->prepare($logQuery);
        $logStmt->execute([
            $_SESSION['user_id'],
            'CREATE',
            'pegawai',
            $last_id,
            'Menambah data pegawai baru: ' . $_POST['nama_lengkap']
        ]);
        
        header("Location: pegawai.php?success=1");
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
    <title>Tambah Pegawai - Sistem Kepegawaian RSUD Mimika</title>
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
        .file-info {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Include same sidebar as dashboard -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tambah Data Pegawai Baru</h2>
            <a href="pegawai.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- PERUBAHAN PENTING: Tambah enctype="multipart/form-data" -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Data Pribadi -->
            <div class="form-section">
                <h5>Data Pribadi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" name="tempat_lahir">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Agama</label>
                        <select class="form-select" name="agama">
                            <option value="">Pilih Agama</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin">
                            <option value="Pria">Pria</option>
                            <option value="Wanita">Wanita</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Pernikahan</label>
                        <select class="form-select" name="status_pernikahan">
                            <option value="Menikah">Menikah</option>
                            <option value="Belum Menikah">Belum Menikah</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah Keluarga</label>
                        <input type="number" class="form-control" name="jumlah_keluarga" min="0">
                    </div>
                </div>
            </div>

            <!-- Data Kepegawaian -->
            <div class="form-section">
                <h5>Data Kepegawaian</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" name="nip">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pangkat/Golongan</label>
                        <select class="form-select" name="pangkat_golongan">
                            <option value="">Pilih Pangkat</option>
                            <option value="I/a">I/a</option>
                            <option value="I/b">I/b</option>
                            <option value="I/c">I/c</option>
                            <option value="I/d">I/d</option>
                            <option value="II/a">II/a</option>
                            <option value="II/b">II/b</option>
                            <option value="II/c">II/c</option>
                            <option value="II/d">II/d</option>
                            <option value="III/a">III/a</option>
                            <option value="III/b">III/b</option>
                            <option value="III/c">III/c</option>
                            <option value="III/d">III/d</option>
                            <option value="IV/a">IV/a</option>
                            <option value="IV/b">IV/b</option>
                            <option value="IV/c">IV/c</option>
                            <option value="IV/d">IV/d</option>
                            <option value="IV/e">IV/e</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Kepegawaian</label>
                        <select class="form-select" name="status_kepegawaian">
                            <option value="PNS">PNS</option>
                            <option value="CPNS">CPNS</option>
                            <option value="Honorer">Honorer</option>
                            <option value="Kontrak">Kontrak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pendidikan</label>
                        <input type="text" class="form-control" name="pendidikan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control" name="jabatan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Kartu Pegawai</label>
                        <input type="text" class="form-control" name="nomor_kartu_pegawai">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat Rumah</label>
                        <textarea class="form-control" name="alamat_rumah" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Dokumen dan Links -->
            <div class="form-section">
                <h5>Dokumen dan Files</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SK (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">KTP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_ktp" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kartu Keluarga (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_kartu_keluarga" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ijazah (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_ijazah" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">STR (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_str" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku STR</label>
                        <input type="date" class="form-control" name="masa_berlaku_str">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SIP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sip" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku SIP</label>
                        <input type="date" class="form-control" name="masa_berlaku_sip">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NPWP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_npwp" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pas Foto (Image)</label>
                        <input type="file" class="form-control" name="link_foto" accept=".jpg,.jpeg,.png">
                        <div class="file-info">Maksimal 10MB. Format: JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Akta Lahir (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_akta_lahir" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Akta Nikah (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_akta_nikah" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKP (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_skp" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SK Kenaikan Pangkat (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_kenaikan_pangkat" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SK Jabatan (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_jabatan" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SK Mutasi (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_mutasi" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SK Pensiun (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sk_pensiun" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sertifikat (PDF/DOC/Image)</label>
                        <input type="file" class="form-control" name="link_sertifikat" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="file-info">Maksimal 10MB. Format: PDF, DOC, JPG, PNG</div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="reset" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</body>
</html>