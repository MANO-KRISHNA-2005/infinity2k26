<?php
require_once 'config.php';
check_auth();

$id = $_GET['id'] ?? null;
$firebase_id = $_GET['firebase_id'] ?? null;

if ($id) {
    // 1. Delete from MySQL
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$id]);

    // 2. Delete from Firebase (Optional but recommended if firebase_doc_id is stored)
    // Note: We'll add Firebase deletion logic once we configure the REST API or similar.
    // For now, it's removed from SQL.
    
    header('Location: registrations.php?msg=deleted');
    exit;
}
?>
