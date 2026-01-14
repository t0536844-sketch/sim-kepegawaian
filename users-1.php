<?php
// users.php - Manajemen Pengguna
require_once 'config.php';

// Hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle Add New User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    
    // Validasi
    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah username sudah ada
        $checkQuery = "SELECT id FROM users WHERE username = :username";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password
            $password_hash = hash('sha256', $password);
            
            // Insert user baru
            $query = "INSERT INTO users (username, password, nama_lengkap, role) 
                     VALUES (:username, :password, :nama_lengkap, :role)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':nama_lengkap', $nama_lengkap);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                // Log action
                $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) 
                            VALUES (:user_id, 'CREATE', 'users', :record_id, :description)";
                $logStmt = $db->prepare($logQuery);
                $logStmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':record_id' => $db->lastInsertId(),
                    ':description' => 'Menambah user baru: ' . $username
                ]);
                
                $message = "User berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan user!";
            }
        }
    }
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    $change_password = isset($_POST['change_password']) && $_POST['change_password'] == '1';
    $new_password = trim($_POST['new_password'] ?? '');
    
    // Validasi
    if (empty($username) || empty($nama_lengkap)) {
        $error = "Username dan Nama Lengkap wajib diisi!";
    } else {
        // Cek apakah username sudah digunakan oleh user lain
        $checkQuery = "SELECT id FROM users WHERE username = :username AND id != :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $error = "Username sudah digunakan oleh user lain!";
        } else {
            if ($change_password && !empty($new_password)) {
                // Update dengan password baru
                $password_hash = hash('sha256', $new_password);
                $query = "UPDATE users SET username = :username, password = :password, 
                         nama_lengkap = :nama_lengkap, role = :role WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', $password_hash);
            } else {
                // Update tanpa password
                $query = "UPDATE users SET username = :username, 
                         nama_lengkap = :nama_lengkap, role = :role WHERE id = :id";
                $stmt = $db->prepare($query);
            }
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nama_lengkap', $nama_lengkap);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Log action
                $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) 
                            VALUES (:user_id, 'UPDATE', 'users', :record_id, :description)";
                $logStmt = $db->prepare($logQuery);
                $logStmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':record_id' => $id,
                    ':description' => 'Mengupdate user: ' . $username
                ]);
                
                $message = "User berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui user!";
            }
        }
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Tidak boleh menghapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $error = "Tidak dapat menghapus akun sendiri!";
    } else {
        // Get user info before deletion
        $getQuery = "SELECT username FROM users WHERE id = :id";
        $getStmt = $db->prepare($getQuery);
        $getStmt->bindParam(':id', $id);
        $getStmt->execute();
        $user = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete user
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            // Log action
            $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) 
                        VALUES (:user_id, 'DELETE', 'users', :record_id, :description)";
            $logStmt = $db->prepare($logQuery);
            $logStmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':record_id' => $id,
                ':description' => 'Menghapus user: ' . $user['username']
            ]);
            
            $message = "User berhasil dihapus!";
        } else {
            $error = "Gagal menghapus user!";
        }
    }
}

// Reset Password User
if (isset($_GET['reset'])) {
    $id = $_GET['reset'];
    
    // Reset password ke default "user123"
    $default_password = hash('sha256', 'user123');
    $query = "UPDATE users SET password = :password WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $default_password);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        // Log action
        $logQuery = "INSERT INTO logs (user_id, action, table_name, record_id, description) 
                    VALUES (:user_id, 'UPDATE', 'users', :record_id, :description)";
        $logStmt = $db->prepare($logQuery);
        $logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':record_id' => $id,
            ':description' => 'Reset password user ID: ' . $id
        ]);
        
        $message = "Password berhasil direset ke 'user123'!";
    } else {
        $error = "Gagal reset password!";
    }
}

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user statistics
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin,
    SUM(CASE WHEN role = 'operator' THEN 1 ELSE 0 END) as operator,
    SUM(CASE WHEN role = 'viewer' THEN 1 ELSE 0 END) as viewer
    FROM users";
$statsStmt = $db->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Sistem Kepegawaian RSUD Mimika</title>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card h4 {
            margin: 0;
            font-weight: bold;
        }
        .stat-card.admin { border-left: 4px solid #dc3545; }
        .stat-card.operator { border-left: 4px solid #ffc107; }
        .stat-card.viewer { border-left: 4px solid #28a745; }
        .stat-card.total { border-left: 4px solid #007bff; }
        .modal-lg-custom {
            max-width: 600px;
        }
        .badge-role-admin { background-color: #dc3545; }
        .badge-role-operator { background-color: #ffc107; color: #000; }
        .badge-role-viewer { background-color: #28a745; }
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
            <a class="nav-link active" href="users.php">
                <i class="bi bi-shield-lock me-2"></i> Manajemen User
            </a>
            <div class="mt-5 pt-5"></div>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-shield-lock me-2"></i> Manajemen Pengguna</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus"></i> Tambah User Baru
            </button>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card total">
                    <div class="text-muted">Total User</div>
                    <h4><?php echo $stats['total']; ?></h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card admin">
                    <div class="text-muted">Admin</div>
                    <h4 style="color: #dc3545;"><?php echo $stats['admin']; ?></h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card operator">
                    <div class="text-muted">Operator</div>
                    <h4 style="color: #ffc107;"><?php echo $stats['operator']; ?></h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card viewer">
                    <div class="text-muted">Viewer</div>
                    <h4 style="color: #28a745;"><?php echo $stats['viewer']; ?></h4>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Pengguna Sistem</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th>Tanggal Dibuat</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-info">Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td>
                                    <span class="badge badge-role-<?php echo $user['role']; ?>">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-success">Online</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-warning btn-edit" 
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="<?php echo $user['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-nama="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                                data-role="<?php echo $user['role']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-info btn-reset" 
                                                data-bs-toggle="tooltip" title="Reset Password"
                                                onclick="if(confirm('Reset password user <?php echo $user['username']; ?> ke default (user123)?')) {
                                                    window.location.href='users.php?reset=<?php echo $user['id']; ?>';
                                                }">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-danger btn-delete"
                                                    onclick="if(confirm('Hapus user <?php echo $user['username']; ?>?')) {
                                                        window.location.href='users.php?delete=<?php echo $user['id']; ?>';
                                                    }">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-danger" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Role Permissions Info -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi Hak Akses Role</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="text-danger"><i class="bi bi-shield-check me-2"></i> ADMIN</h6>
                            <ul class="mb-0">
                                <li>Mengelola semua data pegawai</li>
                                <li>Mengelola pengguna sistem</li>
                                <li>Import/Export data</li>
                                <li>Melihat semua laporan</li>
                                <li>Reset password user</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="text-warning"><i class="bi bi-pencil me-2"></i> OPERATOR</h6>
                            <ul class="mb-0">
                                <li>Menambah data pegawai</li>
                                <li>Mengedit data pegawai</li>
                                <li>Melihat data pegawai</li>
                                <li>Melihat laporan</li>
                                <li>Export data</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="text-success"><i class="bi bi-eye me-2"></i> VIEWER</h6>
                            <ul class="mb-0">
                                <li>Melihat data pegawai</li>
                                <li>Melihat laporan</li>
                                <li>Export data (read-only)</li>
                                <li>Tidak bisa menambah/mengedit</li>
                                <li>Tidak bisa menghapus data</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addUserModalLabel">
                            <i class="bi bi-person-plus me-2"></i> Tambah User Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Username</label>
                                <input type="text" class="form-control" name="username" required 
                                       placeholder="min. 3 karakter">
                                <div class="form-text">Username untuk login</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Password</label>
                                <input type="password" class="form-control" name="password" required 
                                       placeholder="min. 6 karakter">
                                <div class="form-text">Password minimal 6 karakter</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Role</label>
                                <select class="form-select" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="operator" selected>Operator</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editUserModalLabel">
                            <i class="bi bi-pencil me-2"></i> Edit User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Username</label>
                                <input type="text" class="form-control" name="username" id="editUsername" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" id="editPassword"
                                       placeholder="Kosongkan jika tidak diubah">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="change_password" value="1" id="changePassword">
                                    <label class="form-check-label" for="changePassword">
                                        Ubah Password
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" id="editNama" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Role</label>
                                <select class="form-select" name="role" id="editRole" required>
                                    <option value="admin">Admin</option>
                                    <option value="operator">Operator</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <small>
                                        <i class="bi bi-info-circle"></i> 
                                        Password default untuk reset: <strong>user123</strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logs Modal -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="logsModalLabel">
                        <i class="bi bi-clock-history me-2"></i> Activity Logs
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aksi</th>
                                    <th>Deskripsi</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $logsQuery = "SELECT l.*, u.username 
                                            FROM logs l 
                                            LEFT JOIN users u ON l.user_id = u.id 
                                            ORDER BY l.created_at DESC 
                                            LIMIT 50";
                                $logsStmt = $db->query($logsQuery);
                                $logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($logs as $log):
                                ?>
                                <tr>
                                    <td><?php echo date('H:i', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo $log['username'] ?? 'System'; ?></td>
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
                                    <td><?php echo $log['description']; ?></td>
                                    <td><small><?php echo $log['ip_address']; ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#usersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                },
                order: [[0, 'asc']]
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Handle edit button click
            $('.btn-edit').click(function() {
                var id = $(this).data('id');
                var username = $(this).data('username');
                var nama = $(this).data('nama');
                var role = $(this).data('role');
                
                $('#editUserId').val(id);
                $('#editUsername').val(username);
                $('#editNama').val(nama);
                $('#editRole').val(role);
                $('#editPassword').val('');
                $('#changePassword').prop('checked', false);
                
                // Update modal title
                $('#editUserModalLabel').html('<i class="bi bi-pencil me-2"></i> Edit User: ' + username);
            });
            
            // Auto-focus on username field when add modal opens
            $('#addUserModal').on('shown.bs.modal', function () {
                $('#addUserModal input[name="username"]').focus();
            });
            
            // Auto-focus on username field when edit modal opens
            $('#editUserModal').on('shown.bs.modal', function () {
                $('#editUserModal input[name="username"]').focus();
            });
            
            // Toggle password field
            $('#changePassword').change(function() {
                if ($(this).is(':checked')) {
                    $('#editPassword').prop('required', true);
                } else {
                    $('#editPassword').prop('required', false);
                }
            });
            
            // Add keyboard shortcuts
            $(document).keydown(function(e) {
                // Ctrl+Shift+A to add new user
                if (e.ctrlKey && e.shiftKey && e.keyCode == 65) {
                    e.preventDefault();
                    $('#addUserModal').modal('show');
                }
                
                // Escape to close modals
                if (e.keyCode == 27) {
                    $('.modal').modal('hide');
                }
            });
            
            // Show last login time
            setInterval(function() {
                $('.badge.bg-success').fadeOut(500).fadeIn(500);
            }, 3000);
        });
        
        // Export users to CSV
        function exportUsers() {
            var table = document.getElementById('usersTable');
            var html = table.outerHTML;
            
            var blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Users_RSUD_Mimika_' + new Date().toISOString().split('T')[0] + '.xls';
            link.click();
        }
    </script>
</body>
</html>