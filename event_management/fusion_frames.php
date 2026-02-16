<?php
require_once '../admin/config.php';
$this_event = "Fusion Frames"; $slug = "fusion_frames";
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$check = mysqli_query($conn, "SELECT is_active FROM portal_settings WHERE event_slug = '$slug'");
$portal_status = mysqli_fetch_assoc($check);
if (!$portal_status || $portal_status['is_active'] == 0) {
    die("<div style='background:#0a0510; color:#ff4d4d; padding:100px; text-align:center; height:100vh; font-family:Orbitron; box-sizing:border-box;'>
        <h1 style='letter-spacing:5px;'>PORTAL CLOSED</h1>
        <p style='color:#888; margin-top:20px;'>This portal is currently disabled. Please contact the event administrator.</p>
    </div>");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['portal_pass'])) {
    if ($_POST['portal_pass'] === DESK_PASSWORD) { $_SESSION['portal_'.$slug.'_unlocked'] = true; } 
    else { $error = "Incorrect Password"; }
}
$active_mgmt = isset($_SESSION['portal_'.$slug.'_unlocked']) && $_SESSION['portal_'.$slug.'_unlocked'] === true;
if ($active_mgmt) {
    $registrations = mysqli_query($conn, "SELECT * FROM registrations WHERE event_name = '$this_event' ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title><?php echo $this_event; ?> Portal | Infinity 6.0</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>:root { --neon-purple: #bc13fe; --bg-dark: #0a0510; } body { background: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; margin: 0; } header { padding: 20px 40px; border-bottom: 2px solid var(--neon-purple); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.5); } .main-content { padding: 40px; } .table-container { background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); overflow: hidden; } table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; } th { background: rgba(188, 19, 254, 0.1); padding: 15px; color: var(--neon-purple); font-family: 'Orbitron'; font-size: 0.75rem; text-transform: uppercase; } td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); } .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-family: 'Orbitron'; font-weight: bold; text-decoration: none; display: inline-block; font-size: 0.8rem; } .status-pending { color: #ffc107; font-weight: bold; } .status-attended { color: #4ade80; font-weight: bold; } .status-not-coming { color: #ff4d4d; font-weight: bold; }</style>
</head>
<body>
    <?php if (!$active_mgmt): ?>
    <div style="height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="background: rgba(255,255,255,0.03); padding: 40px; border-radius: 20px; border: 1px solid var(--neon-purple); text-align: center; width: 350px;">
            <p style="font-size: 0.7rem; color: #888; margin-bottom: 10px;">PORTAL GLOBALLY ENABLED</p>
            <h2 style="font-family: 'Orbitron'; margin-bottom: 30px;"><?php echo strtoupper($this_event); ?></h2>
            <form method="POST">
                <input type="password" name="portal_pass" placeholder="Enter Access Password" required style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; margin-bottom: 20px; text-align: center;">
                <button type="submit" class="btn" style="background: var(--neon-purple); width: 100%;">UNLOCK EVENT</button>
            </form>
            <?php if(isset($error)) echo "<p style='color:#ff4d4d; font-size:0.8rem; margin-top:15px;'>$error</p>"; ?>
        </div>
    </div>
    <?php exit; endif; ?>
    <header>
        <div style="display: flex; align-items: center; gap: 15px;"><h1 style="font-family: 'Orbitron'; color: var(--neon-purple); margin: 0; font-size: 1.2rem;"><?php echo $this_event; ?> MANAGEMENT</h1></div>
        <div style="display: flex; gap: 15px;"><a href="event_management.php?action=export&event=<?php echo $this_event; ?>" class="btn" style="background: #1d6f42; color: white;"><i class="bi bi-file-earmark-excel-fill"></i> EXCEL</a><a href="event_management.php" style="color: white; text-decoration: none; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.2); padding: 10px 15px; border-radius: 5px;">Dashboard</a></div>
    </header>
    <div class="main-content">
        <div class="table-container">
            <table><thead><tr><th>Code</th><th>Leader Details</th><th>Teammate Details</th><th>Slot</th><th>Attendance</th></tr></thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($registrations)): ?>
                    <tr><td style="font-weight: bold; color: var(--neon-purple);"><?php echo $row['team_id']; ?></td>
                        <td>
                            <div style="font-weight:600;"><?php echo $row['name']; ?></div>
                            <div style="font-size:0.75rem; opacity:0.7;"><?php echo $row['email']; ?></div>
                            <div style="font-size:0.75rem; opacity:0.7;"><?php echo $row['phone']; ?></div>
                            <div style="font-size:0.7rem; color:var(--neon-purple);"><?php echo $row['roll_no']; ?></div>
                        </td>
                        <td>
                            <?php if($row['teammate_name']): ?>
                                <div style="font-weight:600;"><?php echo $row['teammate_name']; ?></div>
                                <div style="font-size:0.75rem; opacity:0.7;"><?php echo $row['teammate_email']; ?></div>
                                <div style="font-size:0.75rem; opacity:0.7;"><?php echo $row['teammate_phone']; ?></div>
                                <div style="font-size:0.7rem; color:var(--neon-purple);"><?php echo $row['teammate_roll_no']; ?></div>
                            <?php else: ?>
                                <span style="opacity:0.3;">Solo Entry</span>
                            <?php endif; ?>
                        </td>
                        <td><input type="text" value="<?php echo htmlspecialchars($row['slot']); ?>" onblur="updateStatus(<?php echo $row['id']; ?>, 'slot', this.value)" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 5px; border-radius: 4px; width: 100px;"></td>
                        <td><select onchange="updateStatus(<?php echo $row['id']; ?>, 'attendance_status', this.value)" style="background: transparent; border: none; color: inherit; cursor: pointer; font-weight: bold;" class="status-<?php echo str_replace(' ', '-', $row['attendance_status']); ?>"><option value="pending" <?php if($row['attendance_status'] == 'pending') echo 'selected'; ?>>PENDING</option><option value="attended" <?php if($row['attendance_status'] == 'attended') echo 'selected'; ?>>ATTENDED</option><option value="not coming" <?php if($row['attendance_status'] == 'not coming') echo 'selected'; ?>>NOT COMING</option></select></td></tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>function updateStatus(id, field, value) { const formData = new FormData(); formData.append('ajax_action', 'update_record'); formData.append('id', id); formData.append('field', field); formData.append('value', value); fetch('event_management.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => { if(!data.success) alert("Failed: " + data.message); else if(field === 'attendance_status') { event.target.className = 'status-' + value.replace(' ', '-'); } }); }</script>
</body></html>
