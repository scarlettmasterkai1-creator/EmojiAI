
<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/includes/db.php";

try {

    // Check login session.
    if (!isset($_SESSION["UserID"])) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "Please log in before saving emojis."
        ]);
        exit;
    }

    $userId = (int) $_SESSION["UserID"];

    // Read JSON request.
    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($data)) {
        throw new Exception("Invalid JSON received.");
    }

    $emotion = trim($data["emotion"] ?? "");
    $emoji = trim($data["emoji"] ?? "");
    $confidence = $data["confidence"] ?? null;

    $allowedEmotions = [
        "Happy",
        "Sad",
        "Angry",
        "Surprise",
        "Neutral"
    ];

    if (!in_array($emotion, $allowedEmotions, true)) {
        throw new Exception("Invalid emotion received.");
    }

    if ($emoji === "") {
        throw new Exception("Generated emoji is missing.");
    }

    if (
        !is_numeric($confidence) ||
        !is_finite((float) $confidence) ||
        (float) $confidence < 0 ||
        (float) $confidence > 100
    ) {
        throw new Exception("Invalid confidence value.");
    }

    $confidence = (float) $confidence;

    // Begin database transaction.
    $conn->begin_transaction();

    // Save emotion result.
    $stmt = $conn->prepare(
        "INSERT INTO emotion_results
        (UserID, emotion, confidence)
        VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "isd",
        $userId,
        $emotion,
        $confidence
    );

    $stmt->execute();

    $resultId = $conn->insert_id;

    $stmt->close();

    // Save generated emoji.
    $stmt2 = $conn->prepare(
        "INSERT INTO emojis
        (result_id, emotion, emoji_symbol)
        VALUES (?, ?, ?)"
    );

    $stmt2->bind_param(
        "iss",
        $resultId,
        $emotion,
        $emoji
    );

    $stmt2->execute();

    $stmt2->close();

    // Commit both database records.
    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Emoji saved successfully.",
        "result_id" => $resultId
    ]);

} catch (Throwable $e) {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }

    error_log("EmojiAI Save Error: " . $e->getMessage());

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
