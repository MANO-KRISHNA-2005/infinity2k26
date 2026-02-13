<?php
require_once 'config.php';
check_auth();

if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $stmt = $pdo->query("SELECT * FROM registrations ORDER BY created_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($type == 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=registrations_'.date('Y-m-d').'.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Team ID', 'Event', 'Name', 'Email', 'Phone', 'Roll No', 'Degree', 'Year', 'Department', 'Teammate', 'Date'));
        foreach ($data as $row) {
            fputcsv($output, array(
                $row['id'], $row['team_id'], $row['event_name'], $row['name'], $row['email'], $row['phone'], 
                $row['roll_no'], $row['degree'], $row['year'], $row['department'], 
                $row['teammate_name'], $row['created_at']
            ));
        }
        fclose($output);
        exit;
    }

    if ($type == 'pdf') {
        // Simple HTML-based PDF (Printable format)
        echo "<html><head><title>Registration Report</title><style>
            table { width: 100%; border-collapse: collapse; font-family: sans-serif; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
            h1 { text-align: center; }
        </style></head><body onload='window.print()'>";
        echo "<h1>INFINITY 2K26 REGISTRATIONS</h1>";
        echo "<table><thead><tr><th>Date</th><th>Team ID</th><th>Event</th><th>Name</th><th>Roll No</th><th>Email</th></tr></thead><tbody>";
        foreach ($data as $row) {
            echo "<tr>
                <td>".date('d/m', strtotime($row['created_at']))."</td>
                <td><strong>".$row['team_id']."</strong></td>
                <td>".$row['event_name']."</td>
                <td>".$row['name']."</td>
                <td>".$row['roll_no']."</td>
                <td>".$row['email']."</td>
            </tr>";
        }
        echo "</tbody></table></body></html>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Export Reports | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --neon-purple: #bc13fe; --bg-dark: #0a050f; --sidebar-width: 250px; }
        body { background-color: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; margin: 0; display: flex; }
        .sidebar { width: var(--sidebar-width); background: rgba(255, 255, 255, 0.03); height: 100vh; border-right: 1px solid rgba(188, 19, 254, 0.2); padding: 30px; box-sizing: border-box; position: fixed; }
        .sidebar h2 { font-family: 'Orbitron', sans-serif; font-size: 1.2rem; color: var(--neon-purple); margin-bottom: 40px; }
        .nav-item { display: block; color: #ccc; text-decoration: none; padding: 12px 0; transition: 0.3s; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .nav-item:hover, .nav-item.active { color: var(--neon-purple); padding-left: 10px; }
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        
        .export-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .export-card { background: rgba(255, 255, 255, 0.02); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.05); padding: 30px; text-align: center; }
        .export-card i { font-size: 3rem; color: var(--neon-purple); display: block; margin-bottom: 15px; }
        .btn-download { display: inline-block; background: var(--neon-purple); color: white; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 0.8rem; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>INFINITY ADMIN</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="registrations.php" class="nav-item">Registrations</a>
        <a href="export.php" class="nav-item active">Export Reports</a>
        <br><br>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Export Reports</h1>
        <div class="export-grid">
            <div class="export-card">
                <i class="bi bi-file-earmark-excel"></i>
                <h3>Excel (CSV)</h3>
                <p>Download full registration data as a .csv file compatible with Microsoft Excel.</p>
                <a href="export.php?type=excel" class="btn-download">DOWNLOAD EXCEL</a>
            </div>
            <div class="export-card">
                <i class="bi bi-file-earmark-pdf"></i>
                <h3>PDF Report</h3>
                <p>Generate a professional PDF summary for printing or records.</p>
                <a href="export.php?type=pdf" target="_blank" class="btn-download">GENERATE PDF</a>
            </div>
        </div>
    </div>
</body>
</html>
