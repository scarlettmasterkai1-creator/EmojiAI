
<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/includes/db.php";

// ========================================
// ONLY ACCEPT POST REQUESTS
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "errors" => ["Invalid request method."]
    ]);

    exit;
}

// ========================================
// RECEIVE FORM DATA
// ========================================

$firstname = trim($_POST["firstname"] ?? "");
$lastname  = trim($_POST["lastname"] ?? "");
$email     = strtolower(trim($_POST["email"] ?? ""));
$username  = trim($_POST["username"] ?? "");
$password  = $_POST["password"] ?? "";

$errors = [];

// ========================================
// FIRST NAME VALIDATION
// ========================================

if ($firstname === "") {

    $errors[] = "First name is required.";

} elseif (strlen($firstname) > 100) {

    $errors[] = "First name cannot exceed 100 characters.";

} elseif (!preg_match("/^[\p{L}][\p{L} '-]*$/u", $firstname)) {

    $errors[] = "First name contains invalid characters.";
}

// ========================================
// LAST NAME VALIDATION
// ========================================

if ($lastname === "") {

    $errors[] = "Last name is required.";

} elseif (strlen($lastname) > 100) {

    $errors[] = "Last name cannot exceed 100 characters.";

} elseif (!preg_match("/^[\p{L}][\p{L} '-]*$/u", $lastname)) {

    $errors[] = "Last name contains invalid characters.";
}

// ========================================
// EMAIL VALIDATION
// ========================================

if ($email === "") {

    $errors[] = "Email address is required.";

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors[] = "Enter a valid email address.";

} elseif (strlen($email) > 150) {

    $errors[] = "Email address cannot exceed 150 characters.";
}

// ========================================
// USERNAME VALIDATION
// ========================================

if ($username === "") {

    $errors[] = "Username is required.";

} elseif (strlen($username) < 3 || strlen($username) > 100) {

    $errors[] = "Username must contain 3 to 100 characters.";

} elseif (!preg_match("/^[a-zA-Z0-9_ ]+$/", $username)) {

    $errors[] = "Username contains invalid characters.";
}

// ========================================
// PASSWORD VALIDATION
// ========================================

if (!is_string($password) || strlen($password) < 8) {

    $errors[] = "Password must contain at least 8 characters.";

} else {

    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password needs an uppercase letter.";
    }

    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password needs a lowercase letter.";
    }

    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password needs a number.";
    }

    if (!preg_match("/[^a-zA-Z0-9]/", $password)) {
        $errors[] = "Password needs a special character.";
    }
}

// ========================================
// RETURN VALIDATION ERRORS
// ========================================

if (!empty($errors)) {

    http_response_code(422);

    echo json_encode([
        "success" => false,
        "errors" => $errors
    ]);

    exit;
}

// ========================================
// CHECK DATABASE AND REGISTER USER
// ========================================

try {

    // Check whether email or username already exists.
    $check = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE Email = ? OR Username = ?
        LIMIT 1
    ");

    $check->bind_param(
        "ss",
        $email,
        $username
    );

    $check->execute();

    $check->store_result();

    $accountExists = ($check->num_rows > 0);

    $check->close();

    // Stop if account already exists.
    if ($accountExists) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "errors" => [
                "This email address or username is already registered."
            ]
        ]);

        exit;
    }

    // Hash password securely.
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    if ($passwordHash === false) {
        throw new RuntimeException("Password hashing failed.");
    }

    // Insert user using your ACTUAL database columns.
    $stmt = $conn->prepare("
        INSERT INTO users
        (
            FirstName,
            LastName,
            Username,
            Email,
            PasswordHash
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssss",
        $firstname,
        $lastname,
        $username,
        $email,
        $passwordHash
    );

    $stmt->execute();

    $stmt->close();

    // Return successful registration.
    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Your EmojiAI account has been created successfully!"
    ]);

    exit;

} catch (mysqli_sql_exception $e) {

    error_log(
        "EmojiAI registration database error: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "errors" => [
            "Registration could not be completed. Please try again."
        ]
    ]);

    exit;

} catch (Throwable $e) {

    error_log(
        "EmojiAI registration error: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "errors" => [
            "An unexpected error occurred. Please try again."
        ]
    ]);

    exit;
}
