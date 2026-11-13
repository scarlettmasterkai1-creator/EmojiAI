
<?php
session_start();

// Check the same session variable used by login.php
if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit;
}

// Connect to the database once
require_once __DIR__ . "/includes/db.php";

$userId = (int) $_SESSION["UserID"];

// Page configuration
$pageTitle = "My Stickers | EmojiAI";
$pageStyles = [
    "css/dashboard.css",
    "css/stickers.css"
];

// Retrieve only the logged-in user's saved assets
$stmt = $conn->prepare(
    "SELECT asset_id, asset_type, file_path, created_at
     FROM emoji_assets
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$assets = $stmt->get_result();

require_once __DIR__ . "/includes/head.php";
require_once __DIR__ . "/includes/navbar.php";
?>

<main class="dashboard-main">

    <header class="dashboard-topbar">
        <div>
            <h1>✨ My Emoji Gallery</h1>
            <p>Your saved custom stickers and animations.</p>
        </div>
    </header>

    <section class="stickers-section">

        <div class="stickers-heading">
            <div>
                <h2>My Creations</h2>
                <p>View and download your personalised emojis.</p>
            </div>

            <a href="emoji-studio.php" class="create-sticker-button">
                + Create New Emoji
            </a>
        </div>

        <?php if ($assets->num_rows > 0): ?>

            <div class="gallery-grid">

                <?php while ($asset = $assets->fetch_assoc()): ?>

                    <article class="gallery-item">

                        <div class="gallery-image">
                            <img
                                src="<?= htmlspecialchars($asset["file_path"], ENT_QUOTES, "UTF-8") ?>"
                                alt="My EmojiAI creation"
                                loading="lazy"
                            >
                        </div>

                        <div class="gallery-details">

                            <h3>
                                <?= htmlspecialchars(
                                    strtoupper($asset["asset_type"]),
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </h3>

                            <small>
                                <?= htmlspecialchars(
                                    $asset["created_at"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </small>

                            <a
                                href="<?= htmlspecialchars($asset["file_path"], ENT_QUOTES, "UTF-8") ?>"
                                download
                                class="download-sticker-button"
                            >
                                <span>↓</span> Download
                            </a>

                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty-gallery">

                <div class="empty-gallery-icon">😊</div>

                <h2>Your gallery is empty</h2>

                <p>
                    You haven't saved any emojis or stickers yet.
                    Visit Emoji Studio to create your first one.
                </p>

                <a href="emoji-studio.php" class="create-sticker-button">
                    Create Your First Emoji
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php
$stmt->close();
require_once __DIR__ . "/includes/footer.php";
?>
