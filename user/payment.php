<?php
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Get current user data
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../auth/login.php');
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['booking_id'])) {
    header('Location: dashboard.php');
    exit;
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT b.*, p.title as package_title, p.destination, p.price as package_price,
           u.name as user_name, u.email as user_email
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

$error = '';
$success = '';
$form_errors = [];

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $name_on_card = $_POST['name_on_card'] ?? '';

    // Validate input
    if (empty($payment_method)) {
        $form_errors['payment_method'] = 'Please select a payment method';
    }
    if (empty($card_number)) {
        $form_errors['card_number'] = 'Card number is required';
    } elseif (!preg_match('/^\d{16}$/', str_replace(' ', '', $card_number))) {
        $form_errors['card_number'] = 'Please enter a valid 16-digit card number';
    }
    if (empty($expiry_date)) {
        $form_errors['expiry_date'] = 'Expiry date is required';
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry_date)) {
        $form_errors['expiry_date'] = 'Please enter a valid expiry date (MM/YY)';
    } else {
        list($month, $year) = explode('/', $expiry_date);
        $current_year = date('y');
        $current_month = date('m');

        if ($year < $current_year || ($year == $current_year && $month < $current_month)) {
            $form_errors['expiry_date'] = 'Card has expired';
        }
    }
    if (empty($cvv)) {
        $form_errors['cvv'] = 'CVV is required';
    } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
        $form_errors['cvv'] = 'Please enter a valid CVV';
    }
    if (empty($name_on_card)) {
        $form_errors['name_on_card'] = 'Name on card is required';
    }

    if (empty($form_errors)) {
        // Simulate payment processing
        $payment_successful = true; // In a real system, this would involve a payment gateway API call

        if ($payment_successful) {
            // Update booking status directly without transaction table interaction
            $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$booking_id]);

            $_SESSION['success'] = "Payment successful. Your booking is confirmed.";
            header('Location: bookings.php');
            exit;
        } else {
            $error = 'Payment processing failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - WonderEase Travel</title>
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
                    <li><a href="bookings.php" class="active">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="support.php">Support</a></li>
                    <li><a href="packages.php">Packages</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="payment-container">
                <h2>Complete Payment</h2>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="booking-summary">
                    <h3>Booking Summary</h3>
                    <div class="summary-details">
                        <p><strong>Package:</strong> <?php echo htmlspecialchars($booking['package_title']); ?></p>
                        <p><strong>Destination:</strong> <?php echo htmlspecialchars($booking['destination']); ?></p>
                        <p><strong>Travelers:</strong> <?php echo $booking['travelers']; ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                    </div>
                </div>

                <form method="POST" class="payment-form" onsubmit="return validateForm()">
                    <div class="form-group <?php echo isset($form_errors['payment_method']) ? 'has-error' : ''; ?>">
                        <label for="payment_method">Payment Method:</label>
                        <select name="payment_method" id="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                            <option value="debit_card" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'debit_card' ? 'selected' : ''; ?>>Debit Card</option>
                        </select>
                        <?php if (isset($form_errors['payment_method'])): ?>
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <span><?php echo $form_errors['payment_method']; ?></span></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?php echo isset($form_errors['card_number']) ? 'has-error' : ''; ?>">
                        <label for="card_number">Card Number:</label>
                        <input type="text" name="card_number" id="card_number"
                            value="<?php echo isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : ''; ?>"
                            placeholder="1234 5678 9012 3456" maxlength="19" required>
                        <?php if (isset($form_errors['card_number'])): ?>
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <span><?php echo $form_errors['card_number']; ?></span></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group <?php echo isset($form_errors['expiry_date']) ? 'has-error' : ''; ?>">
                            <label for="expiry_date">Expiry Date:</label>
                            <input type="text" name="expiry_date" id="expiry_date"
                                value="<?php echo isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : ''; ?>"
                                placeholder="MM/YY" maxlength="5" required>
                            <?php if (isset($form_errors['expiry_date'])): ?>
                                <div class="error-message"><i class="fas fa-exclamation-circle"></i> <span><?php echo $form_errors['expiry_date']; ?></span></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?php echo isset($form_errors['cvv']) ? 'has-error' : ''; ?>">
                            <label for="cvv">CVV:</label>
                            <input type="text" name="cvv" id="cvv"
                                value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>"
                                placeholder="123" maxlength="4" required>
                            <?php if (isset($form_errors['cvv'])): ?>
                                <div class="error-message"><i class="fas fa-exclamation-circle"></i> <span><?php echo $form_errors['cvv']; ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group <?php echo isset($form_errors['name_on_card']) ? 'has-error' : ''; ?>">
                        <label for="name_on_card">Name on Card:</label>
                        <input type="text" name="name_on_card" id="name_on_card"
                            value="<?php echo isset($_POST['name_on_card']) ? htmlspecialchars($_POST['name_on_card']) : ''; ?>"
                            placeholder="Full Name" required>
                        <?php if (isset($form_errors['name_on_card'])): ?>
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <span><?php echo $form_errors['name_on_card']; ?></span></div>
                        <?php endif; ?>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Confirm Payment</button>
                        <a href="bookings.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Format card number with spaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
        });

        // Format expiry date
        document.getElementById('expiry_date').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            e.target.value = value;
        });

        // Validate form before submission
        function validateForm() {
            const cardNumberInput = document.getElementById('card_number');
            const expiryDateInput = document.getElementById('expiry_date');
            const cvvInput = document.getElementById('cvv');
            const nameOnCardInput = document.getElementById('name_on_card');

            const cardNumber = cardNumberInput.value;
            const expiryDate = expiryDateInput.value;
            const cvv = cvvInput.value;
            const nameOnCard = nameOnCardInput.value.trim();

            // Clear previous alerts
            // You might want to use a more sophisticated error display system instead of alerts

            // Validate card number format (4 groups of 4 digits separated by spaces)
            if (!/^\d{4}\s\d{4}\s\d{4}\s\d{4}$/.test(cardNumber)) {
                alert('Please enter a valid card number in the format: XXXX XXXX XXXX XXXX');
                cardNumberInput.focus();
                return false;
            }

            if (!/^(0[1-9]|1[0-2])\/([0-9]{2})$/.test(expiryDate)) {
                alert('Please enter a valid expiry date (MM/YY).');
                expiryDateInput.focus();
                return false;
            }

            // Expiry date check
            const [month, year] = expiryDate.split('/');
            const currentYear = new Date().getFullYear() % 100;
            const currentMonth = new Date().getMonth() + 1;

            if (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                alert('Card has expired.');
                expiryDateInput.focus();
                return false;
            }

            if (!/^\d{3,4}$/.test(cvv)) {
                alert('Please enter a valid 3 or 4 digit CVV.');
                cvvInput.focus();
                return false;
            }

            if (nameOnCard === '') {
                alert('Name on card is required.');
                nameOnCardInput.focus();
                return false;
            }

            return true;
        }
    </script>
</body>

</html>
