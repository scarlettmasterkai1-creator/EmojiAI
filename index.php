<?php
session_start();

$pageTitle = "Welcome to EmojiAI";

// Check whether registration was successful
$registered = (
    isset($_GET["registered"]) &&
    $_GET["registered"] === "success"
);

require __DIR__ . "/includes/head.php";

require_once __DIR__ . "/includes/head.php";
?>

<main class="page-wrap">

    <section class="card">
        <h1>AI Emoji Creator</h1>

        <p>
            Create personalised emojis using
            artificial intelligence.
        </p>

        <div class="button-group">
            <a href="login.php" class="btn btn-primary">
                Login
            </a>

            <a href="register.html" class="btn btn-secondary">
                Register
            </a>
        </div>
    </section>

</main>

<?php
require __DIR__ . "/includes/footer.php";
?>
