<?php
// laporan.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$status = isset($_GET['status']) ? $_GET['status'] : '';
$jabatan = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';

// Build query
$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND DATE(created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

if (!empty($status)) {
    $query .= " AND status_kepegawaian = ?";
    $params[] = $status;
}

if (!empty($jabatan)) {
    $query .= " AND jabatan LIKE ?";
    $params[] = "%$jabatan%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics for dashboard
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_kepegawaian = 'PNS' THEN 1 ELSE 0 END) as pns,
    SUM(CASE WHEN status_kepegawaian = 'Honorer' THEN 1 ELSE 0 END) as honorer,
    SUM(CASE WHEN jenis_kelamin = 'Pria' THEN 1 ELSE 0 END) as pria,
    SUM(CASE WHEN jenis_kelamin = 'Wanita' THEN 1 ELSE 0 END) as wanita
    FROM pegawai WHERE DATE(created_at) BETWEEN ? AND ?";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute([$start_date, $end_date]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get unique jabatan for filter
$jabatan_query = "SELECT DISTINCT jabatan FROM pegawai WHERE jabatan IS NOT NULL AND jabatan != '' ORDER BY jabatan";
$jabatan_stmt = $db->query($jabatan_query);
$jabatan_list = $jabatan_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card h3 {
            color: #667eea;
            font-weight: bold;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
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
            <a class="nav-link active" href="laporan.php">
                <i class="bi bi-file-earmark-text me-2"></i> Laporan
            </a>
            <div class="mt-5 pt-5"></div>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Laporan Kepegawaian</h2>
            <div class="text-muted">
                <?php echo date('d F Y'); ?>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Laporan</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Kepegawaian</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="PNS" <?php echo $status == 'PNS' ? 'selected' : ''; ?>>PNS</option>
                        <option value="CPNS" <?php echo $status == 'CPNS' ? 'selected' : ''; ?>>CPNS</option>
                        <option value="Honorer" <?php echo $status == 'Honorer' ? 'selected' : ''; ?>>Honorer</option>
                        <option value="Kontrak" <?php echo $status == 'Kontrak' ? 'selected' : ''; ?>>Kontrak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatan">
                        <option value="">Semua Jabatan</option>
                        <?php foreach ($jabatan_list as $jab): ?>
                            <option value="<?php echo $jab; ?>" <?php echo $jabatan == $jab ? 'selected' : ''; ?>>
                                <?php echo $jab; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Terapkan Filter
                    </button>
                    <a href="laporan.php" class="btn btn-secondary">Reset</a>
                    <button type="button" onclick="printReport()" class="btn btn-success">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="text-muted">Total Pegawai</div>
                    <h3><?php echo $stats['total']; ?></h3>
                    <div class="small">Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="text-muted">PNS</div>
                    <h3 style="color: #28a745;"><?php echo $stats['pns']; ?></h3>
                    <div class="small"><?php echo $stats['total'] > 0 ? round(($stats['pns'] / $stats['total']) * 100, 1) : 0; ?>% dari total</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="text-muted">Honorer</div>
                    <h3 style="color: #ffc107;"><?php echo $stats['honorer']; ?></h3>
                    <div class="small"><?php echo $stats['total'] > 0 ? round(($stats['honorer'] / $stats['total']) * 100, 1) : 0; ?>% dari total</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="text-muted">Pria : Wanita</div>
                    <h3><?php echo $stats['pria']; ?> : <?php echo $stats['wanita']; ?></h3>
                    <div class="small">Rasio Gender</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="filter-card">
                    <h5>Distribusi Status Kepegawaian</h5>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="filter-card">
                    <h5>Distribusi Jenis Kelamin</h5>
                    <canvas id="genderChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="filter-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Data Pegawai (<?php echo count($pegawai); ?> data)</h5>
                <div>
                    <span class="badge bg-info">Periode: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Jenis Kelamin</th>
                            <th>Pangkat/Gol</th>
                            <th>Tanggal Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pegawai) > 0): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($pegawai as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo $row['nip']; ?></td>
                                    <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status_kepegawaian'] == 'PNS' ? 'success' : 'warning'; ?>">
                                            <?php echo $row['status_kepegawaian']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['jenis_kelamin']; ?></td>
                                    <td><?php echo $row['pangkat_golongan']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-database-exclamation" style="font-size: 48px;"></i>
                                        <h5 class="mt-3">Tidak ada data ditemukan</h5>
                                        <p>Coba ubah filter pencarian Anda</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (count($pegawai) > 0): ?>
                <div class="text-end mt-3">
                    <button onclick="exportToExcel()" class="btn btn-success">
                        <i class="bi bi-file-excel"></i> Export ke Excel
                    </button>
                    <button onclick="printReport()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print Button -->
    <button onclick="printReport()" class="btn btn-primary print-btn">
        <i class="bi bi-printer"></i> Cetak
    </button>

    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['PNS', 'Honorer', 'CPNS', 'Kontrak'],
                datasets: [{
                    data: [
                        <?php echo $stats['pns']; ?>,
                        <?php echo $stats['honorer']; ?>,
                        0, // CPNS - you need to add this to your query
                        0  // Kontrak - you need to add this to your query
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
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

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    data: [<?php echo $stats['pria']; ?>, <?php echo $stats['wanita']; ?>],
                    backgroundColor: ['#007bff', '#e83e8c']
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

        // Print Report
        function printReport() {
            window.print();
        }

        // Export to Excel
        function exportToExcel() {
            // Create a temporary table with only the data
            const table = document.querySelector('table');
            const html = table.outerHTML;
            
            // Create a blob with the HTML
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            
            // Create a link and trigger download
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Laporan_Pegawai_RSUD_Mimika_' + new Date().toISOString().split('T')[0] + '.xls';
            link.click();
        }

        // Auto-refresh every 5 minutes if filtered
        <?php if (!empty($start_date) || !empty($end_date) || !empty($status) || !empty($jabatan)): ?>
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutes
        <?php endif; ?>
    </script>

    <style media="print">
        @media print {
            .sidebar, .print-btn, button, .filter-card form {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .filter-card, .stat-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            body {
                background: white !important;
            }
            h2 {
                color: black !important;
            }
            .table thead th {
                background-color: #f8f9fa !important;
                color: black !important;
                border: 1px solid #ddd !important;
            }
            .badge {
                border: 1px solid #ddd !important;
                color: black !important;
                background: none !important;
            }
        }
    </style>
</body>
</html>