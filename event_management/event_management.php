<?php
require_once '../admin/config.php';

// --- 1. HANDLE AJAX UPDATES ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_action'])) {
    if (!isset($_SESSION['event_mgmt_access'])) { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($_POST['ajax_action'] == 'update_record') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $field = mysqli_real_escape_string($conn, $_POST['field']);
        $value = mysqli_real_escape_string($conn, $_POST['value']);
        $type = $_POST['type'] ?? 'participant';
        $allowed = ['slot', 'attendance_status'];
        if (!in_array($field, $allowed)) { echo json_encode(['success'=>false, 'message'=>'Invalid Field']); exit; }
        $table = ($type === 'alumni') ? 'alumni_registrations' : 'registrations';
        if (mysqli_query($conn, "UPDATE $table SET $field = '$value' WHERE id = '$id'")) echo json_encode(['success'=>true]);
        else echo json_encode(['success'=>false, 'message'=>mysqli_error($conn)]);
    }

    if ($_POST['ajax_action'] == 'toggle_portal') {
        $slug = mysqli_real_escape_string($conn, $_POST['slug']);
        $status = (int)$_POST['status'];
        if (mysqli_query($conn, "UPDATE portal_settings SET is_active = $status WHERE event_slug = '$slug'")) echo json_encode(['success' => true]);
        else echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
    exit;
}

// --- 2. HANDLE EXCEL EXPORT ---
if (isset($_GET['action']) && $_GET['action'] == 'export') {
    if (!isset($_SESSION['event_mgmt_access'])) die("Unauthorized");
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $type = $_GET['type'] ?? 'registrations';
    $event = $_GET['event'] ?? '';

    if ($type === 'alumni') {
        $filename = "Infinity_Alumni_List_" . date('Y-m-d') . ".csv";
        $query = "SELECT name, email, phone, grad_year, slot, attendance_status, created_at FROM alumni_registrations";
        $header = ["Name", "Email", "Phone", "Graduation Year", "Slot", "Attendance", "Registered At"];
    } else {
        $filename = "Infinity_Registrations_" . ($event ? $event . "_" : "") . date('Y-m-d') . ".csv";
        $query = "SELECT team_id, name, event_name, roll_no, phone, slot, attendance_status, created_at FROM registrations";
        if ($event) $query .= " WHERE event_name = '" . mysqli_real_escape_string($conn, $event) . "'";
        $header = ["Team ID", "Name", "Event", "Roll No", "Phone", "Slot", "Attendance", "Registered At"];
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $header);
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) fputcsv($output, $row);
    fclose($output);
    exit;
}

// --- 3. NORMAL VIEW HANDLING ---
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $google_email = $_POST['google_email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (in_array($google_email, AUTHORIZED_ADMINS) && $password === DESK_PASSWORD) {
        $_SESSION['event_mgmt_access'] = true;
        $_SESSION['mgmt_email'] = $google_email;
    } else { $error = "Invalid Authorized Email or Portal Password."; }
}

$has_access = isset($_SESSION['event_mgmt_access']) && $_SESSION['event_mgmt_access'] === true;
$view = $_GET['view'] ?? 'general'; // 'general' or 'alumni'

if ($has_access) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $total_regs = mysqli_query($conn, "SELECT COUNT(*) as count FROM registrations")->fetch_assoc()['count'];
    $alumni_regs = mysqli_query($conn, "SELECT COUNT(*) as count FROM alumni_registrations")->fetch_assoc()['count'];
    
    $search = $_GET['search'] ?? '';
    $sort_event = $_GET['event'] ?? '';
    
    if ($view === 'alumni') {
        $query = "SELECT * FROM alumni_registrations WHERE 1=1";
        if ($search) $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
        $query .= " ORDER BY created_at DESC";
    } else {
        $query = "SELECT * FROM registrations WHERE 1=1";
        if ($search) $query .= " AND (name LIKE '%$search%' OR roll_no LIKE '%$search%' OR team_id LIKE '%$search%')";
        if ($sort_event) $query .= " AND event_name = '$sort_event'";
        $query .= " ORDER BY created_at DESC";
    }
    $records = mysqli_query($conn, $query);
}
$events = ["ProZone", "Incognito", "Inveringo", "TechRush", "Swaptics", "Fusion Frames", "GameHolix", "Tech Arcade"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Infinity 6.0 Management Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --neon-purple: #bc13fe; --bg-dark: #0a0510; }
        body { background: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; margin: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(188, 19, 254, 0.3); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.5); position: sticky; top: 0; z-index: 100; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 30px 40px; }
        .stat-card { background: rgba(255,255,255,0.03); padding: 25px; border-radius: 20px; border: 1px solid rgba(188, 19, 254, 0.1); text-align: center; }
        .main-content { padding: 0 40px 40px; }
        .table-container { background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); overflow: hidden; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
        th { background: rgba(188, 19, 254, 0.1); padding: 15px; color: var(--neon-purple); font-family: 'Orbitron'; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .badge-pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; font-weight: bold; }
        .badge-attended { background: rgba(74, 222, 128, 0.1); color: #4ade80; font-weight: bold; }
        .badge-not-coming { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; font-weight: bold; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-family: 'Orbitron'; font-weight: bold; color: white; text-decoration: none; display: inline-block; font-size: 0.8rem; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab { padding: 10px 25px; border-radius: 8px; cursor: pointer; background: rgba(255,255,255,0.05); text-decoration: none; color: #888; font-family: 'Orbitron'; font-size: 0.8rem; }
        .tab.active { background: var(--neon-purple); color: white; }
    </style>
</head>
<body>
    <?php if (!$has_access): ?>
    <div style="position: fixed; inset: 0; background: var(--bg-dark); z-index: 1000; display: flex; align-items: center; justify-content: center;">
        <div style="background: rgba(255,255,255,0.03); padding: 40px; border-radius: 24px; border: 1px solid var(--neon-purple); width: 100%; max-width: 400px; text-align: center;">
            <h1 style="font-family: 'Orbitron'; color: var(--neon-purple); margin-bottom: 30px;">MANAGEMENT ACCESS</h1>
            <div id="step1">
                <button id="googleLoginBtn" class="btn" style="background: white; color: black; display: flex; align-items: center; justify-content: center; gap: 10px; width:100%;">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20"> Sign in with Google
                </button>
            </div>
            <div id="step2" style="display: none;">
                <p id="emailDisp" style="color: var(--neon-purple); font-weight: bold; margin-bottom: 20px;"></p>
                <form method="POST">
                    <input type="hidden" name="action" value="login"><input type="hidden" name="google_email" id="googleEmailHidden">
                    <input type="password" name="password" placeholder="Enter Portal Password" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; text-align: center; margin-bottom: 15px;">
                    <button type="submit" class="btn" style="background: var(--neon-purple); width:100%;">UNLOCK PORTAL</button>
                </form>
            </div>
        </div>
    </div>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
        const firebaseConfig = { apiKey: "AIzaSyCi-4p4cyahglxocOsHIG2oT6O05nfpOtk", authDomain: "infinity-2k26.firebaseapp.com", projectId: "infinity-2k26" };
        initializeApp(firebaseConfig);
        const auth = getAuth(); const provider = new GoogleAuthProvider();
        const whitelist = <?php echo json_encode(AUTHORIZED_ADMINS); ?>;
        document.getElementById('googleLoginBtn').onclick = async () => {
            try {
                const result = await signInWithPopup(auth, provider);
                if (whitelist.includes(result.user.email)) {
                    document.getElementById('step1').style.display = 'none'; document.getElementById('step2').style.display = 'block';
                    document.getElementById('emailDisp').innerText = "Verified: " + result.user.email;
                    document.getElementById('googleEmailHidden').value = result.user.email;
                } else { alert("Unauthorized: " + result.user.email); await auth.signOut(); }
            } catch (e) { alert(e.message); }
        };
    </script>
    <?php exit; endif; ?>

    <header>
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="../assets/images/psg college logo.png" height="40">
            <h1 style="font-family: 'Orbitron'; color: var(--neon-purple); margin: 0; font-size: 1.2rem; letter-spacing: 2px;">MANAGEMENT</h1>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: #888; margin-right: 20px;">User: <strong style="color: var(--neon-purple);"><?php echo $_SESSION['mgmt_email']; ?></strong></span>
            <a href="logout.php" style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 5px 15px; border-radius: 5px; text-decoration: none; font-size: 0.8rem;">Logout</a>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card"><h3 style="color:#888; font-size:0.7rem;">TOTAL PARTICIPANTS</h3><p style="font-size: 2rem; font-family: 'Orbitron'; margin: 10px 0; color: var(--neon-purple);"><?php echo $total_regs; ?></p></div>
        <div class="stat-card"><h3 style="color:#888; font-size:0.7rem;">ALUMNI GUESTS</h3><p style="font-size: 2rem; font-family: 'Orbitron'; margin: 10px 0; color: #4ade80;"><?php echo $alumni_regs; ?></p></div>
        <div class="stat-card"><h3 style="color:#888; font-size:0.7rem;">TOTAL REVENUE</h3><p style="font-size: 2rem; font-family: 'Orbitron'; margin: 10px 0; color: #ffc107;">--</p></div>
    </div>

    <div class="main-content">
        <div style="background: rgba(255,255,255,0.03); border-radius: 15px; border: 1px solid rgba(188,19,254,0.2); padding: 25px; margin-bottom: 40px;">
            <h3 style="font-family: 'Orbitron'; color: var(--neon-purple); margin-top: 0; font-size: 0.9rem; letter-spacing: 2px; margin-bottom: 20px;">PORTAL MASTER CONTROLS</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <?php 
                $settings = mysqli_query($conn, "SELECT * FROM portal_settings ORDER BY event_name ASC");
                while($s = mysqli_fetch_assoc($settings)): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.3); padding: 10px 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                        <span style="font-size: 0.8rem; font-weight: 600;"><?php echo $s['event_name']; ?></span>
                        <label class="switch">
                            <input type="checkbox" onchange="togglePortal('<?php echo $s['event_slug']; ?>', this.checked ? 1 : 0)" <?php echo $s['is_active'] ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>
            <p style="font-size: 0.7rem; color: #888; margin-top: 15px; font-style: italic;"><i class="bi bi-info-circle"></i> Enabled portals can be accessed from any system/device using the specific portal password.</p>
        </div>

        <div class="tabs">
            <a href="?view=general" class="tab <?php echo $view != 'alumni' ? 'active' : ''; ?>">General Participants</a>
            <a href="?view=alumni" class="tab <?php echo $view == 'alumni' ? 'active' : ''; ?>">Alumni Guest List</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <form style="display: flex; gap: 10px; flex: 1;">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 10px; border-radius: 8px; flex: 0.4;">
                <?php if($view != 'alumni'): ?>
                <select name="event" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 10px; border-radius: 8px;">
                    <option value="">All Events</option>
                    <?php foreach($events as $e): ?><option value="<?php echo $e; ?>" <?php echo $sort_event == $e ? 'selected' : ''; ?>><?php echo $e; ?></option><?php endforeach; ?>
                </select>
                <?php endif; ?>
                <button type="submit" class="btn" style="background: var(--neon-purple);">FILTER</button>
            </form>
            <a href="?action=export&view=<?php echo $view; ?>&event=<?php echo $sort_event; ?>" class="btn" style="background: #1d6f42;"><i class="bi bi-file-earmark-excel"></i> EXCEL EXPORT</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <?php if($view == 'alumni'): ?>
                            <th>Name</th><th>Email</th><th>Phone</th><th>Year</th><th>Slot</th><th>Attendance</th>
                        <?php else: ?>
                            <th>Team Code</th><th>Leader (Name/Email/Phone)</th><th>Teammate (Name/Email/Phone)</th><th>Event</th><th>Roll</th><th>Slot</th><th>Attendance</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($records)): ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <?php if($view == 'alumni'): ?>
                            <td style="font-weight:bold; color:#4ade80;"><?php echo $row['name']; ?></td><td><?php echo $row['email']; ?></td><td><?php echo $row['phone']; ?></td><td><?php echo $row['grad_year']; ?></td>
                        <?php else: ?>
                            <td style="font-weight:bold; color:var(--neon-purple);"><?php echo $row['team_id']; ?></td>
                            <td>
                                <div style="font-weight:600;"><?php echo $row['name']; ?></div>
                                <div style="font-size:0.7rem; opacity:0.7;"><?php echo $row['email']; ?></div>
                                <div style="font-size:0.7rem; opacity:0.7;"><?php echo $row['phone']; ?></div>
                            </td>
                            <td>
                                <?php if($row['teammate_name']): ?>
                                    <div style="font-weight:600;"><?php echo $row['teammate_name']; ?></div>
                                    <div style="font-size:0.7rem; opacity:0.7;"><?php echo $row['teammate_email']; ?></div>
                                    <div style="font-size:0.7rem; opacity:0.7;"><?php echo $row['teammate_phone']; ?></div>
                                <?php else: ?>
                                    <span style="opacity:0.3;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><span style="font-size:0.7rem; background:rgba(188,19,254,0.1); padding:2px 8px; border-radius:4px;"><?php echo $row['event_name']; ?></span></td>
                            <td style="font-size:0.8rem; opacity:0.8;"><?php echo $row['roll_no']; ?></td>
                        <?php endif; ?>
                        <td><input type="text" value="<?php echo htmlspecialchars($row['slot']); ?>" onblur="updateField(<?php echo $row['id']; ?>, 'slot', this.value, '<?php echo $view; ?>')" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 5px; border-radius: 4px; width: 100px;"></td>
                        <td>
                            <select onchange="updateField(<?php echo $row['id']; ?>, 'attendance_status', this.value, '<?php echo $view; ?>')" class="badge-<?php echo str_replace(' ', '-', $row['attendance_status']); ?>" style="background:transparent; border:none; color:inherit; font-weight:bold; cursor:pointer;">
                                <option value="pending" <?php echo $row['attendance_status']=='pending'?'selected':''; ?>>PENDING</option>
                                <option value="attended" <?php echo $row['attendance_status']=='attended'?'selected':''; ?>>ATTENDED</option>
                                <option value="not coming" <?php echo $row['attendance_status']=='not coming'?'selected':''; ?>>NOT COMING</option>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function updateField(id, field, value, type) {
            const fd = new FormData(); fd.append('ajax_action', 'update_record'); fd.append('id', id); fd.append('field', field); fd.append('value', value); fd.append('type', type);
            fetch('event_management.php', { method: 'POST', body: fd })
            .then(r => r.json()).then(d => {
                if(!d.success) alert(d.message);
                else if(field === 'attendance_status') event.target.className = 'badge-' + value.replace(' ', '-');
            }).catch(e => console.error(e));
        }
        function togglePortal(slug, status) {
            const formData = new FormData();
            formData.append('ajax_action', 'toggle_portal');
            formData.append('slug', slug);
            formData.append('status', status);
            fetch('event_management.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { if(!data.success) alert("Error toggling portal: " + data.message); });
        }
    </script>
    <style>
        .switch { position: relative; display: inline-block; width: 40px; height: 20px; vertical-align: middle; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--neon-purple); }
        input:checked + .slider:before { transform: translateX(20px); }
    </style>
</body>
</html>
