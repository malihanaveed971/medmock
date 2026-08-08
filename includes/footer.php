<!-- Footer -->
<footer class="bg-dark text-white mt-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <h5>MedMock</h5>
                <p class="mb-0">
                    Practice FCPS Part-II MCQs with real exam-like experience.
                </p>
            </div>

            <div class="col-md-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url('index.php'); ?>" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="<?php echo url('auth/login.php'); ?>" class="text-white text-decoration-none">Login</a></li>
                    <li><a href="<?php echo url('auth/register.php'); ?>" class="text-white text-decoration-none">Register</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h6>Contact</h6>
                <p class="mb-1">
                    <i class="bi bi-envelope-fill"></i>
                    support@medmock.com
                </p>
                <p class="mb-0">
                    <i class="bi bi-globe"></i>
                    www.medmock.com
                </p>
            </div>
        </div>

        <hr class="border-secondary">

        <div class="text-center">
            © <?php echo date('Y'); ?> MedMock. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>