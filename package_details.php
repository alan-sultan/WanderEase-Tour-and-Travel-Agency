<?php
require_once 'includes/auth.php';

// Get package ID from URL
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get package details from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

// If package not found, redirect to home
if (!$package) {
    header('Location: index.php');
    exit;
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$error = '';
$success = '';

// Handle booking form submission
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'] ?? '';
    $check_in_date = $_POST['check_in_date'] ?? '';
    $check_out_date = $_POST['check_out_date'] ?? '';
    $travelers = (int)($_POST['travelers'] ?? 0);
    $room_type = $_POST['room_type'] ?? 'Standard';
    $number_of_rooms = (int)($_POST['number_of_rooms'] ?? 1);
    $special_requests = $_POST['special_requests'] ?? '';

    // Validate input
    if (empty($booking_date)) {
        $error = 'Please select a booking date';
    } elseif (empty($check_in_date)) {
        $error = 'Please select a check-in date';
    } elseif (empty($check_out_date)) {
        $error = 'Please select a check-out date';
    } elseif ($check_out_date <= $check_in_date) {
        $error = 'Check-out date must be after check-in date';
    } elseif ($travelers < 1) {
        $error = 'Please enter a valid number of travelers';
    } elseif ($number_of_rooms < 1) {
        $error = 'Please enter a valid number of rooms';
    } else {
        // Calculate total price
        $total_price = ($package['price'] * $travelers) * $number_of_rooms; // Assuming price is per person per package

        // Adjust price based on room type
        switch ($room_type) {
            case 'Deluxe':
                $total_price *= 1.2;
                break;
            case 'Suite':
                $total_price *= 1.5;
                break;
                // Standard has no multiplier
        }

        // Create pending booking
        try {
            $stmt = $conn->prepare("
                INSERT INTO bookings (
                    user_id, package_id, booking_date, check_in_date, check_out_date,
                    travelers, total_price, status, room_type, number_of_rooms,
                    special_requests, payment_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $package_id,
                $booking_date,
                $check_in_date,
                $check_out_date,
                $travelers,
                $total_price,
                $room_type,
                $number_of_rooms,
                $special_requests
            ]);

            $booking_id = $conn->lastInsertId();

            // Redirect to payment page
            header("Location: user/payment.php?booking_id=" . $booking_id);
            exit;
        } catch (PDOException $e) {
            $error = 'Booking failed. Please try again. ' . $e->getMessage(); // Added error message for debugging
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package['title']); ?> - WonderEase Travel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <nav class="container">
            <a href="index.php" class="logo">Wonderease</a>
            <div class="nav-links">
                <a href="index.php#destinations">Destinations</a>
                <a href="index.php#offers">Special Offers</a>
                <a href="index.php#about">About Us</a>
                <a href="index.php#contact">Contact</a>
                <?php if (isLoggedIn()): ?>
                    <div class="profile-dropdown">
                        <button class="profile-icon">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </button>
                        <div class="dropdown-content">
                            <a href="user/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            <a href="user/profile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="user/bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
                            <a href="user/support.php"><i class="fas fa-headset"></i> Support</a>
                            <a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="auth/login.php">User Login</a>
                        <a href="admin/login.php">Admin Login</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="package-details">
                <div class="package-header">
                    <h1><?php echo htmlspecialchars($package['title']); ?></h1>
                    <div class="package-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($package['destination']); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo $package['duration']; ?> days</span>
                        <span class="package-price">$<?php echo number_format($package['price'], 2); ?></span>
                    </div>
                </div>

                <div class="package-content">
                    <div class="package-image">
                        <img src="<?php echo htmlspecialchars($package['image_url'] ?? 'assets/images/default-package.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($package['title']); ?>">
                    </div>

                    <div class="package-description">
                        <h2>Package Description</h2>
                        <p><?php echo htmlspecialchars($package['description']); ?></p>

                        <h3>Package Details</h3>
                        <ul>
                            <li><strong>Destination:</strong> <?php echo htmlspecialchars($package['destination']); ?></li>
                            <li><strong>Duration:</strong> <?php echo $package['duration']; ?> days</li>
                            <li><strong>Price per person:</strong> $<?php echo number_format($package['price'], 2); ?></li>
                            <?php if ($package['is_special_offer']): ?>
                                <li><strong>Special Offer:</strong> <?php echo htmlspecialchars($package['discount_badge']); ?></li>
                                <?php if ($package['expiry_date']): ?>
                                    <li><strong>Offer Expires:</strong> <?php echo date('F d, Y', strtotime($package['expiry_date'])); ?></li>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="booking-section">
                        <h3>Book This Package</h3>
                        <?php if ($error): ?>
                            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="" class="booking-form">
                                <div class="form-group">
                                    <label for="booking_date">Booking Date</label>
                                    <input type="date" id="booking_date" name="booking_date"
                                        min="<?php echo date('Y-m-d'); ?>"
                                        value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="check_in_date">Check-in Date</label>
                                    <input type="date" id="check_in_date" name="check_in_date"
                                        min="<?php echo date('Y-m-d'); ?>"
                                        value="<?php echo htmlspecialchars($_POST['check_in_date'] ?? ''); ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="check_out_date">Check-out Date</label>
                                    <input type="date" id="check_out_date" name="check_out_date"
                                        min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                        value="<?php echo htmlspecialchars($_POST['check_out_date'] ?? ''); ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="travelers">Number of Travelers</label>
                                    <input type="number" id="travelers" name="travelers"
                                        min="1" max="10"
                                        value="<?php echo htmlspecialchars($_POST['travelers'] ?? '1'); ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="room_type">Room Type</label>
                                    <select id="room_type" name="room_type" required>
                                        <option value="Standard" <?php echo ($_POST['room_type'] ?? '') === 'Standard' ? 'selected' : ''; ?>>Standard</option>
                                        <option value="Deluxe" <?php echo ($_POST['room_type'] ?? '') === 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                                        <option value="Suite" <?php echo ($_POST['room_type'] ?? '') === 'Suite' ? 'selected' : ''; ?>>Suite</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="number_of_rooms">Number of Rooms</label>
                                    <input type="number" id="number_of_rooms" name="number_of_rooms"
                                        min="1" max="5"
                                        value="<?php echo htmlspecialchars($_POST['number_of_rooms'] ?? '1'); ?>"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="special_requests">Special Requests</label>
                                    <textarea id="special_requests" name="special_requests" rows="3"
                                        placeholder="Any special requests or requirements?"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Total Price</label>
                                    <p class="total-price" id="total_price">$<?php echo number_format($package['price'], 2); ?></p>
                                </div>

                                <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                            </form>
                        <?php else: ?>
                            <p>Please log in to book this package</p>
                            <a href="auth/login.php" class="btn btn-primary">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> WonderEase Travel. All rights reserved.</p>
        </div>
    </footer>

    <?php if ($isLoggedIn): ?>
        <script>
            // Update total price when travelers, number of rooms, or room type changes
            document.addEventListener('DOMContentLoaded', function() {
                const pricePerPerson = <?php echo $package['price']; ?>;

                const travelersInput = document.getElementById('travelers');
                const roomTypeSelect = document.getElementById('room_type');
                const numberOfRoomsInput = document.getElementById('number_of_rooms');
                const totalPriceDisplay = document.getElementById('total_price');

                function updateTotalPrice() {
                    const travelers = parseInt(travelersInput.value) || 0;
                    const rooms = parseInt(numberOfRoomsInput.value) || 1; // Default to 1 room if not specified
                    const roomType = roomTypeSelect.value;

                    let roomTypeMultiplier = 1;
                    if (roomType === 'Deluxe') {
                        roomTypeMultiplier = 1.2;
                    } else if (roomType === 'Suite') {
                        roomTypeMultiplier = 1.5;
                    }

                    let calculatedPrice = pricePerPerson * travelers;

                    // Apply room type multiplier based on number of rooms
                    calculatedPrice = calculatedPrice * rooms * roomTypeMultiplier;

                    totalPriceDisplay.textContent = '$' + calculatedPrice.toFixed(2);
                }

                travelersInput.addEventListener('input', updateTotalPrice);
                roomTypeSelect.addEventListener('change', updateTotalPrice);
                numberOfRoomsInput.addEventListener('input', updateTotalPrice);

                // Initial calculation on page load
                updateTotalPrice();
            });
        </script>
    <?php endif; ?>
</body>

</html>
