<?php
// detail_pegawai.php
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
$query = "SELECT * FROM pegawai WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$pegawai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pegawai) {
    header("Location: pegawai.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pegawai - Sistem Kepegawaian RSUD Mimika</title>
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
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-card h5 {
            color: #667eea;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .document-badge {
            display: inline-block;
            padding: 5px 10px;
            margin: 5px;
            background: #e3f2fd;
            border-radius: 5px;
            color: #1976d2;
        }
        .document-badge a {
            color: #1976d2;
            text-decoration: none;
        }
        .document-badge a:hover {
            text-decoration: underline;
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
            <div>
                <h2>Detail Pegawai</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="pegawai.php">Data Pegawai</a></li>
                        <li class="breadcrumb-item active">Detail Pegawai</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="pegawai.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <a href="edit_pegawai.php?id=<?php echo $pegawai['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
                <button onclick="window.print()" class="btn btn-success">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px; margin: 0 auto;">
                        <?php if (!empty($pegawai['link_foto'])): ?>
                            <img src="<?php echo $pegawai['link_foto']; ?>" 
                                 alt="Foto" 
                                 class="rounded-circle"
                                 style="width: 90px; height: 90px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-person" style="font-size: 48px; color: #667eea;"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-10">
                    <h3><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></h3>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>NIP:</strong> <?php echo $pegawai['nip']; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Jabatan:</strong> <?php echo htmlspecialchars($pegawai['jabatan']); ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Status:</strong> 
                            <span class="badge bg-<?php echo $pegawai['status_kepegawaian'] == 'PNS' ? 'success' : 'warning'; ?>">
                                <?php echo $pegawai['status_kepegawaian']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Kolom Kiri: Data Pribadi -->
            <div class="col-md-6">
                <div class="info-card">
                    <h5><i class="bi bi-person me-2"></i> Data Pribadi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Tempat Lahir</label>
                            <p class="fw-bold"><?php echo $pegawai['tempat_lahir']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Tanggal Lahir</label>
                            <p class="fw-bold"><?php echo date('d F Y', strtotime($pegawai['tanggal_lahir'])); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Agama</label>
                            <p class="fw-bold"><?php echo $pegawai['agama']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Jenis Kelamin</label>
                            <p class="fw-bold"><?php echo $pegawai['jenis_kelamin']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Status Pernikahan</label>
                            <p class="fw-bold"><?php echo $pegawai['status_pernikahan']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Jumlah Keluarga</label>
                            <p class="fw-bold"><?php echo $pegawai['jumlah_keluarga']; ?> orang</p>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-geo-alt me-2"></i> Alamat & Kontak</h5>
                    <div class="mb-3">
                        <label class="form-label text-muted">Alamat Rumah</label>
                        <p class="fw-bold"><?php echo nl2br(htmlspecialchars($pegawai['alamat_rumah'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Data Kepegawaian -->
            <div class="col-md-6">
                <div class="info-card">
                    <h5><i class="bi bi-briefcase me-2"></i> Data Kepegawaian</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Pangkat/Golongan</label>
                            <p class="fw-bold"><?php echo $pegawai['pangkat_golongan']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Pendidikan</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($pegawai['pendidikan']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Nomor Kartu Pegawai</label>
                            <p class="fw-bold"><?php echo $pegawai['nomor_kartu_pegawai']; ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Tanggal Masuk</label>
                            <p class="fw-bold"><?php echo date('d F Y', strtotime($pegawai['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-calendar me-2"></i> Masa Berlaku Dokumen</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Masa Berlaku STR</label>
                            <p class="fw-bold <?php 
                                if (!empty($pegawai['masa_berlaku_str']) && strtotime($pegawai['masa_berlaku_str']) < strtotime('+30 days')) {
                                    echo 'text-danger';
                                } else {
                                    echo 'text-success';
                                }
                            ?>">
                                <?php echo !empty($pegawai['masa_berlaku_str']) ? date('d F Y', strtotime($pegawai['masa_berlaku_str'])) : '-'; ?>
                                <?php if (!empty($pegawai['masa_berlaku_str']) && strtotime($pegawai['masa_berlaku_str']) < strtotime('+30 days')): ?>
                                    <span class="badge bg-danger">Akan Habis</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Masa Berlaku SIP</label>
                            <p class="fw-bold <?php 
                                if (!empty($pegawai['masa_berlaku_sip']) && strtotime($pegawai['masa_berlaku_sip']) < strtotime('+30 days')) {
                                    echo 'text-danger';
                                } else {
                                    echo 'text-success';
                                }
                            ?>">
                                <?php echo !empty($pegawai['masa_berlaku_sip']) ? date('d F Y', strtotime($pegawai['masa_berlaku_sip'])) : '-'; ?>
                                <?php if (!empty($pegawai['masa_berlaku_sip']) && strtotime($pegawai['masa_berlaku_sip']) < strtotime('+30 days')): ?>
                                    <span class="badge bg-danger">Akan Habis</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumen -->
        <div class="info-card">
            <h5><i class="bi bi-folder me-2"></i> Dokumen</h5>
            <div class="row">
                <?php
                $documents = [
                    'KTP' => $pegawai['link_ktp'],
                    'Kartu Keluarga' => $pegawai['link_kartu_keluarga'],
                    'Ijazah' => $pegawai['link_ijazah'],
                    'STR' => $pegawai['link_str'],
                    'SIP' => $pegawai['link_sip'],
                    'NPWP' => $pegawai['link_npwp'],
                    'Akta Lahir' => $pegawai['link_akta_lahir'],
                    'Akta Nikah' => $pegawai['link_akta_nikah'],
                    'SK' => $pegawai['link_sk'],
                    'SKP' => $pegawai['link_skp'],
                    'SK Kenaikan Pangkat' => $pegawai['link_sk_kenaikan_pangkat'],
                    'SK Jabatan' => $pegawai['link_sk_jabatan'],
                    'SK Mutasi' => $pegawai['link_sk_mutasi'],
                    'SK Pensiun' => $pegawai['link_sk_pensiun'],
                    'Sertifikat' => $pegawai['link_sertifikat']
                ];
                ?>
                <?php foreach ($documents as $name => $link): ?>
                    <?php if (!empty($link)): ?>
                        <div class="col-md-3 mb-3">
                            <div class="document-badge">
                                <i class="bi bi-file-earmark me-1"></i>
                                <a href="<?php echo $link; ?>" target="_blank"><?php echo $name; ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if (empty(array_filter($documents))): ?>
                    <div class="col-12 text-center text-muted py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px;"></i>
                        <p class="mt-3">Belum ada dokumen yang diunggah</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline Activity -->
        <div class="info-card">
            <h5><i class="bi bi-clock-history me-2"></i> Riwayat</h5>
            <ul class="timeline">
                <li class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <h6>Data Dibuat</h6>
                        <p class="text-muted"><?php echo date('d F Y H:i', strtotime($pegawai['created_at'])); ?></p>
                    </div>
                </li>
                <?php if ($pegawai['updated_at'] != $pegawai['created_at']): ?>
                <li class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <h6>Terakhir Diperbarui</h6>
                        <p class="text-muted"><?php echo date('d F Y H:i', strtotime($pegawai['updated_at'])); ?></p>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
            <style>
                .timeline {
                    list-style: none;
                    padding-left: 0;
                }
                .timeline-item {
                    position: relative;
                    padding-left: 30px;
                    margin-bottom: 20px;
                }
                .timeline-marker {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 12px;
                    height: 12px;
                    background: #667eea;
                    border-radius: 50%;
                }
                .timeline-content h6 {
                    margin-bottom: 5px;
                    font-weight: bold;
                }
            </style>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>