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
$agama = isset($_GET['agama']) ? $_GET['agama'] : '';

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

if (!empty($agama)) {
    $query .= " AND agama = ?";
    $params[] = $agama;
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
    SUM(CASE WHEN status_kepegawaian = 'CPNS' THEN 1 ELSE 0 END) as cpns,
    SUM(CASE WHEN status_kepegawaian = 'Kontrak' THEN 1 ELSE 0 END) as kontrak,
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

// Get unique agama for filter
$agama_query = "SELECT DISTINCT agama FROM pegawai WHERE agama IS NOT NULL AND agama != '' ORDER BY agama";
$agama_stmt = $db->query($agama_query);
$agama_list = $agama_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
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
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-expired {
            background-color: #dc3545;
            color: white;
            font-size: 0.7em;
            padding: 2px 6px;
        }
        .badge-warning {
            background-color: #ffc107;
            color: black;
            font-size: 0.7em;
            padding: 2px 6px;
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
                            <option value="<?php echo htmlspecialchars($jab); ?>" <?php echo $jabatan == $jab ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jab); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Agama</label>
                    <select class="form-select" name="agama">
                        <option value="">Semua Agama</option>
                        <?php foreach ($agama_list as $agm): ?>
                            <option value="<?php echo htmlspecialchars($agm); ?>" <?php echo $agama == $agm ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agm); ?>
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
                <table class="table table-hover table-striped" id="dataTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Tempat/Tgl Lahir</th>
                            <th>Agama</th>
                            <th>Jenis Kelamin</th>
                            <th>NIP</th>
                            <th>Pangkat/Golongan</th>
                            <th>Pendidikan</th>
                            <th>Status</th>
                            <th>Jabatan</th>
                            <th>Status Kepegawaian</th>
                            <th>SK</th>
                            <th>Keluarga</th>
                            <th>Rumah</th>
                            <th>Masa Berlaku STR</th>
                            <th>Masa Berlaku SIP</th>
                            <th>Nomor Kartu Pegawai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pegawai) > 0): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($pegawai as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td>
                                        <?php 
                                        $tempat_lahir = !empty($row['tempat_lahir']) ? $row['tempat_lahir'] : '-';
                                        $tgl_lahir = !empty($row['tanggal_lahir']) ? date('d/m/Y', strtotime($row['tanggal_lahir'])) : '-';
                                        echo htmlspecialchars($tempat_lahir) . '<br>' . $tgl_lahir;
                                        ?>
                                    </td>
                                    <td><?php echo !empty($row['agama']) ? htmlspecialchars($row['agama']) : '-'; ?></td>
                                    <td><?php echo $row['jenis_kelamin']; ?></td>
                                    <td><?php echo $row['nip']; ?></td>
                                    <td><?php echo !empty($row['pangkat_golongan']) ? htmlspecialchars($row['pangkat_golongan']) : '-'; ?></td>
                                    <td><?php echo !empty($row['pendidikan']) ? htmlspecialchars($row['pendidikan']) : '-'; ?></td>
                                    <td><?php echo !empty($row['status_perkawinan']) ? htmlspecialchars($row['status_perkawinan']) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($row['status_kepegawaian']) {
                                                case 'PNS': echo 'success'; break;
                                                case 'CPNS': echo 'info'; break;
                                                case 'Honorer': echo 'warning'; break;
                                                case 'Kontrak': echo 'secondary'; break;
                                                default: echo 'light text-dark';
                                            }
                                        ?>">
                                            <?php echo $row['status_kepegawaian']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo !empty($row['sk_nomor']) ? htmlspecialchars($row['sk_nomor']) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $keluarga = [];
                                        if (!empty($row['nama_pasangan'])) $keluarga[] = 'Pasangan: ' . htmlspecialchars($row['nama_pasangan']);
                                        if (!empty($row['jumlah_anak'])) $keluarga[] = 'Anak: ' . $row['jumlah_anak'];
                                        echo !empty($keluarga) ? implode('<br>', $keluarga) : '-';
                                        ?>
                                    </td>
                                    <td><?php echo !empty($row['alamat_rumah']) ? htmlspecialchars($row['alamat_rumah']) : '-'; ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($row['masa_berlaku_str'])) {
                                            $str_date = date('d/m/Y', strtotime($row['masa_berlaku_str']));
                                            $hari_menuju_kadaluarsa = floor((strtotime($row['masa_berlaku_str']) - time()) / (60 * 60 * 24));
                                            
                                            echo $str_date;
                                            
                                            if ($hari_menuju_kadaluarsa < 30 && $hari_menuju_kadaluarsa > 0) {
                                                echo '<br><span class="badge-warning"><i class="bi bi-exclamation-triangle"></i> ' . $hari_menuju_kadaluarsa . ' hari</span>';
                                            } elseif ($hari_menuju_kadaluarsa <= 0) {
                                                echo '<br><span class="badge-expired"><i class="bi bi-exclamation-circle"></i> Kadaluarsa</span>';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($row['masa_berlaku_sip'])) {
                                            $sip_date = date('d/m/Y', strtotime($row['masa_berlaku_sip']));
                                            $hari_menuju_kadaluarsa = floor((strtotime($row['masa_berlaku_sip']) - time()) / (60 * 60 * 24));
                                            
                                            echo $sip_date;
                                            
                                            if ($hari_menuju_kadaluarsa < 30 && $hari_menuju_kadaluarsa > 0) {
                                                echo '<br><span class="badge-warning"><i class="bi bi-exclamation-triangle"></i> ' . $hari_menuju_kadaluarsa . ' hari</span>';
                                            } elseif ($hari_menuju_kadaluarsa <= 0) {
                                                echo '<br><span class="badge-expired"><i class="bi bi-exclamation-circle"></i> Kadaluarsa</span>';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo !empty($row['nomor_kartu_pegawai']) ? htmlspecialchars($row['nomor_kartu_pegawai']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="17" class="text-center py-4">
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
                    <button onclick="exportToPDF()" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Export ke PDF
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
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['PNS', 'Honorer', 'CPNS', 'Kontrak'],
                datasets: [{
                    data: [
                        <?php echo $stats['pns']; ?>,
                        <?php echo $stats['honorer']; ?>,
                        <?php echo $stats['cpns']; ?>,
                        <?php echo $stats['kontrak']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#6c757d'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $stats['total']; ?>;
                                const value = context.raw;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    data: [<?php echo $stats['pria']; ?>, <?php echo $stats['wanita']; ?>],
                    backgroundColor: ['#007bff', '#e83e8c'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $stats['total']; ?>;
                                const value = context.raw;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Print Report
        function printReport() {
            const originalTitle = document.title;
            document.title = "Laporan Pegawai RSUD Mimika - " + new Date().toLocaleDateString('id-ID');
            
            // Clone the main content
            const printContent = document.querySelector('.main-content').cloneNode(true);
            
            // Remove charts and buttons
            printContent.querySelectorAll('canvas').forEach(canvas => canvas.remove());
            printContent.querySelectorAll('button').forEach(button => button.remove());
            
            // Remove filter form
            const filterForm = printContent.querySelector('form');
            if (filterForm) filterForm.remove();
            
            // Open print window
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${document.title}</title>
                    <style>
                        @page { size: landscape; margin: 0.5cm; }
                        body { font-family: Arial, sans-serif; font-size: 10px; }
                        h2 { color: #333; text-align: center; margin-bottom: 20px; }
                        .table { width: 100%; border-collapse: collapse; font-size: 9px; }
                        .table th { background-color: #f2f2f2; text-align: left; padding: 4px; border: 1px solid #ddd; font-weight: bold; }
                        .table td { padding: 4px; border: 1px solid #ddd; }
                        .badge { border: 1px solid #333; padding: 1px 4px; border-radius: 3px; font-size: 8px; }
                        .badge-success { background-color: #d4edda; color: #155724; }
                        .badge-warning { background-color: #fff3cd; color: #856404; }
                        .badge-info { background-color: #d1ecf1; color: #0c5460; }
                        .badge-secondary { background-color: #e2e3e5; color: #383d41; }
                        .text-center { text-align: center; }
                        .header-info { text-align: center; margin-bottom: 20px; }
                        .print-date { text-align: right; margin-bottom: 10px; }
                    </style>
                </head>
                <body>
                    <div class="header-info">
                        <h2>LAPORAN DATA PEGAWAI</h2>
                        <h3>RSUD MIMIKA</h3>
                        <p>Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
                    </div>
                    <div class="print-date">
                        Dicetak pada: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                    </div>
                    ${printContent.innerHTML}
                </body>
                </html>
            `);
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
                document.title = originalTitle;
            }, 500);
        }

        // Export to Excel
        function exportToExcel() {
            // Create a table clone for export
            const table = document.getElementById('dataTable').cloneNode(true);
            
            // Remove badges and convert to plain text
            table.querySelectorAll('.badge').forEach(badge => {
                badge.parentNode.innerHTML = badge.textContent;
            });
            
            // Remove small badges in STR/SIP columns
            table.querySelectorAll('.badge-warning, .badge-expired').forEach(badge => {
                badge.remove();
            });
            
            // Create HTML string
            const html = `
                <html>
                <head>
                    <title>Laporan Pegawai RSUD Mimika</title>
                    <meta charset="UTF-8">
                </head>
                <body>
                    <h2>LAPORAN DATA PEGAWAI RSUD MIMIKA</h2>
                    <p>Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
                    <p>Tanggal Export: ${new Date().toLocaleDateString('id-ID')}</p>
                    ${table.outerHTML}
                </body>
                </html>
            `;
            
            // Create blob and download
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Laporan_Pegawai_RSUD_Mimika_' + new Date().toISOString().split('T')[0] + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Export to PDF (using window.print as fallback)
        function exportToPDF() {
            alert('Fitur export PDF memerlukan library tambahan seperti jsPDF atau html2pdf.\n\nUntuk saat ini, gunakan fitur "Cetak" lalu pilih "Save as PDF" pada dialog print.');
        }

        // Auto-refresh every 5 minutes if filtered
        <?php if (!empty($start_date) || !empty($end_date) || !empty($status) || !empty($jabatan) || !empty($agama)): ?>
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutes
        <?php endif; ?>

        // Add data table search functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add search box
            const tableHeader = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-3');
            if (tableHeader) {
                const searchDiv = document.createElement('div');
                searchDiv.className = 'col-md-4';
                searchDiv.innerHTML = `
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari data pegawai...">
                    </div>
                `;
                tableHeader.parentNode.insertBefore(searchDiv, tableHeader);
            }

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#dataTable tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }
        });
    </script>

    <style media="print">
        @media print {
            body * {
                visibility: hidden;
            }
            .main-content, .main-content * {
                visibility: visible;
            }
            .sidebar, .print-btn, button, .filter-card form, .filter-card h5:first-child,
            .row.mt-4, .header-info, .print-date {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .filter-card, .stat-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            body {
                background: white !important;
                font-size: 10px !important;
                margin: 0 !important;
                padding: 10px !important;
            }
            h2, h3, h4, h5 {
                color: black !important;
                margin: 5px 0 !important;
            }
            .table {
                font-size: 8px !important;
                width: 100% !important;
                page-break-inside: auto !important;
            }
            .table thead th {
                background-color: #f2f2f2 !important;
                color: black !important;
                border: 1px solid #ddd !important;
                padding: 3px !important;
            }
            .table td {
                padding: 3px !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            .badge {
                border: 1px solid #333 !important;
                color: black !important;
                background: none !important;
                padding: 1px 3px !important;
                font-size: 7px !important;
            }
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            thead {
                display: table-header-group !important;
            }
            tfoot {
                display: table-footer-group !important;
            }
        }
    </style>
</body>
</html>