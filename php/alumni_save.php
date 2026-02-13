<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO alumni_registrations (user_id, name, email, phone, grad_year, firebase_doc_id) VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['user_id'],
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['grad_year'],
        $data['firebase_doc_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Alumni registration saved to MySQL']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'MySQL Error: ' . $e->getMessage()]);
}
?>
