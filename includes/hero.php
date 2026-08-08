<section class="py-5 bg-white shadow-sm">
    <div class="container">
        <div class="row align-items-center">

            <!-- Left Side -->
            <div class="col-lg-6">
                <span class="badge bg-primary fs-6 px-3 py-2 mb-3">FCPS Part-II Mock Exam Portal</span>
                <h1 class="display-4 fw-bold mb-4 text-dark">
                    Master FCPS Part-II Examinations with Confidence
                </h1>

                <p class="lead text-secondary mb-4">
                    Practice with specialized medical MCQs in realistic timed mock exams.
                    Track your performance, review detailed wrong answer rationales, and pass your exam.
                </p>

                <div class="mb-4">
                    <p class="mb-2 fw-semibold text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> 2,000+ FCPS Part-II Question Pool</p>
                    <p class="mb-2 fw-semibold text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Dynamic 200 MCQ Random Selection per Attempt</p>
                    <p class="mb-2 fw-semibold text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Automated EasyPaisa Access Activation</p>
                    <p class="mb-2 fw-semibold text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Comprehensive Score & Wrong Answer Breakdown</p>
                </div>

                <div class="d-flex gap-3 flex-wrap">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo url('pages/dashboard.php'); ?>" class="btn btn-primary btn-lg px-4 fw-bold">
                            <i class="bi bi-speedometer2 me-1"></i> Go to Candidate Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo url('auth/register.php'); ?>" class="btn btn-primary btn-lg px-4 fw-bold">
                            <i class="bi bi-person-plus me-1"></i> Start Practicing Now
                        </a>
                        <a href="<?php echo url('auth/login.php'); ?>" class="btn btn-outline-primary btn-lg px-4 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Candidate Login
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Side -->
            <div class="col-lg-6 text-center mt-4 mt-lg-0">
                <img src="<?php echo url('assets/images/doctor.jpg'); ?>"
                     class="img-fluid rounded shadow"
                     alt="FCPS Doctor Prep"
                     onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=700&auto=format&fit=crop';">
            </div>

        </div>
    </div>
</section>
