<?php
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Check if package ID is provided
if (!isset($_GET['id'])) {
    header('Location: packages.php');
    exit;
}

$package_id = (int)$_GET['id'];

// Get package details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    header('Location: packages.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $total_price = $package['price'] * $travelers;

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
            header("Location: payment.php?booking_id=" . $booking_id);
            exit;
        } catch (PDOException $e) {
            $error = 'Booking failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Package - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    
    <div class="container">
        <div class="booking-form">
            <h2>Book <?php echo htmlspecialchars($package['title']); ?></h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="package-summary">
                <h3>Package Details</h3>
                <p><strong>Destination:</strong> <?php echo htmlspecialchars($package['destination']); ?></p>
                <p><strong>Duration:</strong> <?php echo $package['duration']; ?> days</p>
                <p><strong>Price per person:</strong> $<?php echo number_format($package['price'], 2); ?></p>
            </div>

            <form method="POST" action="">
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
        </div>
    </div>

    <script>
        // Update total price when travelers or number of rooms changes
        document.getElementById('travelers').addEventListener('change', updateTotalPrice);
        document.getElementById('number_of_rooms').addEventListener('change', updateTotalPrice);
        document.getElementById('room_type').addEventListener('change', updateTotalPrice);

        function updateTotalPrice() {
            const basePrice = <?php echo $package['price']; ?>;
            const travelers = parseInt(document.getElementById('travelers').value) || 0;
            const rooms = parseInt(document.getElementById('number_of_rooms').value) || 0;
            const roomType = document.getElementById('room_type').value;

            // Room type multipliers
            const multipliers = {
                'Standard': 1,
                'Deluxe': 1.5,
                'Suite': 2
            };

            const totalPrice = basePrice * travelers * multipliers[roomType];
            document.getElementById('total_price').textContent = '$' + totalPrice.toFixed(2);
        }

        // Validate check-out date
        document.getElementById('check_in_date').addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const nextDay = new Date(checkInDate);
            nextDay.setDate(nextDay.getDate() + 1);

            const checkOutInput = document.getElementById('check_out_date');
            checkOutInput.min = nextDay.toISOString().split('T')[0];

            if (checkOutInput.value && new Date(checkOutInput.value) <= checkInDate) {
                checkOutInput.value = nextDay.toISOString().split('T')[0];
            }
        });
    </script>
</body>

</html>
