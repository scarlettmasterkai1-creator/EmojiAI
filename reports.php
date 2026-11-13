
<?php

session_start();

// ==========================================
// CHECK USER LOGIN
// ==========================================

if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// DATABASE CONNECTION
// ==========================================

require_once __DIR__ . "/includes/db.php";

$userId = (int) $_SESSION["UserID"];

$reports = [];
$totalResults = 0;
$overallConfidence = 0;
$errors = [];

// ==========================================
// RETRIEVE EMOTION REPORTS
// ==========================================

try {

    $sql = "
        SELECT
            emotion,
            COUNT(*) AS total,
            AVG(confidence) AS average_confidence
        FROM emotion_results
        WHERE UserID = ?
        GROUP BY emotion
        ORDER BY total DESC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $userId);

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $reports[] = $row;

        $totalResults += (int) $row["total"];
    }

    $stmt->close();

    // ======================================
    // CALCULATE OVERALL CONFIDENCE
    // ======================================

    if ($totalResults > 0) {

        $confidenceStmt = $conn->prepare("
            SELECT AVG(confidence) AS overall_confidence
            FROM emotion_results
            WHERE UserID = ?
        ");

        $confidenceStmt->bind_param("i", $userId);

        $confidenceStmt->execute();

        $confidenceResult = $confidenceStmt->get_result();

        $confidenceData = $confidenceResult->fetch_assoc();

        $overallConfidence = (float) (
            $confidenceData["overall_confidence"] ?? 0
        );

        $confidenceStmt->close();
    }

} catch (mysqli_sql_exception $e) {

    error_log(
        "EmojiAI reports database error: " .
        $e->getMessage()
    );

    $errors[] = "Unable to load your reports. Please try again.";

}

// ==========================================
// PAGE CONFIGURATION
// ==========================================

$pageTitle = "EmojiAI Reports";
$pageStyles = ["css/reports.css"];
require_once __DIR__ . "/includes/head.php";
require_once __DIR__ . "/includes/navbar.php";

?>

<main class="page-content">

    <header class="topbar">

        <div>
            <h2>📊 Emoji Reports</h2>

            <p>
                View your personalised EmojiAI activity.
            </p>
        </div>

        <a href="dashboard.php" class="primary-button">
            ← Dashboard
        </a>

    </header>

    <section class="report-section">

        <div class="report-heading">

            <h1>Your Emoji Activity Report</h1>

            <p>
                Review your recorded expression classifications,
                model confidence and creation totals.
            </p>

        </div>

        <?php if (!empty($errors)): ?>

            <div class="validation-errors" role="alert">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <!-- REPORT SUMMARY -->

            <div class="report-summary">

                <div class="report-card">

                    <span class="report-card-icon">😊</span>

                    <h3>Total Results</h3>

                    <p class="report-number">
                        <?= $totalResults ?>
                    </p>

                </div>

                <div class="report-card">

                    <span class="report-card-icon">📊</span>

                    <h3>Emotion Categories</h3>

                    <p class="report-number">
                        <?= count($reports) ?>
                    </p>

                </div>

                <div class="report-card">

                    <span class="report-card-icon">🎯</span>

                    <h3>Average Confidence</h3>

                    <p class="report-number">
                        <?= $totalResults > 0
                            ? number_format($overallConfidence, 1) . "%"
                            : "0%" ?>
                    </p>

                </div>

            </div>

            <!-- EMOTION REPORT TABLE -->

            <div class="report-table-container">

                <h2>Emotion Classification Statistics</h2>

                <?php if (empty($reports)): ?>

                    <p class="empty-report">
                        No reports available yet.
                        Create your first emoji to start generating statistics.
                    </p>

                <?php else: ?>

                    <table class="report-table">

                        <thead>

                            <tr>
                                <th>Emotion</th>
                                <th>Total Results</th>
                                <th>Average Confidence</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($reports as $report): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $report["emotion"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= (int) $report["total"] ?>
                                    </td>

                                    <td>
                                        <?= $report["average_confidence"] !== null
                                            ? number_format(
                                                (float) $report["average_confidence"],
                                                1
                                            ) . "%"
                                            : "N/A" ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

            <!-- EXPORT REPORT -->

            <div class="report-actions">

                <a
                    href="export_report.php"
                    class="primary-button"
                >
                    Download Report CSV ↓
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
