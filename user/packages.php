<?php
require_once '../includes/auth.php';

// Get all packages
$conn = getDBConnection();

$sql = "SELECT * FROM packages WHERE 1=1";
$params = [];

// Apply filters based on GET parameters
if (isset($_GET['destination']) && !empty($_GET['destination'])) {
    $sql .= " AND destination LIKE :destination";
    $params[':destination'] = '%' . $_GET['destination'] . '%';
}

if (isset($_GET['departure_date']) && !empty($_GET['departure_date'])) {
    $sql .= " AND start_date >= :departure_date";
    $params[':departure_date'] = $_GET['departure_date'];
}

if (isset($_GET['return_date']) && !empty($_GET['return_date'])) {
    $sql .= " AND end_date <= :return_date";
    $params[':return_date'] = $_GET['return_date'];
}

// Note: 'travelers' is not a direct column in the 'packages' table based on previous schema info.
// For now, I'll omit this filter. If you have a column for max_travelers or similar, we can add it.

$sql .= " ORDER BY featured DESC, created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current user data
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Packages - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <h3>Welcome, <?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="bookings.php">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="support.php">Support</a></li>
                    <li><a href="packages.php" class="active">Packages</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Travel Packages</h1>
            </header>

            <!-- Search Form on Packages Page (optional, but good for persistence) -->
            <section class="search-section" style="padding: 15px 0; margin-top: 10px;">
                <form action="packages.php" method="GET" class="search-bar">
                    <input type="text" name="destination" placeholder="Where do you want to go?" value="<?php echo htmlspecialchars($_GET['destination'] ?? ''); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($_GET)): // Show clear button if any search parameters are set 
                    ?>
                        <a href="packages.php" class="btn btn-secondary">Clear Search</a>
                    <?php endif; ?>
                </form>
            </section>

            <div class="package-grid">
                <?php if (empty($packages)): ?>
                    <p>No packages found matching your criteria.</p>
                <?php else: ?>
                    <?php foreach ($packages as $package): ?>
                        <div class="package-card">
                            <?php
                            // Fix image path by removing the leading 'assets/' since we're in the user directory
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
                            <div class="package-card-content">
                                <h2><?php echo htmlspecialchars($package['title']); ?></h2>
                                <p class="destination"><?php echo htmlspecialchars($package['destination']); ?></p>
                                <p class="description"><?php echo htmlspecialchars($package['description']); ?></p>
                                <div class="package-details">
                                    <p><strong>Duration:</strong> <?php echo $package['duration']; ?> days</p>
                                    <p class="package-price">$<?php echo number_format($package['price'], 2); ?></p>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                    <a href="book_package.php?id=<?php echo $package['id']; ?>" class="btn btn-primary">Book Now</a>
                                <?php else: ?>
                                    <a href="../auth/login.php" class="btn btn-primary">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
