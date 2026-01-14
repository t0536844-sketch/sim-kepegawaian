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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
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
            $_POST['link_sk'],
            $_POST['jumlah_keluarga'],
            $_POST['alamat_rumah'],
            $_POST['link_ktp'],
            $_POST['link_kartu_keluarga'],
            $_POST['link_ijazah'],
            $_POST['link_str'],
            $masa_berlaku_str,
            $_POST['link_sip'],
            $masa_berlaku_sip,
            $_POST['nomor_kartu_pegawai'],
            $_POST['link_npwp'],
            $_POST['link_foto'],
            $_POST['link_akta_lahir'],
            $_POST['link_akta_nikah'],
            $_POST['link_skp'],
            $_POST['link_sk_kenaikan_pangkat'],
            $_POST['link_sk_jabatan'],
            $_POST['link_sk_mutasi'],
            $_POST['link_sk_pensiun'],
            $_POST['link_sertifikat'],
            $id
        ]);
        
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
        
    } catch (PDOException $e) {
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

        <form method="POST">
            <!-- Data Pribadi -->
            <div class="form-section">
                <h5><i class="bi bi-person me-2"></i> Data Pribadi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" 
                               value="<?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>" required>
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
                <h5><i class="bi bi-file-earmark me-2"></i> Dokumen dan Links</h5>
                <div class="row g-3">
                    <?php
                    $document_fields = [
                        ['label' => 'Link KTP', 'field' => 'link_ktp'],
                        ['label' => 'Link Kartu Keluarga', 'field' => 'link_kartu_keluarga'],
                        ['label' => 'Link Ijazah', 'field' => 'link_ijazah'],
                        ['label' => 'Link STR', 'field' => 'link_str'],
                        ['label' => 'Link SIP', 'field' => 'link_sip'],
                        ['label' => 'Link NPWP', 'field' => 'link_npwp'],
                        ['label' => 'Link Pas Foto', 'field' => 'link_foto'],
                        ['label' => 'Link Akta Lahir', 'field' => 'link_akta_lahir'],
                        ['label' => 'Link Akta Nikah', 'field' => 'link_akta_nikah'],
                        ['label' => 'Link SK', 'field' => 'link_sk'],
                        ['label' => 'Link SKP', 'field' => 'link_skp'],
                        ['label' => 'Link SK Kenaikan Pangkat', 'field' => 'link_sk_kenaikan_pangkat'],
                        ['label' => 'Link SK Jabatan', 'field' => 'link_sk_jabatan'],
                        ['label' => 'Link SK Mutasi', 'field' => 'link_sk_mutasi'],
                        ['label' => 'Link SK Pensiun', 'field' => 'link_sk_pensiun'],
                        ['label' => 'Link Sertifikat', 'field' => 'link_sertifikat']
                    ];
                    
                    foreach ($document_fields as $index => $doc) {
                        if ($index % 2 == 0) {
                            echo '<div class="row">';
                        }
                        echo '<div class="col-md-6">';
                        echo '<label class="form-label">' . $doc['label'] . '</label>';
                        echo '<input type="url" class="form-control" name="' . $doc['field'] . '" 
                               value="' . htmlspecialchars($pegawai[$doc['field']]) . '" placeholder="https://">';
                        echo '</div>';
                        if ($index % 2 == 1 || $index == count($document_fields) - 1) {
                            echo '</div>';
                        }
                    }
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Masa Berlaku STR</label>
                            <input type="date" class="form-control" name="masa_berlaku_str"
                                   value="<?php echo $pegawai['masa_berlaku_str']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Masa Berlaku SIP</label>
                            <input type="date" class="form-control" name="masa_berlaku_sip"
                                   value="<?php echo $pegawai['masa_berlaku_sip']; ?>">
                        </div>
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
</body>
</html>