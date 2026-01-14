<?php
// logs.php - Halaman khusus untuk melihat audit logs
require_once 'config.php';

// Hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Build query
$query = "SELECT l.*, u.username, u.nama_lengkap 
          FROM logs l 
          LEFT JOIN users u ON l.user_id = u.id 
          WHERE DATE(l.created_at) BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if (!empty($user_id)) {
    $query .= " AND l.user_id = ?";
    $params[] = $user_id;
}

if (!empty($action)) {
    $query .= " AND l.action = ?";
    $params[] = $action;
}

$query .= " ORDER BY l.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique users for filter
$usersQuery = "SELECT id, username, nama_lengkap FROM users ORDER BY username";
$usersStmt = $db->query($usersQuery);
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique actions
$actionsQuery = "SELECT DISTINCT action FROM logs ORDER BY action";
$actionsStmt = $db->query($actionsQuery);
$actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clock-history me-2"></i> Audit Logs</h2>
            <a href="users.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Users
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filter Logs</h5>
            </div>
            <div class="card-body">
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
                        <label class="form-label">User</label>
                        <select class="form-select" name="user_id">
                            <option value="">Semua User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username'] . ' - ' . $user['nama_lengkap']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aksi</label>
                        <select class="form-select" name="action">
                            <option value="">Semua Aksi</option>
                            <?php foreach ($actions as $act): ?>
                                <option value="<?php echo $act; ?>" <?php echo $action == $act ? 'selected' : ''; ?>>
                                    <?php echo $act; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Terapkan Filter
                        </button>
                        <a href="logs.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Activity Logs (<?php echo count($logs); ?> records)</h5>
                    <div>
                        <small class="text-muted">
                            Menampilkan data dari <?php echo date('d M Y', strtotime($start_date)); ?> 
                            sampai <?php echo date('d M Y', strtotime($end_date)); ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Tabel</th>
                                <th>ID Record</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($log['created_at'])); ?><br>
                                        <small class="text-muted"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <strong><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($log['nama_lengkap']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($log['action']) {
                                                case 'CREATE': echo 'success'; break;
                                                case 'UPDATE': echo 'warning'; break;
                                                case 'DELETE': echo 'danger'; break;
                                                case 'LOGIN': echo 'info'; break;
                                                case 'LOGOUT': echo 'secondary'; break;
                                                default: echo 'primary';
                                            }
                                        ?>">
                                            <?php echo $log['action']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['table_name'] ?? '-'; ?></td>
                                    <td><?php echo $log['record_id'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                    <td><small><?php echo $log['ip_address']; ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-journal-x" style="font-size: 48px;"></i>
                                            <h5 class="mt-3">Tidak ada logs ditemukan</h5>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-end">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>