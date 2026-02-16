<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email)) {
    try {
        // Find or Create user entry to get numeric ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data->email]);
        $row = $stmt->fetch();
        
        if ($row) {
            echo json_encode(["success" => true, "userId" => $row['id']]);
        } else {
            // Create placeholder if truly missing (should have been created during reg, but safety first)
            $ins = $pdo->prepare("INSERT INTO users (email, coins) VALUES (?, 0)");
            $ins->execute([$data->email]);
            echo json_encode(["success" => true, "userId" => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
}
?>
