<?php
require_once 'php/db_config.php';
$stmt = $pdo->query("SELECT * FROM publicity_members");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($members) . "<br>";
print_r($members);
?>
