
<?php
session_start();

if (!isset($_SESSION["UserID"])) {
    http_response_code(401);
    exit("Please log in.");
}

require_once __DIR__ . "/includes/db.php";

$userId = (int) $_SESSION["UserID"];

$stmt = $conn->prepare(
    "SELECT result_id, emotion, confidence, created_at
     FROM emotion_results
     WHERE UserID = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

header("Content-Type: text/csv; charset=UTF-8");
header(
    'Content-Disposition: attachment; filename="emoji-report.csv"'
);

$output = fopen("php://output", "w");

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    "Result ID",
    "Expression",
    "Confidence",
    "Date Created"
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row["result_id"],
        $row["emotion"],
        $row["confidence"],
        $row["created_at"]
    ]);
}

fclose($output);
$stmt->close();
exit;
