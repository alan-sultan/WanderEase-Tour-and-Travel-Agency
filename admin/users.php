<?php
require_once 'auth_check.php';

// Get current user data
$user = getCurrentUser();

// Get filter parameters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    try {
        $conn = getDBConnection();
        // Don't allow deleting the last admin
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_role = $stmt->fetchColumn();
        
        if ($user_role === 'admin' && $admin_count <= 1) {
            $_SESSION['error'] = 'Cannot delete the last admin user.';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = 'User deleted successfully.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete user.';
    }
    header('Location: users.php');
    exit;
}

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

if ($role_filter) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
}

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$conn = getDBConnection();
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

$offset = ($page - 1) * $per_page;
$query .= " ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";

// Execute main query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <h3>Admin Panel</h3>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
                    <li><a href="packages.php"><i class="fas fa-suitcase"></i> Manage Packages</a></li>
                    <li><a href="users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="support.php"><i class="fas fa-headset"></i> Support Messages</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <div class="header-content">
                    <h1>Manage Users</h1>
                    <a href="register_admin.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Register New Admin
                    </a>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="search-filter">
                <form method="GET" class="search-box">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search users..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
                <div class="filter-box">
                    <select name="role" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="status-<?php echo $u['role']; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <div class="package-actions">
                                        <?php if ($u['id'] !== $user['id']): ?>
                                            <form method="POST" class="delete-form" 
                                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-small">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&page=<?php echo $i; ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 