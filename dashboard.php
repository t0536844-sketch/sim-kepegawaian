<?php
// dashboard.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];
$queries = [
    'total' => "SELECT COUNT(*) as count FROM pegawai",
    'pns' => "SELECT COUNT(*) as count FROM pegawai WHERE status_kepegawaian = 'PNS'",
    'honorer' => "SELECT COUNT(*) as count FROM pegawai WHERE status_kepegawaian = 'Honorer'",
    'aktif' => "SELECT COUNT(*) as count FROM pegawai WHERE link_sk_pensiun IS NULL OR link_sk_pensiun = ''",
    'pensiun' => "SELECT COUNT(*) as count FROM pegawai WHERE link_sk_pensiun IS NOT NULL AND link_sk_pensiun != ''",
    'pria' => "SELECT COUNT(*) as count FROM pegawai WHERE jenis_kelamin = 'Pria'",
    'wanita' => "SELECT COUNT(*) as count FROM pegawai WHERE jenis_kelamin = 'Wanita'"
];

foreach ($queries as $key => $query) {
    $stmt = $db->query($query);
    $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Get recent employees
$recentQuery = "SELECT * FROM pegawai ORDER BY created_at DESC LIMIT 5";
$recentStmt = $db->query($recentQuery);
$recentEmployees = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
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
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.total { background: linear-gradient(135deg, var(--primary), var(--secondary)); }
        .stat-card.pns { background: linear-gradient(135deg, var(--success), #059669); }
        .stat-card.honorer { background: linear-gradient(135deg, var(--info), #1d4ed8); }
        .stat-card.aktif { background: linear-gradient(135deg, var(--warning), #d97706); }
        .stat-card.pensiun { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-card.pria { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-card.wanita { background: linear-gradient(135deg, #ec4899, #db2777); }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4>RSUD MIMIKA</h4>
            <small>Sistem Kepegawaian</small>
        </div>
        <div class="px-3 mb-4">
            <div class="bg-white rounded-pill p-2 text-dark text-center">
                <i class="bi bi-person-circle me-2"></i>
                <?php echo $_SESSION['nama_lengkap']; ?>
                <span class="badge bg-primary ms-2"><?php echo $_SESSION['role']; ?></span>
            </div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link" href="pegawai.php">
                <i class="bi bi-people me-2"></i> Data Pegawai
            </a>
            <a class="nav-link" href="tambah_pegawai.php">
                <i class="bi bi-person-plus me-2"></i> Tambah Pegawai
            </a>
            <a class="nav-link" href="import.php">
                <i class="bi bi-upload me-2"></i> Import Data
            </a>
            <a class="nav-link" href="export.php">
                <i class="bi bi-download me-2"></i> Export Data
            </a>
            <a class="nav-link" href="laporan.php">
                <i class="bi bi-file-earmark-text me-2"></i> Laporan
            </a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a class="nav-link" href="users.php">
                    <i class="bi bi-shield-lock me-2"></i> Manajemen User
                </a>
            <?php endif; ?>
            <div class="mt-5 pt-5"></div>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard Kepegawaian</h2>
            <div class="text-muted">
                <?php echo date('d F Y'); ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card total">
                    <h5>Total Pegawai</h5>
                    <h2><?php echo $stats['total']; ?></h2>
                    <div><i class="bi bi-people-fill"></i> Seluruh Pegawai</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card pns">
                    <h5>PNS</h5>
                    <h2><?php echo $stats['pns']; ?></h2>
                    <div><i class="bi bi-person-check"></i> Pegawai Negeri Sipil</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card honorer">
                    <h5>Honorer</h5>
                    <h2><?php echo $stats['honorer']; ?></h2>
                    <div><i class="bi bi-person"></i> Pegawai Honorer</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card aktif">
                    <h5>Aktif</h5>
                    <h2><?php echo $stats['aktif']; ?></h2>
                    <div><i class="bi bi-check-circle"></i> Sedang Bertugas</div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Distribusi Jenis Kelamin</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="genderChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Status Kepegawaian</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Pegawai Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>NIP</th>
                                        <th>Jabatan</th>
                                        <th>Status</th>
                                        <th>Tanggal Input</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentEmployees as $employee): ?>
                                    <tr>
                                        <td><?php echo $employee['nama_lengkap']; ?></td>
                                        <td><?php echo $employee['nip']; ?></td>
                                        <td><?php echo $employee['jabatan']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $employee['status_kepegawaian'] == 'PNS' ? 'success' : 'warning'; ?>">
                                                <?php echo $employee['status_kepegawaian']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($employee['created_at'])); ?></td>
                                        <td>
                                            <a href="detail_pegawai.php?id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    data: [<?php echo $stats['pria']; ?>, <?php echo $stats['wanita']; ?>],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Aktif', 'Pensiun'],
                datasets: [{
                    data: [<?php echo $stats['aktif']; ?>, <?php echo $stats['pensiun']; ?>],
                    backgroundColor: ['#10b981', '#8b5cf6'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>