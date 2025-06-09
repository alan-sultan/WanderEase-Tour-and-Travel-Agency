<?php
require_once 'auth_check.php';

// Get current user data
$user = getCurrentUser();

// Get filter parameters
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Handle package deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_package'])) {
    $package_id = (int)$_POST['package_id'];
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $_SESSION['success'] = 'Package deleted successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete package.';
    }
    header('Location: packages.php');
    exit;
}

// Build query
$query = "SELECT * FROM packages WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR destination LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$conn = getDBConnection();
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Add pagination
$limit = (int)$per_page;
$offset = (int)(($page - 1) * $per_page);
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

// Execute main query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - WonderEase Travel</title>
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
                    <li><a href="packages.php" class="active"><i class="fas fa-suitcase"></i> Manage Packages</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="support.php"><i class="fas fa-headset"></i> Support Messages</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <div class="header-content">
                    <h1>Manage Packages</h1>
                    <a href="add_package.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New Package
                    </a>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Search -->
            <div class="search-filter">
                <form method="GET" class="search-box">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search packages..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Packages Grid -->
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <div class="package-image">
                            <?php
                            $image_path = '../' . $package['image_url'];
                            $default_image = '../assets/images/default-package.jpg';

                            // Check if the image exists
                            if (!file_exists($image_path)) {
                                $image_path = $default_image;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>"
                                alt="<?php echo htmlspecialchars($package['title']); ?>"
                                onerror="this.src='<?php echo $default_image; ?>'">
                        </div>
                        <div class="package-details">
                            <h3><?php echo htmlspecialchars($package['title']); ?></h3>
                            <p class="destination">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($package['destination']); ?>
                            </p>
                            <p class="duration">
                                <i class="fas fa-clock"></i>
                                <?php echo $package['duration']; ?> days
                            </p>
                            <p class="price">
                                <i class="fas fa-tag"></i>
                                $<?php echo number_format($package['price'], 2); ?>
                            </p>
                            <div class="package-actions">
                                <a href="edit_package.php?id=<?php echo $package['id']; ?>"
                                    class="btn btn-primary btn-small">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" class="delete-form"
                                    onsubmit="return confirm('Are you sure you want to delete this package?');">
                                    <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                    <button type="submit" name="delete_package" class="btn btn-danger btn-small">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"
                            class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>