<?php
/**
 * MedMock - Import All CSV Files from Folder
 * Can be run via CLI (`php database/import_all_csvs.php`) or accessed via browser by admin.
 */

session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/csv_importer.php";

// If accessed via browser, verify admin rights
if (php_sapi_name() !== 'cli') {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: " . url("auth/login.php"));
        exit();
    }
}

$conn = getConnection();
$uploadDir = __DIR__ . '/../csv_uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$csvFiles = glob($uploadDir . '/*.csv');
if (empty($csvFiles)) {
    // Also check uppercase .CSV
    $csvFiles = glob($uploadDir . '/*.CSV');
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    include __DIR__ . "/../includes/header.php";
    include __DIR__ . "/../includes/navbar.php";
    echo '<div class="container py-5">';
    echo '<div class="d-flex justify-content-between align-items-center mb-4">';
    echo '<h2 class="fw-bold"><i class="bi bi-folder-check"></i> Bulk Batch Import CSV Files</h2>';
    echo '<a href="' . url('admin/upload_mcqs.php') . '" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to MCQ Uploads</a>';
    echo '</div>';
} else {
    echo "=== MedMock Bulk CSV Directory Importer ===\n";
}

if (empty($csvFiles)) {
    $msg = "No CSV files found in <code>csv_uploads/</code> folder. Please copy your topic CSV files into <b>d:\\xampp\\htdocs\\medmock\\csv_uploads\\</b> and try again.";
    if ($isCli) {
        echo "No CSV files found in $uploadDir\n";
    } else {
        echo '<div class="alert alert-warning">' . $msg . '</div>';
        echo '</div>';
        include __DIR__ . "/../includes/footer.php";
    }
    exit();
}

$totalImported = 0;
$totalSkipped = 0;
$fileResults = [];

foreach ($csvFiles as $file) {
    $fileName = basename($file);
    $conn->beginTransaction();
    try {
        $result = importMcqCsv($file, $conn);
        if ($result['success']) {
            $conn->commit();
            $totalImported += $result['imported'];
            $totalSkipped  += $result['skipped'];
            $fileResults[] = [
                'file' => $fileName,
                'status' => 'success',
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'reasons' => $result['reasons']
            ];
        } else {
            $conn->rollBack();
            $fileResults[] = [
                'file' => $fileName,
                'status' => 'error',
                'imported' => 0,
                'skipped' => 0,
                'message' => $result['message']
            ];
        }
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $fileResults[] = [
            'file' => $fileName,
            'status' => 'error',
            'imported' => 0,
            'skipped' => 0,
            'message' => $e->getMessage()
        ];
    }
}

if ($isCli) {
    echo "Files Processed: " . count($csvFiles) . "\n";
    echo "Total MCQs Imported: $totalImported\n";
    echo "Total Rows Skipped: $totalSkipped\n\n";
    foreach ($fileResults as $res) {
        if ($res['status'] === 'success') {
            echo "[✓] {$res['file']}: {$res['imported']} questions imported ({$res['skipped']} skipped)\n";
        } else {
            echo "[✗] {$res['file']}: Error - {$res['message']}\n";
        }
    }
} else {
    echo '<div class="alert alert-success shadow-sm p-4 mb-4">';
    echo '<h4 class="alert-heading fw-bold mb-2"><i class="bi bi-check-circle-fill"></i> Batch Import Completed!</h4>';
    echo "<p class='mb-0'>Successfully processed <b>" . count($csvFiles) . "</b> CSV files. Imported <b>$totalImported</b> questions into the MCQ pool.</p>";
    echo '</div>';

    echo '<div class="card shadow-sm border-0 mb-4">';
    echo '<div class="card-header bg-dark text-white fw-bold py-3">Import Results by File</div>';
    echo '<ul class="list-group list-group-flush">';
    foreach ($fileResults as $res) {
        echo '<li class="list-group-item d-flex justify-content-between align-items-center py-3">';
        echo '<div>';
        echo '<h6 class="mb-1 fw-bold"><i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i>' . htmlspecialchars($res['file']) . '</h6>';
        if ($res['status'] === 'success') {
            echo '<small class="text-muted">Imported: <span class="badge bg-success">' . $res['imported'] . ' MCQs</span>';
            if ($res['skipped'] > 0) {
                echo ' | Skipped: <span class="badge bg-warning text-dark">' . $res['skipped'] . ' rows</span>';
            }
            echo '</small>';
            if (!empty($res['reasons'])) {
                echo '<ul class="small text-danger mt-2 mb-0 ps-3">';
                foreach ($res['reasons'] as $reason) {
                    echo '<li>' . htmlspecialchars($reason) . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo '<small class="text-danger">Error: ' . htmlspecialchars($res['message']) . '</small>';
        }
        echo '</div>';
        echo '<div>';
        if ($res['status'] === 'success') {
            echo '<span class="badge bg-success rounded-pill px-3 py-2">Success</span>';
        } else {
            echo '<span class="badge bg-danger rounded-pill px-3 py-2">Failed</span>';
        }
        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';

    echo '<div class="text-center">';
    echo '<a href="' . url('admin/dashboard.php') . '" class="btn btn-primary btn-lg me-2"><i class="bi bi-speedometer2"></i> Admin Dashboard</a>';
    echo '<a href="' . url('admin/manage_mcqs.php') . '" class="btn btn-outline-success btn-lg"><i class="bi bi-journal-text"></i> View MCQ Question Pool</a>';
    echo '</div>';

    echo '</div>';
    include __DIR__ . "/../includes/footer.php";
}
