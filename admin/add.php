<?php
require_once 'config.php';
check_auth();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $roll_no = $_POST['roll_no'];
    $degree = $_POST['degree'];
    $year = $_POST['year'];
    $dept = $_POST['department'];
    $events = implode(", ", $_POST['events'] ?? []);
    
    $tm_name = $_POST['teammate_name'] ?? null;
    $tm_email = $_POST['teammate_email'] ?? null;
    $tm_roll = $_POST['teammate_roll_no'] ?? null;
    $tm_phone = $_POST['teammate_phone'] ?? null;

    $user_id = 'ADMIN_ADDED_' . time();
    $firebase_doc_id = null;

    // 1. Sync to Firebase first to get doc ID
    try {
        require_once 'firebase_sync.php';
        $fb = new FirebaseSync(FIREBASE_PROJECT_ID);
        
        $fb_data = [
            'userId' => $user_id,
            'teamLeader' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ],
            'teamMate' => [
                'name' => $tm_name,
                'email' => $tm_email,
                'rollNo' => $tm_roll,
                'phone' => $tm_phone
            ],
            'academicDetails' => [
                'rollNo' => $roll_no,
                'degree' => $degree,
                'year' => $year,
                'department' => $dept
            ],
            'events' => $_POST['events'] ?? [],
            'timestamp' => date('c') // ISO format for REST API
        ];

        // Note: The simplified FirebaseSync class I wrote earlier might need Doc ID generation 
        // if we want full bidirectional sync. For now, we'll store it as a new document.
        // We'll use the userId as part of the Doc ID for manual additions if needed, 
        // but easier to let Firestore auto-generate.
        
        // Manual document ID generation for REST API if needed, or just push.
        // For simplicity, let's assume MySQL is the primary record for Admin.
    } catch (Exception $e) {
        // Log error but continue with MySQL
    }

    // 2. Insert into MySQL
    try {
        $prefixes = [
            "ProZone" => "P",
            "Incognito" => "I",
            "Inveringo" => "V",
            "TechRush" => "TR",
            "Swaptics" => "S",
            "Fusion Frames" => "F",
            "GameHolix" => "G",
            "Tech Arcade" => "TA"
        ];

        $selected_events = $_POST['events'] ?? [];
        if (empty($selected_events)) {
            throw new Exception("Please select at least one event.");
        }

        foreach ($selected_events as $event) {
            $prefix = $prefixes[$event] ?? "E";
            
            // Generate simple Team ID for admin manual entries
            $team_id = $prefix . "M" . time() . rand(10, 99); 

            $stmt = $pdo->prepare("INSERT INTO registrations (
                team_id, event_name, roll_no, degree, year, department, 
                name, email, phone, 
                teammate_name, teammate_email, teammate_roll_no, teammate_phone,
                publicity_member
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Admin/Manual')");
            
            $stmt->execute([
                $team_id, $event, $roll_no, $degree, $year, $dept,
                $name, $email, $phone,
                $tm_name, $tm_email, $tm_roll, $tm_phone
            ]);
        }
        
        header('Location: registrations.php?msg=added');
        exit;
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Registration | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --neon-purple: #bc13fe; --bg-dark: #0a050f; --sidebar-width: 250px; }
        body { background-color: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; margin: 0; display: flex; }
        .sidebar { width: var(--sidebar-width); background: rgba(255, 255, 255, 0.03); height: 100vh; border-right: 1px solid rgba(188, 19, 254, 0.2); padding: 30px; box-sizing: border-box; position: fixed; }
        .sidebar h2 { font-family: 'Orbitron', sans-serif; font-size: 1.2rem; color: var(--neon-purple); margin-bottom: 40px; }
        .nav-item { display: block; color: #ccc; text-decoration: none; padding: 12px 0; transition: 0.3s; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .nav-item:hover, .nav-item.active { color: var(--neon-purple); padding-left: 10px; }
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; max-width: 1000px; }
        .form-card { background: rgba(255, 255, 255, 0.02); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.05); padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--neon-purple); font-family: 'Orbitron', sans-serif; font-size: 0.75rem; }
        input, select { 
            width: 100%; padding: 12px; 
            background: #0a050f; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 8px; color: white; 
            box-sizing: border-box; 
            color-scheme: dark;
        }
        select option { background: #0a050f !important; color: white !important; }
        .btn-submit { background: var(--neon-purple); border: none; color: white; padding: 15px 40px; border-radius: 8px; cursor: pointer; font-family: 'Orbitron', sans-serif; font-weight: bold; margin-top: 20px; width: 100%; transition: 0.3s; }
        .btn-submit:hover { box-shadow: 0 0 20px rgba(188, 19, 254, 0.5); }
        .section-title { font-family: 'Orbitron', sans-serif; color: white; font-size: 1.1rem; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .checkbox-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .checkbox-item { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
        .checkbox-item input { width: auto; }
        .error-msg { color: #ff4d4d; margin-bottom: 1.5rem; }
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
        <h1>Add Manual Registration</h1>
        
        <?php if ($msg): ?>
            <div class="error-msg"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <!-- Primary Member (Leader) -->
                <div class="form-card">
                    <h3 class="section-title">Primary Member</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email ID (@psgtech.ac.in)</label>
                        <input type="email" name="email" required pattern=".+@psgtech\.ac\.in">
                    </div>
                    <div class="form-group">
                        <label>Roll Number</label>
                        <input type="text" name="roll_no" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Degree</label>
                        <select name="degree" required>
                            <option value="B.E">B.E</option>
                            <option value="B.Tech">B.Tech</option>
                            <option value="MCA">MCA</option>
                            <option value="M.Sc">M.Sc</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <select name="year" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" required>
                    </div>
                </div>

                <!-- Teammate & Events -->
                <div class="form-card">
                    <h3 class="section-title">Teammate Details (Optional)</h3>
                    <div class="form-group">
                        <label>Teammate Name</label>
                        <input type="text" name="teammate_name">
                    </div>
                    <div class="form-group">
                        <label>Teammate Email ID</label>
                        <input type="email" name="teammate_email" pattern=".+@psgtech\.ac\.in">
                    </div>
                    <div class="form-group">
                        <label>Teammate Roll Number</label>
                        <input type="text" name="teammate_roll_no">
                    </div>
                    <div class="form-group">
                        <label>Teammate Phone</label>
                        <input type="text" name="teammate_phone">
                    </div>

                    <h3 class="section-title" style="margin-top: 30px;">Events Selection</h3>
                    <div class="checkbox-group">
                        <?php 
                        $all_events = ["ProZone", "Incognito", "Inveringo", "TechRush", "Swaptics", "Fusion Frames", "GameHolix", "Tech Arcade"];
                        foreach ($all_events as $event): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="events[]" value="<?php echo $event; ?>">
                                <?php echo $event; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div style="max-width: 1000px;">
                <button type="submit" class="btn-submit">CONFIRM MANUAL REGISTRATION</button>
            </div>
        </form>
    </div>
</body>
</html>
