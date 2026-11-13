
<?php
session_start();


if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true ||
    !isset($_SESSION["UserID"])
) {
    header("Location: login.php");
    exit();
}

$pageTitle = "EmojiAI Dashboard";
$pageStyles = ["css/dashboard.css"];

require __DIR__ . "/includes/head.php";
require __DIR__ . "/includes/navbar.php";

// Get the logged-in user's name if available.
$username = $_SESSION["Username"] ?? "Creator";
?>

<main class="dashboard-page">

    <!-- Main Dashboard Layout -->
    <div class="instagram-dashboard">

        <!-- LEFT SIDEBAR -->
        <aside class="dashboard-sidebar">

            <div class="sidebar-logo">
                <span class="logo-icon">😊</span>
                <span>EmojiAI</span>
            </div>

            <nav class="sidebar-navigation">

                <a href="dashboard.php" class="sidebar-link active">
                    <span>🏠</span>
                    <span>Home</span>
                </a>

                <a href="capture.php" class="sidebar-link">
                    <span>📸</span>
                    <span>Create Emoji</span>
                </a>

                <a href="history.php" class="sidebar-link">
                    <span>🕘</span>
                    <span>My History</span>
                </a>

                <a href="emoji-studio.php" class="sidebar-link">
                    <span>🎨</span>
                    <span>Emoji Studio</span>
                </a>

                <a href="stickers.php" class="sidebar-link">
                    <span>✨</span>
                    <span>My Stickers</span>
                </a>

                <a href="reports.php" class="sidebar-link">
                    <span>📊</span>
                    <span>Reports</span>
                </a>

            </nav>

            <div class="sidebar-bottom">
                <a href="logout.php" class="sidebar-link logout-link">
                    <span>🚪</span>
                    <span>Logout</span>
                </a>
            </div>

        </aside>


        <!-- MAIN CONTENT -->
        <section class="dashboard-main">

            <!-- TOP NAVIGATION -->
            <header class="dashboard-topbar">

                <div class="mobile-logo">
                    😊 EmojiAI
                </div>

                <div class="dashboard-search">
                    <span>🔍</span>
                    <input
                        type="search"
                        id="dashboardSearch"
                        placeholder="Search EmojiAI..."
                    >
                </div>

                <div class="topbar-profile">
                    <span class="notification-icon">🔔</span>

                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>

                    <span class="profile-name">
                        <?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?>
                    </span>
                </div>

            </header>


            <!-- WELCOME SECTION -->
            <section class="welcome-section">

                <div class="welcome-content">

                    <span class="welcome-label">
                        YOUR PERSONAL EMOJI CREATOR
                    </span>

                    <h1>
                        Welcome back,
                        <?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?>!
                    </h1>

                    <p>
                        Express yourself in a whole new way.
                        Capture your facial expressions and transform
                        them into personalised emojis with EmojiAI.
                    </p>

                    <a href="capture.php" class="primary-dashboard-btn">
                        📸 Create Your Emoji
                        <span>→</span>
                    </a>

                </div>

                <div class="welcome-illustration">
                    <span class="floating-emoji emoji-one">😄</span>
                    <span class="floating-emoji emoji-two">😍</span>
                    <span class="floating-emoji emoji-three">😎</span>
                    <span class="floating-emoji emoji-four">🥳</span>
                    <div class="main-emoji">😊</div>
                </div>

            </section>


            <!-- QUICK ACCESS -->
            <section class="dashboard-section">

                <div class="section-heading">
                    <div>
                        <h2>Explore EmojiAI</h2>
                        <p>Everything you need to create and manage emojis.</p>
                    </div>
                </div>

                <div class="dashboard-feature-grid">

                    <a href="capture.php" class="feature-card">

                        <div class="feature-icon camera-icon">📸</div>

                        <h3>Generate Emoji</h3>

                        <p>
                            Use your camera to capture your facial
                            expression and generate an emoji.
                        </p>

                        <span class="feature-arrow">Create now →</span>

                    </a>


                    <a href="emoji-studio.php" class="feature-card">

                        <div class="feature-icon studio-icon">🎨</div>

                        <h3>Emoji Studio</h3>

                        <p>
                            Customise your emojis with different
                            colours, expressions and animations.
                        </p>

                        <span class="feature-arrow">Open studio →</span>

                    </a>


                    <a href="stickers.php" class="feature-card">

                        <div class="feature-icon sticker-icon">✨</div>

                        <h3>Stickers & GIFs</h3>

                        <p>
                            View and manage your personalised
                            emoji stickers and GIF creations.
                        </p>

                        <span class="feature-arrow">View creations →</span>

                    </a>


                    <a href="history.php" class="feature-card">

                        <div class="feature-icon history-icon">🕘</div>

                        <h3>Emoji History</h3>

                        <p>
                            Browse your previously generated emojis
                            and revisit your creations.
                        </p>

                        <span class="feature-arrow">View history →</span>

                    </a>

                </div>

            </section>


            <!-- INSPIRATION SECTION -->
            <section class="dashboard-section inspiration-section">

                <div class="section-heading">
                    <div>
                        <h2>Express Your Mood</h2>
                        <p>Different expressions, personalised emojis.</p>
                    </div>
                </div>

                <div class="mood-grid">

                    <div class="mood-card">
                        <span>😄</span>
                        <h3>Happy</h3>
                    </div>

                    <div class="mood-card">
                        <span>😢</span>
                        <h3>Sad</h3>
                    </div>

                    <div class="mood-card">
                        <span>😠</span>
                        <h3>Angry</h3>
                    </div>

                    <div class="mood-card">
                        <span>😲</span>
                        <h3>Surprised</h3>
                    </div>

                    <div class="mood-card">
                        <span>😐</span>
                        <h3>Neutral</h3>
                    </div>

                </div>

            </section>


            <!-- BOTTOM INFORMATION -->
            <footer class="dashboard-footer">

                <div>
                    <strong>😊 EmojiAI</strong>
                    <p>Turn your expressions into personalised emojis.</p>
                </div>

                <p>Express yourself differently. ✨</p>

            </footer>

        </section>

    </div>

</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
