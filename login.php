
<?php

session_start();

require_once __DIR__ . "/includes/db.php";

$pageTitle = "Login | EmojiAI";
$pageStyles = ["css/login.css"];

$errors = [];
$loginIdentifier = "";

// Generate CSRF token.
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Redirect an already logged-in user.
if (!empty($_SESSION["logged_in"]) && !empty($_SESSION["UserID"])) {
    header("Location: dashboard.php");
    exit;
}

// ==========================================
// PROCESS LOGIN
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $loginIdentifier = trim($_POST["login_identifier"] ?? "");
    $password = $_POST["password"] ?? "";
    $csrfToken = $_POST["csrf_token"] ?? "";

    // Validate CSRF token.
    if (
        !isset($_SESSION["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $csrfToken)
    ) {
        $errors[] = "Your session has expired. Refresh the page and try again.";
    }

    if ($loginIdentifier === "") {
        $errors[] = "Enter your username or email address.";
    }

    if ($password === "") {
        $errors[] = "Enter your password.";
    }

    if (empty($errors)) {

        try {

            // Use the EXACT column names from your database.
            $sql = "
                SELECT user_id, Username, Email, PasswordHash
                FROM users
                WHERE Username = ? OR Email = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ss",
                $loginIdentifier,
                $loginIdentifier
            );

            $stmt->execute();

            $stmt->bind_result(
                $userId,
                $username,
                $email,
                $storedPasswordHash
            );

            $accountFound = $stmt->fetch();

            $stmt->close();

            // Verify password.
            if (
                $accountFound &&
                password_verify($password, $storedPasswordHash)
            ) {

                // Secure the session.
                session_regenerate_id(true);

                $_SESSION["UserID"] = $userId;
                $_SESSION["Username"] = $username;
                $_SESSION["Email"] = $email;
                $_SESSION["logged_in"] = true;

                // Regenerate CSRF token.
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                // Login successful.
                header("Location: dashboard.php");
                exit;

            } else {

                $errors[] = "Incorrect username/email or password.";

            }

        } catch (mysqli_sql_exception $e) {

            error_log(
                "EmojiAI LOGIN DATABASE ERROR: " .
                $e->getMessage()
            );

            $errors[] = "A database error occurred. Please check the server error log.";

        } catch (Throwable $e) {

            error_log(
                "EmojiAI LOGIN ERROR: " .
                $e->getMessage()
            );

            $errors[] = "An unexpected error occurred. Please try again.";

        }
    }
}

// ==========================================
// LOAD LOGIN PAGE
// ==========================================

require_once __DIR__ . "/includes/head.php";

?>

<main class="login-container">

    <section class="login-card">

        <div class="login-logo">
            <span class="logo-emoji">😊</span>
            <span>EmojiAI</span>
        </div>

        <div class="login-header">

            <h1>Welcome Back</h1>

            <p>
                Login to continue creating your personalised emojis.
            </p>

        </div>

        <?php if (
            isset($_GET["registered"]) &&
            in_array($_GET["registered"], ["success", "1"], true)
        ): ?>

            <div class="success-message" role="status">
                Your account was created successfully.
                You can now log in.
            </div>

        <?php endif; ?>

        <?php if (!empty($errors)): ?>

            <div class="validation-errors" role="alert">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $_SESSION["csrf_token"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
            >

            <div class="form-group">

                <label for="login_identifier">
                    Username or Email Address
                </label>

                <input
                    type="text"
                    id="login_identifier"
                    name="login_identifier"
                    value="<?= htmlspecialchars(
                        $loginIdentifier,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    placeholder="Enter your username or email"
                    autocomplete="username"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button type="submit" class="login-button">
                Login
            </button>

        </form>

        <div class="register-link">

            <p>
                Don't have an account?
                <a href="register.html">Register here</a>
            </p>

        </div>

        <div class="login-footer">
            <p>Express yourself differently ✨</p>
        </div>

    </section>

</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
