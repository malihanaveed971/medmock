<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-grid-3x3-gap-fill"></i> Question Palette</h6>
        <span class="badge bg-light text-dark"><?php echo count($questions); ?> Questions</span>
    </div>
    <div class="card-body p-3">

        <!-- Legend -->
        <div class="d-flex flex-wrap justify-content-between small text-muted mb-3 pb-2 border-bottom">
            <span><i class="bi bi-square-fill text-success"></i> Answered</span>
            <span><i class="bi bi-square-fill text-warning"></i> Review</span>
            <span><i class="bi bi-square-fill text-secondary"></i> Unanswered</span>
        </div>

        <div class="d-flex flex-wrap gap-2 overflow-auto" style="max-height: 480px;">
            <?php foreach ($questions as $index => $qItem): 
                $btnClass = "btn-outline-secondary";

                if (!empty($qItem['is_review']) && $qItem['is_review'] == 1) {
                    $btnClass = "btn-warning text-dark fw-bold";
                } elseif (!empty($qItem['selected_option'])) {
                    $btnClass = "btn-success fw-bold";
                }

                $activeBorder = ($index == $current) ? "border border-3 border-dark shadow" : "";
            ?>
                <a href="question.php?q=<?php echo $index; ?>"
                   class="btn <?php echo $btnClass; ?> <?php echo $activeBorder; ?> question-btn"
                   style="width: 45px; height: 42px; line-height: 26px;">
                    <?php echo $index + 1; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <hr class="my-3">

        <a href="results.php" class="btn btn-danger w-100 btn-lg fw-bold" onclick="return confirm('Are you sure you want to finish and submit your exam now?');">
            <i class="bi bi-check-circle-fill"></i> Finish & Submit Test
        </a>

    </div>
</div>