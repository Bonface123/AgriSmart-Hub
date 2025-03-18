<?php
function renderFooter() {
?>
    <!-- Footer Start -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white">About Agrismart Hub</h5>
                    <p>Innovating Agriculture with Smart Farming Solutions. We provide digital tools, training, and resources to empower modern farmers.</p>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="text-white">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-light">About</a></li>
                        <li><a href="services.php" class="text-light">Services</a></li>
                        <li><a href="projects.php" class="text-light">Projects</a></li>
                        <li><a href="blog.php" class="text-light">Blog</a></li>
                        <li><a href="contact.php" class="text-light">Contact</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white">Contact</h5>
                    <p><i class="fa fa-map-marker-alt me-2"></i> Nairobi, Kenya</p>
                    <p><i class="fa fa-phone-alt me-2"></i> +254 700 123 456</p>
                    <p><i class="fa fa-envelope me-2"></i> info@agrismarthub.com</p>
                </div>

                <!-- Social Media -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white">Follow Us</h5>
                    <a href="#" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <hr class="my-3 border-light">

            <div class="text-center">
                <p class="mb-0">© <?php echo date("Y"); ?> Agrismart Hub. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    <!-- Footer End -->

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
}
?>
