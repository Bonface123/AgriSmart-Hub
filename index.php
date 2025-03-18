<?php
include 'includes/header.php';
renderHeader("Welcome to AgriSmart Hub");
?>

<!-- Hero Section -->
<header class="hero text-center text-white bg-success py-5">
    <div class="container">
        <h1 class="display-4">Empowering Agriculture Through Digital Innovation</h1>
        <p class="lead">AgriSmart Hub connects farmers with trusted suppliers for easy access to farm inputs.</p>
        <a href="pages/register.php" class="btn btn-light btn-lg">Get Started</a>
    </div>
</header>

<!-- Overview Section -->
<section class="container my-5 text-center">
    <h2>What is AgriSmart Hub?</h2>
    <p class="lead">
        AgriSmart Hub is a web-based platform designed to streamline the distribution of farm inputs 
        such as fertilizers, seeds, and pesticides. By eliminating middlemen, enabling online orders, 
        and integrating M-Pesa payments, we ensure efficiency, transparency, and accessibility for farmers.
    </p>
</section>

<!-- Features Section -->
<section class="container my-5">
    <div class="row text-center">
        <div class="col-md-4">
            <h3><i class="bi bi-basket2-fill text-success"></i> Order Inputs Online</h3>
            <p>Farmers can browse, compare, and order farm inputs directly from verified suppliers.</p>
        </div>
        <div class="col-md-4">
            <h3><i class="bi bi-shop text-success"></i> Supplier Management</h3>
            <p>Suppliers can manage stock, process orders, and schedule deliveries efficiently.</p>
        </div>
        <div class="col-md-4">
            <h3><i class="bi bi-credit-card text-success"></i> Secure Transactions</h3>
            <p>Integrated with M-Pesa for seamless and transparent payments.</p>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h2>How It Works</h2>
        <div class="row mt-4">
            <div class="col-md-3">
                <h4><i class="bi bi-person-plus text-success"></i> Register</h4>
                <p>Farmers and suppliers create accounts to access the platform.</p>
            </div>
            <div class="col-md-3">
                <h4><i class="bi bi-basket text-success"></i> Browse & Order</h4>
                <p>Farmers browse available inputs, compare prices, and place orders.</p>
            </div>
            <div class="col-md-3">
                <h4><i class="bi bi-truck text-success"></i> Delivery/Pickup</h4>
                <p>Suppliers process orders, and farmers receive inputs via delivery or scheduled pickup.</p>
            </div>
            <div class="col-md-3">
                <h4><i class="bi bi-cash text-success"></i> Payment</h4>
                <p>Secure payments are processed via M-Pesa integration.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="text-center my-5">
    <h2>Join AgriSmart Hub Today!</h2>
    <p>Transform your farming experience with easy access to quality farm inputs.</p>
    <a href="pages/register.php" class="btn btn-success btn-lg">Sign Up Now</a>
</section>

<?php
include 'includes/footer.php';
renderFooter();
?>
