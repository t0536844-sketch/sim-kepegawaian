<?php
// tambah_pegawai.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
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
            $_POST['link_sk'],
            $_POST['jumlah_keluarga'],
            $_POST['alamat_rumah'],
            $_POST['link_ktp'],
            $_POST['link_kartu_keluarga'],
            $_POST['link_ijazah'],
            $_POST['link_str'],
            $_POST['masa_berlaku_str'],
            $_POST['link_sip'],
            $_POST['masa_berlaku_sip'],
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
            $_POST['link_sertifikat']
        ]);
        
        // Log action
        $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) VALUES (?, ?, ?, ?, ?)";
        $logStmt = $db->prepare($logQuery);
        $logStmt->execute([
            $_SESSION['user_id'],
            'CREATE',
            'pegawai',
            $db->lastInsertId(),
            'Menambah data pegawai baru: ' . $_POST['nama_lengkap']
        ]);
        
        header("Location: pegawai.php?success=1");
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

        <form method="POST">
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
                <h5>Dokumen dan Links</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Link KTP</label>
                        <input file="file" class="form-control" name="link_ktp" accept=".pdf">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Kartu Keluarga</label>
                        <input type="url" class="form-control" name="link_kartu_keluarga" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Ijazah</label>
                        <input type="url" class="form-control" name="link_ijazah" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link STR</label>
                        <input type="url" class="form-control" name="link_str" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku STR</label>
                        <input type="date" class="form-control" name="masa_berlaku_str">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SIP</label>
                        <input type="url" class="form-control" name="link_sip" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Masa Berlaku SIP</label>
                        <input type="date" class="form-control" name="masa_berlaku_sip">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link NPWP</label>
                        <input type="url" class="form-control" name="link_npwp" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Pas Foto</label>
                        <input type="url" class="form-control" name="link_foto" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Akta Lahir</label>
                        <input type="url" class="form-control" name="link_akta_lahir" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Akta Nikah</label>
                        <input type="url" class="form-control" name="link_akta_nikah" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SK</label>
                        <input type="url" class="form-control" name="link_sk" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SKP</label>
                        <input type="url" class="form-control" name="link_skp" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SK Kenaikan Pangkat</label>
                        <input type="url" class="form-control" name="link_sk_kenaikan_pangkat" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SK Jabatan</label>
                        <input type="url" class="form-control" name="link_sk_jabatan" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SK Mutasi</label>
                        <input type="url" class="form-control" name="link_sk_mutasi" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link SK Pensiun</label>
                        <input type="url" class="form-control" name="link_sk_pensiun" placeholder="https://">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Sertifikat</label>
                        <input type="url" class="form-control" name="link_sertifikat" placeholder="https://">
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