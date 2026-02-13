<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'php/db_config.php';

echo "<h2>Database Connection Test</h2>";
try {
    if ($pdo) {
        echo "<p style='color:green'>Connection Successful!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Connection Failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Checking 'publicity_members' Table</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM publicity_members");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($members)) {
        echo "<p style='color:orange'>Table exists but is empty. Please add members via Admin Dashboard.</p>";
    } else {
        echo "<p style='color:green'>Found " . count($members) . " members:</p>";
        echo "<ul>";
        foreach ($members as $m) {
            echo "<li>ID: " . $m['id'] . " | Name: " . htmlspecialchars($m['name']) . " | Active: " . $m['active'] . "</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Query Failed: " . $e->getMessage() . "</p>";
    echo "<p>Did you run <code>php/setup.sql</code>?</p>";
}

echo "<h2>Testing API Response Code</h2>";
$apiUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/php/api_publicity_members.php";
echo "Fetching: $apiUrl<br>";

$apiContent = @file_get_contents($apiUrl);
if ($apiContent === FALSE) {
    echo "<p style='color:red'>Failed to fetch API URL directly.</p>";
} else {
    echo "<h3>API Output:</h3>";
    echo "<pre>" . htmlspecialchars($apiContent) . "</pre>";
}
?>
