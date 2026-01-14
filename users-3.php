<?php
// users.php - Manajemen Pengguna dengan Hak Akses Diperketat
require_once 'config.php';

// HANYA ADMIN yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Akses ditolak! Hanya Administrator yang dapat mengakses halaman ini.";
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// HANYA ADMIN YANG BISA MENAMBAH USER ADMIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    
    // Validasi
    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // HANYA ADMIN YANG BISA MEMBUAT USER DENGAN ROLE ADMIN
        if ($role == 'admin' && $_SESSION['role'] != 'admin') {
            $error = "Hanya Administrator yang dapat membuat user dengan role Admin!";
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
                        ':description' => 'Menambah user baru: ' . $username . ' (Role: ' . $role . ')'
                    ]);
                    
                    $message = "User berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan user!";
                }
            }
        }
    }
}

// HANYA ADMIN YANG BISA UPDATE USER
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
    } elseif ($change_password && strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } else {
        // HANYA ADMIN YANG BISA MENGUBAH ROLE MENJADI ADMIN
        if ($role == 'admin' && $_SESSION['role'] != 'admin') {
            $error = "Hanya Administrator yang dapat mengubah role menjadi Admin!";
        } else {
            // Get current user data
            $currentQuery = "SELECT role, username FROM users WHERE id = :id";
            $currentStmt = $db->prepare($currentQuery);
            $currentStmt->bindParam(':id', $id);
            $currentStmt->execute();
            $currentUser = $currentStmt->fetch(PDO::FETCH_ASSOC);
            
            // ADMIN TIDAK BISA MENURUNKAN ROLE ADMIN LAIN
            if ($currentUser['role'] == 'admin' && $role != 'admin' && $_SESSION['role'] == 'admin') {
                $error = "Tidak dapat menurunkan role Admin!";
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
                            ':description' => 'Mengupdate user: ' . $currentUser['username'] . ' → ' . $username . ' (Role: ' . $role . ')'
                        ]);
                        
                        $message = "User berhasil diperbarui!";
                    } else {
                        $error = "Gagal memperbarui user!";
                    }
                }
            }
        }
    }
}

// HANYA ADMIN YANG BISA MENGHAPUS USER
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get user info before deletion
    $getQuery = "SELECT username, role FROM users WHERE id = :id";
    $getStmt = $db->prepare($getQuery);
    $getStmt->bindParam(':id', $id);
    $getStmt->execute();
    $user = $getStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "User tidak ditemukan!";
    } else {
        // Tidak boleh menghapus diri sendiri
        if ($id == $_SESSION['user_id']) {
            $error = "Tidak dapat menghapus akun sendiri!";
        } 
        // ADMIN TIDAK BISA MENGHAPUS ADMIN LAIN
        elseif ($user['role'] == 'admin' && $_SESSION['role'] == 'admin') {
            $error = "Tidak dapat menghapus user dengan role Admin!";
        } else {
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
                    ':description' => 'Menghapus user: ' . $user['username'] . ' (Role: ' . $user['role'] . ')'
                ]);
                
                $message = "User berhasil dihapus!";
            } else {
                $error = "Gagal menghapus user!";
            }
        }
    }
}

// HANYA ADMIN YANG BISA RESET PASSWORD
if (isset($_GET['reset'])) {
    $id = $_GET['reset'];
    
    // Get user info
    $getQuery = "SELECT username, role FROM users WHERE id = :id";
    $getStmt = $db->prepare($getQuery);
    $getStmt->bindParam(':id', $id);
    $getStmt->execute();
    $user = $getStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "User tidak ditemukan!";
    } else {
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
                ':description' => 'Reset password user: ' . $user['username'] . ' (Role: ' . $user['role'] . ')'
            ]);
            
            $message = "Password berhasil direset ke 'user123'!";
        } else {
            $error = "Gagal reset password!";
        }
    }
}

// Get all users (ADMIN bisa lihat semua, lainnya hanya lihat non-admin)
if ($_SESSION['role'] == 'admin') {
    $query = "SELECT * FROM users ORDER BY 
              CASE role 
                WHEN 'admin' THEN 1 
                WHEN 'operator' THEN 2 
                WHEN 'viewer' THEN 3 
              END, created_at DESC";
} else {
    $query = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
}

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
        .permission-badge {
            font-size: 0.7rem;
            margin-left: 5px;
        }
        .admin-only {
            border: 2px solid #dc3545;
            position: relative;
        }
        .admin-only:before {
            content: "ADMIN ONLY";
            position: absolute;
            top: -10px;
            right: 10px;
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
        }
        .disabled-action {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .access-level {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        .access-admin { background: #dc3545; color: white; }
        .access-operator { background: #ffc107; color: #000; }
        .access-viewer { background: #28a745; color: white; }
    </style>
</head>
<body>
    <!-- SIDEBAR DENGAN LINK AUDIT LOGS -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4>RSUD MIMIKA</h4>
            <small>Sistem Kepegawaian</small>
        </div>
        <div class="px-3 mb-4">
            <div class="bg-white rounded-pill p-2 text-dark text-center">
                <i class="bi bi-person-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                <span class="badge bg-primary ms-2"><?php echo strtoupper($_SESSION['role']); ?></span>
            </div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link" href="pegawai.php">
                <i class="bi bi-people me-2"></i> Data Pegawai
            </a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a class="nav-link active" href="users.php">
                    <i class="bi bi-shield-lock me-2"></i> Manajemen User
                    <span class="badge bg-danger permission-badge">ADMIN</span>
                </a>
                <a class="nav-link" href="logs.php">
                    <i class="bi bi-clock-history me-2"></i> Audit Logs
                    <span class="badge bg-danger permission-badge">ADMIN</span>
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
            <div>
                <h2><i class="bi bi-shield-lock me-2"></i> Manajemen Pengguna</h2>
                <p class="text-muted mb-0">Hak Akses: 
                    <span class="access-level access-<?php echo $_SESSION['role']; ?>">
                        <?php echo strtoupper($_SESSION['role']); ?>
                    </span>
                </p>
            </div>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Tambah User Baru
                </button>
            <?php endif; ?>
        </div>

        <!-- Warning for non-admin users -->
        <?php if ($_SESSION['role'] != 'admin'): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong> Anda login sebagai 
                <span class="badge bg-warning"><?php echo strtoupper($_SESSION['role']); ?></span>. 
                Hanya user dengan role <span class="badge bg-danger">ADMIN</span> yang dapat mengelola data user.
            </div>
        <?php endif; ?>

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
        <?php if ($_SESSION['role'] == 'admin'): ?>
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
        <?php endif; ?>

        <!-- Users Table -->
        <div class="card <?php echo $_SESSION['role'] == 'admin' ? 'admin-only' : ''; ?>">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Pengguna Sistem</h5>
                <?php if ($_SESSION['role'] != 'admin'): ?>
                    <small class="text-muted">Read-only mode</small>
                <?php endif; ?>
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
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
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
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="badge bg-danger permission-badge">FULL ACCESS</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-success">Online</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Offline</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Button -->
                                            <?php if ($user['role'] == 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-warning disabled-action" 
                                                        title="Tidak dapat mengedit Admin lain">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-warning btn-edit" 
                                                        data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                        data-nama="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                                        data-role="<?php echo $user['role']; ?>"
                                                        data-current-role="<?php echo $user['role']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Reset Password Button -->
                                            <button type="button" class="btn btn-info btn-reset" 
                                                    data-bs-toggle="tooltip" title="Reset Password"
                                                    onclick="confirmReset('<?php echo $user['username']; ?>', <?php echo $user['id']; ?>)">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-danger disabled-action" 
                                                        title="Tidak dapat menghapus akun sendiri">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php elseif ($user['role'] == 'admin'): ?>
                                                <button type="button" class="btn btn-danger disabled-action" 
                                                        title="Tidak dapat menghapus Admin">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-danger btn-delete"
                                                        onclick="confirmDelete('<?php echo $user['username']; ?>', <?php echo $user['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
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
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-danger"><i class="bi bi-shield-check me-2"></i> ADMIN</h6>
                            <div class="mb-2">
                                <span class="badge bg-danger">Super User</span>
                            </div>
                            <ul class="mb-0">
                                <li><strong>Full System Access</strong></li>
                                <li>Mengelola semua data pegawai</li>
                                <li>Mengelola pengguna sistem</li>
                                <li>Import/Export data</li>
                                <li>Melihat semua laporan</li>
                                <li>Reset password user</li>
                                <li>Access Audit Logs</li>
                                <li><strong>Hanya Admin yang bisa:</strong></li>
                                <li class="small">- Membuat user Admin baru</li>
                                <li class="small">- Mengedit user Admin</li>
                                <li class="small">- Menghapus user</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-warning"><i class="bi bi-pencil me-2"></i> OPERATOR</h6>
                            <div class="mb-2">
                                <span class="badge bg-warning">Read/Write Access</span>
                            </div>
                            <ul class="mb-0">
                                <li><strong>Data Management</strong></li>
                                <li>Menambah data pegawai</li>
                                <li>Mengedit data pegawai</li>
                                <li>Melihat data pegawai</li>
                                <li>Melihat laporan</li>
                                <li>Export data</li>
                                <li><strong>Tidak bisa:</strong></li>
                                <li class="small">- Mengelola user</li>
                                <li class="small">- Import data</li>
                                <li class="small">- Reset password</li>
                                <li class="small">- Access Audit Logs</li>
                                <li class="small">- Menghapus data pegawai</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-success"><i class="bi bi-eye me-2"></i> VIEWER</h6>
                            <div class="mb-2">
                                <span class="badge bg-success">Read-Only Access</span>
                            </div>
                            <ul class="mb-0">
                                <li><strong>View Only</strong></li>
                                <li>Melihat data pegawai</li>
                                <li>Melihat laporan</li>
                                <li>Export data (read-only)</li>
                                <li><strong>Tidak bisa:</strong></li>
                                <li class="small">- Menambah/mengedit data</li>
                                <li class="small">- Menghapus data</li>
                                <li class="small">- Import data</li>
                                <li class="small">- Mengelola user</li>
                                <li class="small">- Access Audit Logs</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Permission Matrix -->
                <div class="mt-4">
                    <h6>Matriks Hak Akses:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fitur</th>
                                    <th class="text-center">Admin</th>
                                    <th class="text-center">Operator</th>
                                    <th class="text-center">Viewer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Lihat Data Pegawai</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Tambah/Edit Pegawai</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>Hapus Pegawai</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>Import Data</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>Export Data</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>Manajemen User</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>Audit Logs</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td>Reset Password</td>
                                    <td class="text-center"><i class="bi bi-check-lg text-success"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                    <td class="text-center"><i class="bi bi-x-lg text-danger"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal (Hanya untuk Admin) -->
    <?php if ($_SESSION['role'] == 'admin'): ?>
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom">
            <div class="modal-content">
                <form method="POST" id="addUserForm">
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
                                       placeholder="min. 3 karakter" minlength="3">
                                <div class="form-text">Username untuk login</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" required 
                                           placeholder="min. 6 karakter" minlength="6" id="passwordInput">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('passwordInput')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Password minimal 6 karakter</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Role</label>
                                <select class="form-select" name="role" required id="roleSelect">
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin (Full Access)</option>
                                    <option value="operator" selected>Operator (Read/Write)</option>
                                    <option value="viewer">Viewer (Read-Only)</option>
                                </select>
                                <div id="adminWarning" class="alert alert-danger mt-2" style="display: none;">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>Perhatian:</strong> Hanya Admin yang dapat membuat user dengan role Admin.
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <small>
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Default Password:</strong> user123
                                    </small>
                                </div>
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
                <form method="POST" id="editUserForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editUserId">
                    <input type="hidden" name="current_role" id="editCurrentRole">
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
                                <div class="input-group">
                                    <input type="password" class="form-control" name="new_password" id="editPassword"
                                           placeholder="Kosongkan jika tidak diubah" minlength="6">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('editPassword')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
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
                                    <option value="admin">Admin (Full Access)</option>
                                    <option value="operator">Operator (Read/Write)</option>
                                    <option value="viewer">Viewer (Read-Only)</option>
                                </select>
                                <div id="roleChangeWarning" class="alert alert-warning mt-2" style="display: none;">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>Perhatian:</strong> Tidak dapat menurunkan role Admin ke role lainnya.
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <small>
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Reset Password:</strong> Gunakan tombol reset password untuk mengatur ke default "user123"
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
    <?php endif; ?>

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
                order: [[0, 'asc']],
                columnDefs: [
                    <?php if ($_SESSION['role'] != 'admin'): ?>
                    { targets: [6], visible: false } // Hide actions column for non-admin
                    <?php endif; ?>
                ]
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
                var currentRole = $(this).data('current-role');
                
                $('#editUserId').val(id);
                $('#editUsername').val(username);
                $('#editNama').val(nama);
                $('#editRole').val(role);
                $('#editCurrentRole').val(currentRole);
                $('#editPassword').val('');
                $('#changePassword').prop('checked', false);
                
                // Show/hide role change warning
                if (currentRole === 'admin') {
                    $('#roleChangeWarning').show();
                    $('#editRole').prop('disabled', true);
                } else {
                    $('#roleChangeWarning').hide();
                    $('#editRole').prop('disabled', false);
                }
                
                // Update modal title
                $('#editUserModalLabel').html('<i class="bi bi-pencil me-2"></i> Edit User: ' + username);
            });
            
            // Role selection warning in add modal
            $('#roleSelect').change(function() {
                if ($(this).val() === 'admin') {
                    $('#adminWarning').show();
                } else {
                    $('#adminWarning').hide();
                }
            });
            
            // Auto-focus on username field when add modal opens
            $('#addUserModal').on('shown.bs.modal', function () {
                $('#addUserModal input[name="username"]').focus();
            });
            
            // Auto-focus on username field when edit modal opens
            $('#editUserModal').on('shown.bs.modal', function () {
                $('#editUserModal input[name="username"]').focus();
            });
            
            // Toggle password field requirement
            $('#changePassword').change(function() {
                if ($(this).is(':checked')) {
                    $('#editPassword').prop('required', true);
                } else {
                    $('#editPassword').prop('required', false);
                }
            });
            
            // Password validation
            $('#addUserForm, #editUserForm').submit(function(e) {
                var password = $('#addUserForm input[name="password"]').val() || 
                               $('#editUserForm input[name="new_password"]').val();
                var changePassword = $('#changePassword').is(':checked');
                
                if (changePassword && password && password.length < 6) {
                    e.preventDefault();
                    alert('Password minimal 6 karakter!');
                    return false;
                }
                
                return true;
            });
            
            // Add keyboard shortcuts (admin only)
            <?php if ($_SESSION['role'] == 'admin'): ?>
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
            <?php endif; ?>
            
            // Show online status indicator
            setInterval(function() {
                $('.badge.bg-success').fadeOut(500).fadeIn(500);
            }, 3000);
        });
        
        // Toggle password visibility
        function togglePassword(inputId) {
            var input = $('#' + inputId);
            var type = input.attr('type');
            
            if (type === 'password') {
                input.attr('type', 'text');
                $(input.next().find('i')).removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                $(input.next().find('i')).removeClass('bi-eye-slash').addClass('bi-eye');
            }
        }
        
        // Confirm delete
        function confirmDelete(username, id) {
            if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?\n\n' +
                       'Tindakan ini tidak dapat dibatalkan!')) {
                window.location.href = 'users.php?delete=' + id;
            }
        }
        
        // Confirm reset password
        function confirmReset(username, id) {
            if (confirm('Reset password user "' + username + '" ke default "user123"?\n\n' +
                       'User harus login kembali dengan password baru.')) {
                window.location.href = 'users.php?reset=' + id;
            }
        }
        
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
        
        // Check if user is admin (for UI adjustments)
        var isAdmin = <?php echo $_SESSION['role'] == 'admin' ? 'true' : 'false'; ?>;
        
        if (!isAdmin) {
            // Disable all form elements for non-admin
            $('input, select, button, textarea').prop('disabled', true);
            $('.btn').addClass('disabled-action');
        }
    </script>
</body>
</html>