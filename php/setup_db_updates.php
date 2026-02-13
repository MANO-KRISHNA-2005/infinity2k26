<?php
require_once 'db_config.php';

try {
    // 1. Create publicity_members table
    $sql_create_members = "CREATE TABLE IF NOT EXISTS publicity_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        active TINYINT DEFAULT 1
    )";
    $pdo->exec($sql_create_members);
    echo "Table 'publicity_members' created or already exists.<br>";

    // 2. Add publicity_member column to registrations if not exists
    $sql_check_col = "SHOW COLUMNS FROM registrations LIKE 'publicity_member'";
    $stmt = $pdo->query($sql_check_col);
    if ($stmt->rowCount() == 0) {
        $sql_add_col = "ALTER TABLE registrations ADD COLUMN publicity_member VARCHAR(100) NULL AFTER user_id";
        $pdo->exec($sql_add_col);
        echo "Column 'publicity_member' added to 'registrations' table.<br>";
    } else {
        echo "Column 'publicity_member' already exists.<br>";
    }

    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
