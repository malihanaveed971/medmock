<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/csv_importer.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();

$msg = "";
$error = "";

// Sample CSV Download Handler
if (isset($_GET['download_sample'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="medmock_mcq_sample.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option', 'explanation', 'subject']);
    fputcsv($output, [
        'A 50-year-old male presents with severe chest pain. ECG shows ST elevation in II, III, aVF. Diagnosis?',
        'Anterior MI',
        'Inferior MI',
        'Lateral MI',
        'Pericarditis',
        'Aortic Dissection',
        'B',
        'ST elevation in II, III, and aVF indicates an Inferior Wall Myocardial Infarction.',
        'Cardiology'
    ]);
    fclose($output);
    exit();
}

// Handle Single CSV File Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error. Please select a valid CSV file.";
    } else {
        $conn->beginTransaction();
        try {
            $result = importMcqCsv($file['tmp_name'], $conn);
            if ($result['success']) {
                $conn->commit();
                $imported = $result['imported'];
                $skipped  = $result['skipped'];
                if ($imported > 0) {
                    $msg = "Successfully uploaded and imported <b>$imported</b> questions into the MCQ pool!";
                    if ($skipped > 0) {
                        $msg .= "<br><small class='text-muted'>($skipped rows were skipped due to missing or invalid required fields)</small>";
                    }
                } else {
                    $error = "Import completed, but <b>0 questions were imported</b> (out of $skipped data rows processed).<br><br>";
                    if (!empty($result['debug'])) {
                        $d = $result['debug'];
                        $error .= "<b>Detected File Delimiter:</b> " . htmlspecialchars($d['delimiter']) . "<br>";
                        if (!empty($d['headers'])) {
                            $error .= "<b>Detected Columns in CSV (Row 1):</b><ol class='mb-2 small ps-3'>";
                            foreach ($d['headers'] as $idx => $h) {
                                $error .= "<li>Column $idx: <code>" . htmlspecialchars($h) . "</code></li>";
                            }
                            $error .= "</ol>";
                        }
                    }
                    if (!empty($result['reasons'])) {
                        $error .= "<b>Sample row error details:</b><ul class='mb-0 small'>" . implode('', array_map(function($r) { return "<li>" . htmlspecialchars($r) . "</li>"; }, $result['reasons'])) . "</ul>";
                    }
                }
            } else {
                $conn->rollBack();
                $error = "CSV Import Error: " . htmlspecialchars($result['message']);
            }
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = "CSV Import Error: " . $e->getMessage();
        }
    }
}

// Count files in csv_uploads directory
$folderPath = __DIR__ . '/../csv_uploads';
$folderCsvs = is_dir($folderPath) ? glob($folderPath . '/*.{csv,CSV}', GLOB_BRACE) : [];
$folderCsvCount = count($folderCsvs);

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-cloud-upload"></i> Bulk Upload MCQs to Question Pool</h2>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Admin Dashboard
        </a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Batch Folder Importer Card -->
    <div class="card shadow-sm border-0 mb-4 bg-gradient bg-light text-dark">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-folder-fill text-warning me-2"></i> Import Multiple CSV Files from Folder</h4>
                <p class="text-muted mb-0">
                    Place your topic-wise CSV files in <code>d:\xampp\htdocs\medmock\csv_uploads\</code>.
                    Currently detected: <b><?php echo $folderCsvCount; ?> CSV file(s)</b>.
                </p>
            </div>
            <div>
                <a href="<?php echo url('database/import_all_csvs.php'); ?>" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm">
                    <i class="bi bi-play-circle-fill me-1"></i> Batch Import All (<?php echo $folderCsvCount; ?> Files)
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet"></i> Upload Single CSV File</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" action="upload_mcqs.php">
                        <div class="mb-4">
                            <label for="csv_file" class="form-label fw-bold">Select CSV File (.csv)</label>
                            <input class="form-control form-control-lg" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text">File must strictly be in standard CSV format with correct header layout.</div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                            <i class="bi bi-cloud-upload-fill"></i> Upload & Import MCQs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-primary"></i> CSV File Format Requirements</h5>
                    <p class="text-muted">Your CSV file must include the following header columns in exact order:</p>
                    <ol class="small text-muted mb-4 ps-3">
                        <li><b>question</b> - Question statement text</li>
                        <li><b>option_a</b> - Option A text</li>
                        <li><b>option_b</b> - Option B text</li>
                        <li><b>option_c</b> - Option C text</li>
                        <li><b>option_d</b> - Option D text</li>
                        <li><b>option_e</b> - Option E text (Optional)</li>
                        <li><b>correct_option</b> - A, B, C, D, or E</li>
                        <li><b>explanation</b> - Answer rationale/explanation text</li>
                        <li><b>subject</b> - Specialty / Subject (e.g. Cardiology)</li>
                    </ol>

                    <a href="upload_mcqs.php?download_sample=1" class="btn btn-outline-primary w-100 fw-bold">
                        <i class="bi bi-download"></i> Download Sample CSV Template
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>
