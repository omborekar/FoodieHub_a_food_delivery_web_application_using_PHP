<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FoodieHub - Online Food Ordering</title>
  <meta name="description" content="FoodieHub: Your favorite food delivered fast. Order online from the best restaurants near you.">
  <link rel="icon" href="public/assets/img/favicon.png" type="image/png">
  <link rel="stylesheet" href="public/css/navbar.css">
  <link rel="stylesheet" href="public/css/index.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">




</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <div class="logo">FoodieHub</div>
    <div class="nav-buttons">
      <button onclick="window.location.href='login.php'">Login</button>
      <button onclick="window.location.href='signup.php'">Signup</button>
    </div>
  </div>

  <!-- Hero Section (Keep as is) -->
  <div class="hero">
    <h1>Welcome to FoodieHub</h1>
    <p>Order your favorite meals online with fast delivery and amazing discounts!</p>
  </div>

  <!-- Featured Dishes -->
  <div class="section" style="background-color: #fff;">
    <h2>Popular Dishes</h2>
    <div class="menu-preview">
      <div class="dish-card">
        <img src="public/assets/img/pizza.jpg" alt="Pizza">
        <div class="info">
          <h4>Cheesy Margherita</h4>
          <p>Fresh dough, tomato sauce, mozzarella</p>
        </div>
      </div>
      <div class="dish-card">
        <img src="public/assets/img/burger.jpg" alt="Burger">
        <div class="info">
          <h4>Classic Veg Burger</h4>
          <p>Grilled patty, lettuce, special sauce</p>
        </div>
      </div>
      <div class="dish-card">
        <img src="public/assets/img/biryani.jpg" alt="Biryani">
        <div class="info">
          <h4>Hyderabadi Biryani</h4>
          <p>Spiced rice with aromatic herbs</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Why Choose Us -->
  <div class="section" style="background-color: #f9f9f9;">
    <h2>Why Choose FoodieHub?</h2>
    <div class="features">
      <div class="feature-box">
        <img src="public/assets/img/fastdelivery.jpg" alt="Fast Delivery">
        <h4>Fast Delivery</h4>
        <p>Get your meals delivered in under 30 minutes.</p>
      </div>
      <div class="feature-box">
        <img src="public/assets/img/topquality.jpg" alt="Top Quality">
        <h4>Top Quality</h4>
        <p>We use only the freshest ingredients.</p>
      </div>
      <div class="feature-box">
        <img src="public/assets/img/greatdeals.jpg" alt="Great Deals">
        <h4>Great Deals</h4>
        <p>Enjoy offers and discounts every day.</p>
      </div>
    </div>
  </div>

  <!-- Testimonials -->
  <div class="section" style="background-color: #fff;">
    <h2>What Our Customers Say</h2>
    <div class="features">
      <div class="testimonial">
        <p>"Super quick delivery and the food is always hot and delicious. Highly recommended!"</p>
        <strong>- Aditi, Mumbai</strong>
      </div>
      <div class="testimonial">
        <p>"The best biryani I've ever ordered online. You’ve earned a loyal customer!"</p>
        <strong>- Rahul, Hyderabad</strong>
      </div>
    </div>
  </div>

  <!-- Newsletter -->
  <div class="section" style="background-color: #f9f9f9;">
    <h2>Get Exclusive Offers</h2>
    <div class="newsletter">
      <input type="email" placeholder="Enter your email">
      <button>Subscribe</button>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    &copy; 2025 FoodieHub. All rights reserved. | 
    <a href="about.php">About Us</a> | 
    <a href="contact.php">Contact</a>
  </div>

</body>
</html>
