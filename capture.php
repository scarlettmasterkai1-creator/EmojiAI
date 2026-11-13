
<?php

$pageTitle = "Capture Emoji";

$pageStyles = [
    "css/capture.css"
];

require_once __DIR__ . "/includes/head.php";
require_once __DIR__ . "/includes/navbar.php";

?>

<main class="capture-page">

    <section class="capture-container">

        <!-- PAGE HEADER -->
        <div class="capture-header">

            <span class="capture-label">
                EMOJIAI CREATOR
            </span>

            <h1>Create Your Emoji</h1>

            <p>
                Allow camera access, capture your facial expression
                and let EmojiAI generate your personalised emoji.
            </p>

        </div>

        <!-- CAMERA -->
        <div class="camera-container">

            <video
                id="video"
                autoplay
                playsinline
                muted>
            </video>

            <canvas id="canvas" hidden></canvas>

        </div>

        <!-- CAMERA STATUS -->
        <p id="camera-status" role="status">
            Initialising camera...
        </p>

        <!-- CAPTURE BUTTON -->
        <button
            type="button"
            id="capture"
            disabled>
            📸 Capture Emoji
        </button>

        <!-- GENERATED EMOJI RESULT -->
        <div
            class="result-container"
            id="result-container"
            hidden>

            <h2>Your Generated Emoji</h2>

            <div
                id="result-emoji"
                class="generated-emoji">
            </div>

            <h3 id="result-emotion"></h3>

            <p id="result-confidence"></p>

            <p id="result-message"></p>

            <button
                type="button"
                id="capture-again">
                Capture Again
            </button>

        </div>

        <!-- EMOJI DISPLAY TEST -->
        <div class="test-container">

            <h3>EmojiAI Display Test</h3>

            <p>
                Test whether the generated emoji result
                can appear correctly.
            </p>

            <button
                type="button"
                id="test-emoji">
                Test Emoji Display
            </button>

        </div>

    </section>

</main>

<!-- LOAD JAVASCRIPT -->
<script src="js/capture.js" defer></script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
