<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT id, name FROM publicity_members WHERE active = 1 ORDER BY name ASC");
        $members = $stmt->fetchAll();
        echo json_encode($members);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name)) {
            $stmt = $pdo->prepare("INSERT INTO publicity_members (name) VALUES (?)");
            if ($stmt->execute([$data->name])) {
                echo json_encode(["success" => true, "message" => "Member added"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Failed to add member"]);
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $stmt = $pdo->prepare("UPDATE publicity_members SET active = 0 WHERE id = ?");
            if ($stmt->execute([$data->id])) {
                echo json_encode(["success" => true, "message" => "Member removed"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Failed to remove member"]);
            }
        }
        break;
        
    default:
        http_response_code(405);
        break;
}
?>
