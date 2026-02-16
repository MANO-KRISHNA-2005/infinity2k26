<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db_config.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->userId) &&
    !empty($data->events) &&
    !empty($data->academicDetails->rollNo) &&
    !empty($data->teamLeader->email)
) {
    try {
        $pdo->beginTransaction();

        $prefixes = [
            "ProZone" => "P",
            "Incognito" => "I",
            "Inveringo" => "V",
            "TechRush" => "TR",
            "Swaptics" => "S",
            "Fusion Frames" => "F",
            "GameHolix" => "G",
            "Tech Arcade" => "TA"
        ];

        // 1. DUPLICATE CHECK
        // Check if any of the participants are already registered for the selected events
        $errors = [];
        $checkSql = "SELECT event_name, roll_no, teammate_roll_no, name FROM registrations WHERE event_name = ? AND (roll_no = ? OR teammate_roll_no = ? OR (teammate_roll_no IS NOT NULL AND (roll_no = ? OR teammate_roll_no = ?)))";
        $checkStmt = $pdo->prepare($checkSql);

        $leaderRoll = $data->academicDetails->rollNo;
        $teammateRoll = $data->teamMate->rollNo ?? null;

        foreach ($data->events as $event) {
            // Check for Leader
            $checkStmt->execute([$event, $leaderRoll, $leaderRoll, $leaderRoll, $leaderRoll]);
            if ($row = $checkStmt->fetch()) {
                $errors[] = "Participant (Roll: $leaderRoll) is already registered for '$event'.";
                continue;
            }

            // Check for Teammate (if exists)
            if ($teammateRoll) {
                $checkStmt->execute([$event, $teammateRoll, $teammateRoll, $teammateRoll, $teammateRoll]);
                if ($row = $checkStmt->fetch()) {
                    $errors[] = "Teammate (Roll: $teammateRoll) is already registered for '$event'.";
                }
            }
        }

        if (!empty($errors)) {
            echo json_encode(["success" => false, "message" => implode(" ", $errors)]);
            exit;
        }

        $results = [];

        foreach ($data->events as $event) {
            $prefix = $prefixes[$event] ?? "E"; // Default E for unknown

            // Generate Sequential Team ID
            // Query for the highest existing ID with this prefix
            $stmt = $pdo->prepare("SELECT team_id FROM registrations WHERE team_id LIKE ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$prefix . "%"]);
            $last_reg = $stmt->fetch();

            $next_num = 1;
            if ($last_reg) {
                // Extract number from prefix (e.g., TR12 -> 12)
                $last_id = $last_reg['team_id'];
                $num_part = substr($last_id, strlen($prefix));
                if (is_numeric($num_part)) {
                    $next_num = (int)$num_part + 1;
                }
            }

            $team_id = $prefix . $next_num;
            $results[$event] = $team_id;

            $sql = "INSERT INTO registrations (
                        team_id, event_name, roll_no, degree, year, department, 
                        name, email, phone, 
                        teammate_name, teammate_email, teammate_roll_no, teammate_phone,
                        firebase_doc_id, publicity_member, slot, attendance_status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                $team_id,
                $event,
                $data->academicDetails->rollNo,
                $data->academicDetails->degree,
                $data->academicDetails->year,
                $data->academicDetails->department,
                $data->teamLeader->name,
                $data->teamLeader->email,
                $data->teamLeader->phone,
                $data->teamMate->name ?? null,
                $data->teamMate->email ?? null,
                $data->teamMate->rollNo ?? null,
                $data->teamMate->phone ?? null,
                $data->firebaseDocId ?? null,
                $data->publicityMember ?? null,
                null
            ]);

            // For Leader (Member 1)
            $leaderSql = "INSERT INTO users (email, roll_no, coins) 
                          VALUES (?, ?, 0) 
                          ON DUPLICATE KEY UPDATE roll_no = VALUES(roll_no)";
            $stmtLeader = $pdo->prepare($leaderSql);
            $stmtLeader->execute([$data->teamLeader->email, $data->academicDetails->rollNo]);
            
            // Get Leader's numeric ID
            $stmtGetLeader = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmtGetLeader->execute([$data->teamLeader->email]);
            $leaderRow = $stmtGetLeader->fetch();
            $leaderId = $leaderRow['id'];
            
            // For Teammate (Member 2 - if exists)
            if (!empty($data->teamMate->email)) {
                $teammateRoll = $data->teamMate->rollNo ?? null;
                $teammateSql = "INSERT INTO users (email, roll_no, coins) 
                               VALUES (?, ?, 0) 
                               ON DUPLICATE KEY UPDATE roll_no = COALESCE(VALUES(roll_no), roll_no)"; 
                $stmtTeammate = $pdo->prepare($teammateSql);
                $stmtTeammate->execute([$data->teamMate->email, $teammateRoll]);
                
                // Get Teammate's numeric ID
                $stmtGetTeammate = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmtGetTeammate->execute([$data->teamMate->email]);
                $teammateRow = $stmtGetTeammate->fetch();
                $teammateId = $teammateRow['id'];
                
                // Link them numerically
                $linkSql = "UPDATE users SET teammate_user_id = ? WHERE id = ?";
                $stmtLink = $pdo->prepare($linkSql);
                
                // Leader -> Teammate
                $stmtLink->execute([$teammateId, $leaderId]);
                // Teammate -> Leader
                $stmtLink->execute([$leaderId, $teammateId]);
            }
            
        }

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Registrations saved with Team IDs.",
            "teamIds" => $results
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Unable to save registration.", "error" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete registration data."]);
}
?>
