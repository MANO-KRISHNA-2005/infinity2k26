<?php
require_once 'config.php';
check_auth();

// Fetch alumni registrations
$stmt = $pdo->query("SELECT * FROM alumni_registrations ORDER BY created_at DESC");
$alumni = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alumni Registrations | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --neon-purple: #bc13fe;
            --bg-dark: #0a050f;
            --sidebar-width: 250px;
        }
        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.03);
            height: 100vh;
            border-right: 1px solid rgba(188, 19, 254, 0.2);
            padding: 30px;
            box-sizing: border-box;
            position: fixed;
        }
        .sidebar h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            color: var(--neon-purple);
            margin-bottom: 40px;
        }
        .nav-item {
            display: block;
            color: #ccc;
            text-decoration: none;
            padding: 12px 0;
            transition: 0.3s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .nav-item:hover, .nav-item.active {
            color: var(--neon-purple);
            padding-left: 10px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            margin: 0;
        }
        .table-container {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        th {
            color: var(--neon-purple);
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        tr:hover {
            background: rgba(188, 19, 254, 0.05);
        }
        .btn-logout {
            color: #ff4d4d;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>INFINITY ADMIN</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="registrations.php" class="nav-item">Registrations</a>
        <a href="alumni_list.php" class="nav-item active">Alumni Registrations</a>
        <a href="export.php" class="nav-item">Export Reports</a>
        <br><br>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Alumni Attendees</h1>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Grad Year</th>
                        <th>Registered On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alumni)): ?>
                        <tr><td colspan="6" style="text-align:center;">No alumni registrations found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($alumni as $row): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['grad_year']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
