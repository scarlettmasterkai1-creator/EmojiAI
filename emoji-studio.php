
<?php
session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "EmojiAI Studio";

$pageStyles = [
    "css/dashboard.css",
    "css/emoji-studio.css"
];

require __DIR__ . "/includes/head.php";
?>

<main class="studio-page">

    <header class="studio-heading">
        <a href="dashboard.php">← Dashboard</a>

        <h1>🎨 Custom Emoji Studio</h1>
        <p>Design, animate and save your personalised emojis.</p>
    </header>

    <section class="studio-layout">

        <div class="studio-preview">
            <canvas
                id="emojiCanvas"
                width="400"
                height="400">
            </canvas>
        </div>

        <div class="studio-controls">

            <label for="faceColor">Face Colour</label>
            <input type="color" id="faceColor" value="#FFD54F">

            <label for="eyes">Eyes</label>
            <select id="eyes">
                <option value="normal">Normal</option>
                <option value="happy">Happy</option>
                <option value="surprised">Surprised</option>
            </select>

            <label for="mouth">Mouth</label>
            <select id="mouth">
                <option value="smile">Smile</option>
                <option value="sad">Sad</option>
                <option value="angry">Angry</option>
                <option value="surprised">Surprised</option>
                <option value="neutral">Neutral</option>
            </select>

            <label for="animation">Animation</label>
            <select id="animation">
                <option value="none">None</option>
                <option value="bounce">Bounce</option>
                <option value="rotate">Rotate</option>
                <option value="pulse">Pulse</option>
            </select>

            <div class="studio-buttons">

                <button type="button" id="previewAnimation"
                    class="studio-action-button">
                    ▶ Preview Animation
                </button>

                <button type="button" id="downloadPNG"
                    class="studio-action-button">
                    Download PNG
                </button>

                <button type="button" id="createGIF"
                    class="studio-action-button">
                    Create GIF
                </button>

                <button type="button" id="shareSticker"
                    class="studio-action-button">
                    Share Sticker
                </button>

            </div>

            <p id="studioStatus" aria-live="polite">
                Emoji Studio ready.
            </p>

        </div>

    </section>

</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gif.js/0.2.0/gif.js"></script>

<script>
    window.EMOJI_GIF_WORKER = new URL(
        "js/vendor/gif.worker.js",
        document.baseURI
    ).href;
</script>

<script src="js/emoji-studio.js"></script>

<?php require __DIR__ . "/includes/footer.php"; ?>
