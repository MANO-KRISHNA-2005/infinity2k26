<?php
require_once 'config.php';
check_auth();

$search = $_GET['search'] ?? '';

// Fetch all registrations
$sql = "SELECT * FROM registrations";
if ($search) {
    $sql .= " WHERE name LIKE :search OR email LIKE :search OR roll_no LIKE :search OR teammate_name LIKE :search";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$registrations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Registrations | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --neon-purple: #bc13fe;
            --bg-dark: #0a050f;
            --sidebar-width: 250px;
        }
        body { background-color: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; margin: 0; display: flex; }
        .sidebar { width: var(--sidebar-width); background: rgba(255, 255, 255, 0.03); height: 100vh; border-right: 1px solid rgba(188, 19, 254, 0.2); padding: 30px; box-sizing: border-box; position: fixed; }
        .sidebar h2 { font-family: 'Orbitron', sans-serif; font-size: 1.2rem; color: var(--neon-purple); margin-bottom: 40px; }
        .nav-item { display: block; color: #ccc; text-decoration: none; padding: 12px 0; transition: 0.3s; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .nav-item:hover, .nav-item.active { color: var(--neon-purple); padding-left: 10px; }
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        
        .table-container { background: rgba(255, 255, 255, 0.02); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.05); padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); color: var(--neon-purple); font-family: 'Orbitron', sans-serif; font-size: 0.8rem; }
        td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: top; }
        .event-tag { background: rgba(188, 19, 254, 0.1); color: var(--neon-purple); padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; margin: 2px; display: inline-block; }
        
        .search-bar { width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; margin-bottom: 20px; box-sizing: border-box; }
        .btn-action { background: none; border: none; color: #ccc; cursor: pointer; font-size: 1.1rem; margin-right: 10px; }
        .btn-delete { color: #ff4d4d; }
        .btn-edit { color: #4dadff; }
        .btn-action:hover { opacity: 0.7; }
        .btn-logout { color: #ff4d4d; text-decoration: none; font-size: 0.9rem; margin-top: auto; display: block; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>INFINITY ADMIN</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="registrations.php" class="nav-item active">Registrations</a>
        <a href="export.php" class="nav-item">Export Reports</a>
        <br><br>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="margin: 0;">Registration Management</h1>
            <a href="add.php" style="background: var(--neon-purple); color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-family: 'Orbitron', sans-serif; font-size: 0.8rem;">+ ADD NEW</a>
        </div>
        <form method="GET">
            <input type="text" name="search" class="search-bar" placeholder="Search by name, email, or roll number..." value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Team ID</th>
                        <th>Participant (Leader)</th>
                        <th>Academic Info</th>
                        <th>Event</th>
                        <th>Teammate</th>
                        <th>Registered By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr><td colspan="8" style="text-align: center;">No registrations found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?php echo date('d-M-y', strtotime($reg['created_at'])); ?></td>
                            <td style="font-family: 'Orbitron', sans-serif; color: var(--neon-purple); font-weight: bold;">
                                <?php echo htmlspecialchars($reg['team_id']); ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($reg['name']); ?></strong><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($reg['email']); ?></small><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($reg['phone']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($reg['roll_no']); ?><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($reg['degree']) . ' - ' . htmlspecialchars($reg['year']) . ' Year'; ?></small><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($reg['department']); ?></small>
                            </td>
                            <td>
                                <span class="event-tag"><?php echo htmlspecialchars($reg['event_name']); ?></span>
                            </td>
                            <td>
                                <?php if ($reg['teammate_name']): ?>
                                    <strong><?php echo htmlspecialchars($reg['teammate_name']); ?></strong><br>
                                    <small style="color: #888;"><?php echo htmlspecialchars($reg['teammate_roll_no']); ?></small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($reg['publicity_member'] ?? 'Self/Online'); ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $reg['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i></a>
                                <button onclick="deleteRegistration(<?php echo $reg['id']; ?>, '<?php echo $reg['firebase_doc_id'] ?? ''; ?>')" class="btn-action btn-delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function deleteRegistration(id, firebaseId) {
            if (confirm("Are you sure you want to delete this registration? This will remove it from BOTH SQL and Firebase.")) {
                window.location.href = `delete.php?id=${id}&firebase_id=${firebaseId}`;
            }
        }
    </script>
</body>
</html>
