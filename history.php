
<?php
session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/includes/db.php";

$userId = (int) $_SESSION["UserID"];

// Retrieve the logged-in user's emotion history
// Retrieve the logged-in user's emotion history
$sql = "SELECT result_id, emotion, confidence, image_path
        FROM emotion_results
        WHERE UserID = ?
        ORDER BY result_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$pageTitle = "Emoji History";

$pageStyles = [
    "css/dashboard.css",
    "css/history.css"
];

require_once __DIR__ . "/includes/head.php";
require_once __DIR__ . "/includes/navbar.php";

// Convert emotion names into emojis
$emojiMap = [
    "Happy"    => "😄",
    "Sad"      => "😢",
    "Angry"    => "😠",
    "Surprise" => "😲",
    "Neutral"  => "😐"
];
?>

<main class="dashboard-main">

    <div class="history-container">

        <header class="history-header">
            <div>
                <h1>Emoji History</h1>
                <p>
                    View your previously analysed facial expressions
                    and generated emojis.
                </p>
            </div>
        </header>

        <section class="history-card">

            <div class="history-card-heading">
                <h2>Your Previous Results</h2>
                <span>
                    <?= $result->num_rows ?> Results
                </span>
            </div>

            <?php if ($result->num_rows > 0): ?>

                <div class="history-table-wrapper">

                    <table class="history-table">

                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Emotion</th>
                                <th>Emoji</th>
                                <th>Confidence</th>
                                <th>Captured Image</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php while ($row = $result->fetch_assoc()): ?>

                                <?php
                                $emotion = $row["emotion"];
                                $emoji = $emojiMap[$emotion] ?? "😊";
                                ?>

                                <tr>

                                    <td>
                                        #<?= (int) $row["result_id"] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($emotion) ?>
                                    </td>

                                    <td class="history-emoji">
                                        <?= $emoji ?>
                                    </td>

                                    <td>
                                        <?= $row["confidence"] !== null
                                            ? htmlspecialchars($row["confidence"]) . "%"
                                            : "Not available" ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($row["image_path"])): ?>
                                            <img
                                                src="<?= htmlspecialchars($row["image_path"]) ?>"
                                                alt="Captured facial expression"
                                                class="history-image"
                                            >
                                        <?php else: ?>
                                            No image
                                        <?php endif; ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="history-empty">

                    <span>🕘</span>

                    <h3>No Emoji History Yet</h3>

                    <p>
                        Your generated emoji results will appear here
                        after you use the Capture Emoji feature.
                    </p>

                    <a href="capture.php" class="history-create-button">
                        Create Your First Emoji
                    </a>

                </div>

            <?php endif; ?>

        </section>

    </div>

</main>

<?php
$stmt->close();
require_once __DIR__ . "/includes/footer.php";
?>
