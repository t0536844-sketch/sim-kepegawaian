<?php
// pegawai.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (nama_lengkap LIKE ? OR nip LIKE ? OR jabatan LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($status) {
    $query .= " AND status_kepegawaian = ?";
    $params[] = $status;
}

$query .= " ORDER BY nama_lengkap ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai - Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR (REPLACEMENT for include 'sidebar.php') -->
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
            <a class="nav-link active" href="pegawai.php">
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
    <!-- END SIDEBAR -->

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Data Pegawai RSUD Mimika</h2>
            <a href="tambah_pegawai.php" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Tambah Pegawai
            </a>
        </div>

        <!-- Filter and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari nama, NIP, atau jabatan..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="PNS" <?php echo $status == 'PNS' ? 'selected' : ''; ?>>PNS</option>
                            <option value="Honorer" <?php echo $status == 'Honorer' ? 'selected' : ''; ?>>Honorer</option>
                            <option value="CPNS" <?php echo $status == 'CPNS' ? 'selected' : ''; ?>>CPNS</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="pegawaiTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Pangkat/Gol</th>
                                <th>Status</th>
                                <th>Jenis Kelamin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($pegawai as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo $row['nip']; ?></td>
                                <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                <td><?php echo $row['pangkat_golongan']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($row['status_kepegawaian']) {
                                            case 'PNS': echo 'success'; break;
                                            case 'Honorer': echo 'warning'; break;
                                            case 'CPNS': echo 'info'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo $row['status_kepegawaian']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $row['jenis_kelamin']; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="detail_pegawai.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit_pegawai.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="hapus_pegawai.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Hapus"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <i class="bi bi-trash"></i>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pegawaiTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                }
            });
        });
    </script>
</body>
</html>