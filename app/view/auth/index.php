<?php if (isset($error)): ?>
    <div style="color:red;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div style="color:green;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="images/logo.avif"/>
    <title>WanderEase Tour and Travel Agency</title>
</head>

<body>
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
            <li><a href="#home">Home</a></li>
            <li><a href="#service">Services</a></li>
            <li><a href="#destination">Destinations</a></li>
            <li><a href="packages.html" >Packages</a></li>
            <li><a href="#client">Clients</a></li>
        </ul>
    </nav>
    <header class="header" id="home">
        <div class="section__container header__container">
            <h1>TRAVELLER<br /><span>FOR LIFE.</span></h1>
            <p>Live your best moments</p>
            <form action="/">
                <div class="input__group">
                    <span><i class="ri-map-2-fill"></i></span>
                    <input type="text" placeholder="Where are you going?" />
                </div>
                <div class="input__group">
                    <span><i class="ri-map-pin-2-fill"></i></span>
                    <input type="text" placeholder="Location" />
                </div>
                <div class="input__group">
                    <span><i class="ri-list-ordered-2"></i></span>
                    <input type="text" placeholder="Tour type" />
                </div>
                <div class="input__group">
                    <button class="btn">Search</button>
                </div>
            </form>
        </div>
    </header>

    <section class="section__container feature__container" id="service">
        <div class="feature__card">
            <img src="images/feature-1.png" alt="feature" />
            <div>
                <h4>Best Destinations</h4>
                <p>Discover the most breathtaking places around the globe.</p>
            </div>
        </div>
        <div class="feature__card">
            <img src="images/feature-2.png" alt="feature" />
            <div>
                <h4>Best Price Guaranteed</h4>
                <p>Enjoy unbeatable prices on every trip you book.</p>
            </div>
        </div>
        <div class="feature__card">
            <img src="images/feature-3.png" alt="feature" />
            <div>
                <h4>Instant Booking</h4>
                <p>Secure your dream vacation with just a click.</p>
            </div>
        </div>
    </section>

    <section class="destination" id="destination">
        <div class="section__container destination__container">
            <h2 class="section__header">Top Destinations</h2>
            <p class="section__description">
                Find out what are the best destinations in the world
            </p>
            <div class="destination__grid">
                <div class="destination__card">
                    <img src="images/destination-1.jpg" alt="destination" />
                    <div class="destination__content">Bhutan</div>
                </div>
                <div class="destination__card">
                    <img src="images/destination-2.jpg" alt="destination" />
                    <div class="destination__content">Japan</div>
                </div>
                <div class="destination__card">
                    <img src="images/destination-3.jpg" alt="destination" />
                    <div class="destination__content">Nepal</div>
                </div>
            </div>
        </div>
    </section>

    <section class="discount">
        <div class="section__container discount__container">
            <h2>
                Get upto 60% discount<br /><span>by joining us before summer</span>
            </h2>
            <div class="discount__btn">
                <button class="btn">Join Us</button>
            </div>
        </div>
    </section>

    <section class="section__container client__container" id="client">
        <h2 class="section__header">Client Reviews</h2>
        <p class="section__description">
            We have many happy customers who booked holidays with us
        </p>
        <div class="clients-container">
            <button class="arrow left" onclick="changeCard('left')">
              &#8592;
            </button>
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
                The instant booking feature is a game-changer. I secured my
                    dream vacation to Bali in minutes!
              </p>
            </div>
            <button class="arrow right" onclick="changeCard('right')">
              &#8594;
            </button>
          </div>
    </section>

    <section class="subscribe">
        <div class="section__container subscribe__container">
            <h2>Subscribe to our newsletter for updates</h2>
            <div>
                <p>For the best recommendation of your. Please subscribe us.</p>
                <form action="/">
                    <input type="text" placeholder="Enter Your Email Here" />
                    <button class="btn">SUBSCRIBE</button>
                </form>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="section__container footer__container">
            <div class="footer__col">
                <div class="footer__logo">
                    <a href="#" class="logo">Travel<span>Dream</span></a>
                </div>
                <p>
                    Explore the world's best destinations, enjoy unbeatable prices, and
                    book your perfect getaway instantly.
                </p>
                <h4>CONNECT WITH US</h4>
                <ul class="footer__socials">
                    <li>
                        <a href="#"><i class="ri-twitter-fill"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="ri-google-fill"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="ri-linkedin-fill"></i></a>
                    </li>
                </ul>
            </div>
            <div class="footer__col">
                <h4>QUICK LINKS</h4>
                <ul class="footer__links">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Blogs</a></li>
                    <li><a href="#">Testimonials</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h4>DESTINATIONS</h4>
                <ul class="footer__links">
                    <li><a href="#">China</a></li>
                    <li><a href="#">Venezuela</a></li>
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
            Copyright © 2024 Web Design Mastery. All rights reserved.
        </div>
    </footer>

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="main.js"></script>
</body>

</html>
>?