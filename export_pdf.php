<?php
// export_pdf.php — Export laporan kepegawai an dalam format PDF via browser print
require_once 'config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$start_date = sanitizeDate($_GET['start_date'] ?? '');
$end_date = sanitizeDate($_GET['end_date'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$jabatanFilter = sanitize($_GET['jabatan'] ?? '');
$agamaFilter = sanitize($_GET['agama'] ?? '');

$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];
if ($start_date && $end_date) { $query .= " AND DATE(created_at) BETWEEN ? AND ?"; $params[] = $start_date; $params[] = $end_date; }
if ($statusFilter) { $query .= " AND status_kepegawaian = ?"; $params[] = $statusFilter; }
if ($search) { $query .= " AND (nama_lengkap LIKE ? OR nip LIKE ? OR jabatan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($jabatanFilter) { $query .= " AND jabatan LIKE ?"; $params[] = "%$jabatanFilter%"; }
if ($agamaFilter) { $query .= " AND agama = ?"; $params[] = $agamaFilter; }
$query .= " ORDER BY nama_lengkap ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$total = count($pegawai);
$pns = 0; $honorer = 0; $pria = 0; $wanita = 0;
foreach ($pegawai as $p) {
    if ($p['status_kepegawaian'] === 'PNS') $pns++;
    if ($p['status_kepegawaian'] === 'Honorer') $honorer++;
    if ($p['jenis_kelamin'] === 'Pria') $pria++;
    if ($p['jenis_kelamin'] === 'Wanita') $wanita++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kepegawaian — <?= date('d/m/Y') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 11px; }
        .header-logo { text-align: center; margin-bottom: 20px; }
        .header-logo h4 { margin: 0; font-weight: 700; color: #4f46e5; }
        .header-logo p { margin: 0; color: #6b7280; font-size: 12px; }
        .stats-row { display: flex; gap: 10px; margin-bottom: 15px; }
        .stat-box { flex: 1; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 8px 12px; text-align: center; }
        .stat-box h5 { margin: 0; font-weight: 700; }
        .stat-box small { color: #6b7280; }
        .table thead th { background: #4f46e5 !important; color: #fff !important; font-weight: 600; border: none; font-size: 10px; }
        .table tbody td { font-size: 10px; padding: 4px 8px; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f8f9fa; }
        .filter-info { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px; font-size: 11px; }
        .footer-print { text-align: center; margin-top: 20px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .badge-status { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; }
        .badge-pns { background: #d1fae5; color: #065f46; }
        .badge-honorer { background: #fef3c7; color: #92400e; }
        .badge-cpns { background: #dbeafe; color: #1e40af; }
        .badge-kontrak { background: #e5e7eb; color: #374151; }

        @media print {
            body { font-size: 10px; }
            .no-print { display: none !important; }
            .header-logo { margin-bottom: 10px; }
            .stats-row { gap: 5px; }
            .stat-box { padding: 5px 8px; }
            .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-status { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .filter-info { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 1.5cm; size: landscape; }
        }
    </style>
</head>
<body>
    <!-- Action buttons (hidden on print) -->
    <div class="no-print text-end p-3">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak PDF
        </button>
        <a href="laporan.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="container-fluid px-4">
        <!-- Header -->
        <div class="header-logo">
            <h4><i class="bi bi-hospital me-2"></i>RUMAH SAKIT UMUM DAERAH MIMIKA</h4>
            <p>Laporan Data Kepegawaian — Dicetak pada <?= date('d F Y, H:i') ?> WIT</p>
        </div>

        <!-- Filter Info -->
        <?php if ($statusFilter || $jabatanFilter || $agamaFilter || $search): ?>
        <div class="filter-info">
            <strong>Filter:</strong>
            <?php if ($search): ?> Pencarian: <strong><?= e($search) ?></strong> |<?php endif; ?>
            <?php if ($statusFilter): ?> Status: <strong><?= e($statusFilter) ?></strong> |<?php endif; ?>
            <?php if ($jabatanFilter): ?> Jabatan: <strong><?= e($jabatanFilter) ?></strong> |<?php endif; ?>
            <?php if ($agamaFilter): ?> Agama: <strong><?= e($agamaFilter) ?></strong> |<?php endif; ?>
            <?php if ($start_date && $end_date): ?> Periode: <?= date('d/m/Y', strtotime($start_date)) ?> — <?= date('d/m/Y', strtotime($end_date)) ?><?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box"><h5><?= $total ?></h5><small>Total Pegawai</small></div>
            <div class="stat-box"><h5 class="text-success"><?= $pns ?></h5><small>PNS</small></div>
            <div class="stat-box"><h5 class="text-warning"><?= $honorer ?></h5><small>Honorer</small></div>
            <div class="stat-box"><h5><?= $pria ?> : <?= $wanita ?></h5><small>Pria : Wanita</small></div>
        </div>

        <!-- Table -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="18%">Nama Lengkap</th>
                    <th width="12%">NIP</th>
                    <th width="14%">Jabatan</th>
                    <th width="10%">Status</th>
                    <th width="8%">J. Kelamin</th>
                    <th width="10%">Agama</th>
                    <th width="12%">No. Telepon</th>
                    <th width="11%">Tanggal Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($pegawai as $row): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><strong><?= e($row['nama_lengkap']) ?></strong></td>
                    <td><?= e($row['nip']) ?></td>
                    <td><?= e($row['jabatan']) ?></td>
                    <td>
                        <span class="badge-status badge-<?= strtolower($row['status_kepegawaian']) ?>">
                            <?= e($row['status_kepegawaian']) ?>
                        </span>
                    </td>
                    <td><?= e($row['jenis_kelamin']) ?></td>
                    <td><?= e($row['agama']) ?></td>
                    <td><?= e($row['no_telepon']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pegawai)): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-print">
            SIM Kepegawaian RSUD Mimika — Dokumen ini dicetak secara otomatis oleh sistem.
        </div>
    </div>
</body>
</html>
