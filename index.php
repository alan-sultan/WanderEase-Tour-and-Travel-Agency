<?php
// Add at the very top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
$conn = getDBConnection();

// Debug database connection
if (!$conn) {
    die("Database connection failed");
}

// Debug packages query
try {
    $stmt = $conn->query("SELECT * FROM packages ORDER BY id DESC LIMIT 6");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($packages)) {
        echo "<!-- No packages found in database -->";
    }
} catch (PDOException $e) {
    die("Error fetching packages: " . $e->getMessage());
}

// Debug special offers query
try {
    $stmt = $conn->query("SELECT * FROM packages WHERE is_special_offer = 1 ORDER BY id DESC LIMIT 3");
    $special_offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($special_offers)) {
        echo "<!-- No special offers found in database -->";
    }
} catch (PDOException $e) {
    die("Error fetching special offers: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wonderease - Your Journey Begins Here</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <nav class="container">
            <a href="index.php" class="logo">Wonderease</a>
            <div class="nav-links">
                <div class="main-links">
                    <a href="#destinations">Destinations</a>
                    <a href="#offers">Special Offers</a>
                    <a href="#about">About Us</a>
                    <a href="#contact">Contact</a>
                </div>
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
        <!-- Hero Section -->
        <section class="welcome-section">
            <h1>Discover Your Dream Destination</h1>
            <p>Experience unforgettable adventures with Wonderease Travel Agency</p>
            <div class="hero-buttons">
                <a href="#destinations" class="btn btn-primary">Explore Destinations</a>
                <a href="#contact" class="btn btn-secondary">Contact Us</a>
            </div>
        </section>

        <div class="container">
            <!-- Search Section -->

            <!-- Featured Destinations -->
            <section id="destinations" class="section">
                <h2 class="section-title">Popular Destinations</h2>
                <p class="section-subtitle">Explore our most sought-after vacation spots</p>

                <div class="package-grid">
                    <?php
                    // Fetch featured packages from database
                    $stmt = $conn->query("SELECT * FROM packages WHERE featured = 1 ORDER BY id DESC LIMIT 6");
                    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($packages as $package) {
                        // Ensure all values have defaults if null
                        $title = $package['title'] ?? 'Untitled Package';
                        $description = $package['description'] ?? 'No description available';
                        $image_url = $package['image_url'] ?? 'assets/images/default-package.jpg';
                        $duration = $package['duration'] ?? 0;
                        $price = $package['price'] ?? 0;
                        $id = $package['id'] ?? 0;

                        echo '<div class="package-card">';
                        echo '<img src="' . htmlspecialchars($image_url) . '" alt="' . htmlspecialchars($title) . '">';
                        echo '<div class="package-card-content">';
                        echo '<h3>' . htmlspecialchars($title) . '</h3>';
                        echo '<p>' . htmlspecialchars($description) . '</p>';
                        echo '<div class="package-meta">';
                        echo '<span><i class="fas fa-clock"></i> ' . $duration . ' days</span>';
                        echo '<span class="package-price">$' . number_format($price, 2) . '</span>';
                        echo '</div>';
                        echo '<a href="package_details.php?id=' . $id . '" class="btn btn-primary">View Details</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <div class="view-all-container">
                    <?php if (isLoggedIn()): ?>
                        <a href="user/packages.php" class="btn btn-secondary">View All Destinations</a>
                    <?php else: ?>
                        <a href="auth/login.php?redirect_to=/wonderease-ip-project/user/packages.php" class="btn btn-secondary">View All Destinations</a>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Special Offers -->
            <section id="offers" class="section">
                <h2 class="section-title">Special Offers</h2>
                <p class="section-subtitle">Limited-time deals you don't want to miss</p>

                <div class="offers-container">
                    <?php
                    // Fetch special offers from database
                    $stmt = $conn->query("SELECT * FROM packages WHERE is_special_offer = 1 ORDER BY expiry_date ASC LIMIT 3");
                    $special_offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($special_offers as $offer) {
                        echo '<div class="offer-card">';
                        if ($offer['discount_badge']) {
                            echo '<div class="offer-badge">' . htmlspecialchars($offer['discount_badge']) . '</div>';
                        }
                        echo '<h3>' . htmlspecialchars($offer['title']) . '</h3>';
                        echo '<p>' . htmlspecialchars($offer['description']) . '</p>';
                        if ($offer['expiry_date']) {
                            echo '<p class="offer-expires">Expires: ' . date('F d, Y', strtotime($offer['expiry_date'])) . '</p>';
                        }

                        echo '<div class="offer-actions-bottom">';
                        if (isLoggedIn()) {
                            echo '<a href="user/book_package.php?id=' . $offer['id'] . '" class="btn btn-primary">Book Now</a>';
                        } else {
                            echo '<a href="auth/login.php" class="btn btn-primary">Login to Book</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </section>

            <!-- Testimonials -->
            <section class="section testimonials-section">
                <h2 class="section-title">What Our Travelers Say</h2>
                <div class="testimonials-container">
                    <?php
                    // In a real application, this would come from a database
                    $testimonials = [
                        [
                            'name' => 'Sarah Johnson',
                            'location' => 'New York, USA',
                            'image' => 'https://randomuser.me/api/portraits/women/32.jpg',
                            'text' => 'Our trip to Bali was absolutely perfect! Wonderease took care of every detail, from flights to accommodations. We\'ll definitely book with them again!'
                        ],
                        [
                            'name' => 'Michael Chen',
                            'location' => 'Toronto, Canada',
                            'image' => 'https://randomuser.me/api/portraits/women/32.jpg',
                            'text' => 'The Paris getaway package exceeded all our expectations. The hotel was in the perfect location, and the guided tours were informative and fun.'
                        ],
                        [
                            'name' => 'Emma Rodriguez',
                            'location' => 'London, UK',
                            'image' => 'https://randomuser.me/api/portraits/women/65.jpg',
                            'text' => 'I was hesitant to book a solo trip, but Wonderease made it so easy and comfortable. The Tokyo adventure was the trip of a lifetime!'
                        ]
                    ];

                    foreach ($testimonials as $testimonial) {
                        echo '<div class="testimonial-card">';
                        echo '<div class="testimonial-content">';
                        echo '<p>"' . $testimonial['text'] . '"</p>';
                        echo '</div>';
                        echo '<div class="testimonial-author">';
                        echo '<img src="' . $testimonial['image'] . '" alt="' . $testimonial['name'] . '">';
                        echo '<div class="author-info">';
                        echo '<h4>' . $testimonial['name'] . '</h4>';
                        echo '<p>' . $testimonial['location'] . '</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </section>

            <!-- Services -->
            <section class="section services-section">
                <h2 class="section-title">Our Services</h2>
                <p class="section-subtitle">We offer comprehensive travel solutions</p>

                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-plane"></i>
                        </div>
                        <h3>Flight Booking</h3>
                        <p>Find the best deals on flights to destinations worldwide.</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h3>Hotel Reservations</h3>
                        <p>Book accommodations ranging from budget to luxury.</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <h3>Vacation Packages</h3>
                        <p>All-inclusive packages for hassle-free travel experiences.</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Car Rentals</h3>
                        <p>Convenient transportation options at competitive prices.</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h3>Guided Tours</h3>
                        <p>Expert-led tours to make the most of your travel experience.</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <h3>Beach Getaways</h3>
                        <p>Perfect beach vacations for relaxation and fun.</p>
                    </div>
                </div>
            </section>

            <!-- About Us -->
            <section id="about" class="section about-section">
                <h2 class="section-title">About Wonderease</h2>
                <div class="about-container">
                    <div class="about-image">
                        <img src="assets/images/agency.jpg" alt="Wonderease Travel Agency">
                    </div>
                    <div class="about-content">
                        <h3>Your Trusted Travel Partner Since 2010</h3>
                        <p>At Wonderease, we believe that travel should be an enriching, hassle-free experience. For over a decade, we've been helping travelers create unforgettable memories through carefully curated travel packages and personalized service.</p>
                        <p>Our team of experienced travel consultants is passionate about exploring the world and sharing their expertise with you. Whether you're planning a romantic getaway, family vacation, or solo adventure, we're here to make your travel dreams a reality.</p>
                        <div class="about-stats">
                            <div class="stat">
                                <span class="stat-number">10,000+</span>
                                <span class="stat-label">Happy Travelers</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">50+</span>
                                <span class="stat-label">Destinations</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">12</span>
                                <span class="stat-label">Years Experience</span>
                            </div>
                        </div>
                        <a href="#" class="btn btn-secondary">Learn More</a>
                    </div>
                </div>
            </section>

            <!-- Newsletter -->
            <section class="section newsletter-section">
                <div class="newsletter-container">
                    <div class="newsletter-content">
                        <h2>Subscribe to Our Newsletter</h2>
                        <p>Stay updated with our latest offers, travel tips, and destination guides.</p>
                    </div>
                    <form class="newsletter-form" id="newsletter-form">
                        <input type="email" id="newsletter-email" name="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                    <div id="newsletter-message" class="alert" style="display:none;"></div>
                </div>
            </section>

            <!-- Contact -->
            <section id="contact" class="section contact-section">
                <h2 class="section-title">Contact Us</h2>
                <p class="section-subtitle">We're here to help plan your perfect trip</p>

                <div class="contact-container">
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h3>Our Location</h3>
                                <p>123 Travel Street, Suite 456<br>New York, NY 10001</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <h3>Phone Number</h3>
                                <p>+1 (555) 123-4567</p>
                                <p>+1 (555) 987-6543</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h3>Email Address</h3>
                                <p>info@wonderease.com</p>
                                <p>support@wonderease.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Working Hours</h3>
                                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                <p>Saturday: 10:00 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form-container">
                        <form class="contact-form" id="contact-form">
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Your Email</label>
                                <input type="email" id="contact-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                        <div id="contact-message" class="alert" style="display:none;"></div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Wonderease</h3>
                    <p>Your trusted travel partner for unforgettable experiences and adventures around the world.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#destinations">Destinations</a></li>
                        <li><a href="#offers">Special Offers</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Popular Destinations</h3>
                    <ul class="footer-links">
                        <li><a href="#">Bali, Indonesia</a></li>
                        <li><a href="#">Paris, France</a></li>
                        <li><a href="#">Tokyo, Japan</a></li>
                        <li><a href="#">Santorini, Greece</a></li>
                        <li><a href="#">New York, USA</a></li>
                        <li><a href="#">Maldives</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Payment Methods</h3>
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <i class="fab fa-cc-discover"></i>
                    </div>
                    <div class="footer-app-download">
                        <h3>Download Our App</h3>
                        <div class="app-buttons">
                            <a href="#" class="app-button">
                                <i class="fab fa-apple"></i>
                                <span>App Store</span>
                            </a>
                            <a href="#" class="app-button">
                                <i class="fab fa-google-play"></i>
                                <span>Google Play</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Wonderease Travel Agency. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Simple JavaScript for mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }

            // Newsletter Form Submission
            const newsletterForm = document.getElementById('newsletter-form');
            const newsletterMessage = document.getElementById('newsletter-message');

            if (newsletterForm) {
                newsletterForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    newsletterMessage.style.display = 'none';

                    try {
                        const response = await fetch('actions/subscribe_newsletter.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        if (data.status === 'success') {
                            newsletterMessage.classList.remove('alert-error');
                            newsletterMessage.classList.add('alert-success');
                            newsletterForm.reset(); // Clear the form
                        } else {
                            newsletterMessage.classList.remove('alert-success');
                            newsletterMessage.classList.add('alert-error');
                        }
                        newsletterMessage.textContent = data.message;
                        newsletterMessage.style.display = 'block';
                    } catch (error) {
                        console.error('Newsletter subscription error:', error);
                        newsletterMessage.classList.remove('alert-success');
                        newsletterMessage.classList.add('alert-error');
                        newsletterMessage.textContent = 'An unexpected error occurred. Please try again.';
                        newsletterMessage.style.display = 'block';
                    }
                });
            }

            // Contact Form Submission
            const contactForm = document.getElementById('contact-form');
            const contactMessage = document.getElementById('contact-message');

            if (contactForm) {
                contactForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    contactMessage.style.display = 'none';

                    try {
                        const response = await fetch('actions/submit_contact.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        if (data.status === 'success') {
                            contactMessage.classList.remove('alert-error');
                            contactMessage.classList.add('alert-success');
                            contactForm.reset(); // Clear the form
                        } else {
                            contactMessage.classList.remove('alert-success');
                            contactMessage.classList.add('alert-error');
                        }
                        contactMessage.textContent = data.message;
                        contactMessage.style.display = 'block';
                    } catch (error) {
                        console.error('Contact form submission error:', error);
                        contactMessage.classList.remove('alert-success');
                        contactMessage.classList.add('alert-error');
                        contactMessage.textContent = 'An unexpected error occurred. Please try again.';
                        contactMessage.style.display = 'block';
                    }
                });
            }
        });
    </script>
</body>

</html>
