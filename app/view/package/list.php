<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="packages.css" />
  <link rel="icon" href="images/logo.avif" />
  <title>WanderEase Tour and Travel Agency</title>
</head>

<body>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="error" style="color: red; text-align: center; margin-bottom: 15px;">
      <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="success" style="color: green; text-align: center; margin-bottom: 15px;">
      <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <nav>
    <div class="nav__header">
      <div class="nav__logo">
        <a href="#" class="logo">WanderEase Tour and <span>Travel Agency</span></a>
      </div>
      <div class="nav__menu__btn" id="menu-btn">
        <i class="ri-menu-line"></i>
      </div>
    </div>
    <ul class="nav__links" id="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="#destination">Destinations</a></li>
      <li><a href="#package">Packages</a></li>
      <li><a href="#client">Clients</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="logout.php">Logout</a></li>
      <?php else: ?>
        <li><a href="login.html">Login</a></li>
        <li><a href="register.html">Register</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <section class="section__container package__container" id="package">
    <h2 class="section__header">Featured Packages</h2>
    <p class="section__description">We will help you find the trip that's perfect for you!</p>
    <div class="package__grid">
      <?php
      $packages = [
        ["Thailand City Tour", "images/package-1.jpg", "Explore the vibrant culture and stunning landscapes of Thailand's bustling cities.", 130],
        ["Laos City Tour", "images/package-2.jpg", "Immerse yourself in the rich culture and stunning landscapes of Laos.", 150],
        ["Singapore City Tour", "images/package-3.jpg", "Experience the vibrant blend of culture, cuisine, and cutting-edge architecture.", 110],
        ["Dubai City Tour", "images/package-4.jpg", "Explore the vibrant culture and stunning landscapes of Dubai's bustling cities.", 230],
        ["Italy City Tour", "images/package-5.jpg", "Immerse yourself in the rich culture and stunning landscapes of Italy.", 250],
        ["Japan City Tour", "images/package-6.jpg", "Experience the vibrant blend of culture, cuisine, and cutting-edge architecture.", 210]
      ];
      foreach ($packages as $package):
      ?>
        <div class="package__card">
          <img src="<?= $package[1] ?>" alt="package" />
          <div class="package__card__details">
            <h4><?= $package[0] ?></h4>
            <p><?= $package[2] ?></p>
            <div>
              <?php if (isset($_SESSION['user_id'])): ?>
                <form action="book.php" method="POST">
                  <input type="hidden" name="package_name" value="<?= $package[0] ?>">
                  <input type="hidden" name="price" value="<?= $package[3] ?>">
                  <button class="btn">Book Now</button>
                </form>
              <?php else: ?>
                <a href="login.html" class="btn">Log in to Book</a>
              <?php endif; ?>
              <h3>$<?= $package[3] ?></h3>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section__container client__container" id="client">
    <h2 class="section__header">Client Reviews</h2>
    <p class="section__description">We have many happy customers who booked holidays with us</p>
    <div class="clients-container">
      <button class="arrow left" onclick="changeCard('left')">&#8592;</button>
      <div class="clients-card">
        <div class="card">
          <div class="mini-card">
            <img id="photo" src="images/client-1.jpg" alt="Profile Picture" />
            <div>
              <h2 id="name">Michael Lee</h2>
              <p id="date">24 May, 2021</p>
            </div>
          </div>
          <p id="stars">★★★★★</p>
        </div>
        <p id="writtenReview">
          The instant booking feature is a game-changer. I secured my dream vacation to Bali in minutes!
        </p>
      </div>
      <button class="arrow right" onclick="changeCard('right')">&#8594;</button>
    </div>
  </section>

  <section class="subscribe">
    <div class="section__container subscribe__container">
      <h2>Subscribe to our newsletter for updates</h2>
      <div>
        <p>For the best recommendation for your journey. Please subscribe to us.</p>
        <form action="subscribe.php" method="POST">
          <input type="email" name="email" placeholder="Enter Your Email Here" required />
          <button class="btn">SUBSCRIBE</button>
        </form>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="section__container footer__container">
      <div class="footer__col">
        <div class="footer__logo">
          <a href="#" class="logo">WanderEase<span> Travel</span></a>
        </div>
        <p>
          Explore the world's best destinations, enjoy unbeatable prices, and book your perfect getaway instantly.
        </p>
        <h4>CONNECT WITH US</h4>
        <ul class="footer__socials">
          <li><a href="#"><i class="ri-twitter-fill"></i></a></li>
          <li><a href="#"><i class="ri-google-fill"></i></a></li>
          <li><a href="#"><i class="ri-linkedin-fill"></i></a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>QUICK LINKS</h4>
        <ul class="footer__links">
          <li><a href="index.php">Home</a></li>
          <li><a href="register.html">Register</a></li>
          <li><a href="login.html">Login</a></li>
          <li><a href="#">Testimonials</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4 id="destination">DESTINATIONS</h4>
        <ul class="footer__links">
          <li><a href="#">China</a></li>
          <li><a href="#">Dubai</a></li>
          <li><a href="#">Brazil</a></li>
          <li><a href="#">Australia</a></li>
          <li><a href="#">London</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>OUR ACTIVITIES</h4>
        <ul class="footer__links">
          <li><a href="#">Trekking</a></li>
          <li><a href="#">Peak Climbing</a></li>
          <li><a href="#">Biking</a></li>
          <li><a href="#">River Rafting</a></li>
          <li><a href="#">Cultural Tour</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bar">
      Copyright © 2024 WanderEase.
    </div>
  </footer>

  <script src="https://unpkg.com/scrollreveal"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="main.js"></script>
</body>

</html>
