<?php
require_once 'config.php';
check_auth();

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: registrations.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$id]);
$reg = $stmt->fetch();

if (!$reg) { echo "Registration not found."; exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $roll_no = $_POST['roll_no'];
    $events = implode(", ", $_POST['events'] ?? []);
    
    $tm_name = $_POST['teammate_name'] ?? null;
    $tm_email = $_POST['teammate_email'] ?? null;
    $tm_roll = $_POST['teammate_roll_no'] ?? null;
    $tm_phone = $_POST['teammate_phone'] ?? null;

    // 1. Update MySQL
    $event_name = $_POST['event_name'] ?? $reg['event_name'];
    $update_stmt = $pdo->prepare("UPDATE registrations SET 
        name=?, email=?, phone=?, roll_no=?, event_name=?,
        teammate_name=?, teammate_email=?, teammate_roll_no=?, teammate_phone=?
        WHERE id=?");
    $update_stmt->execute([
        $name, $email, $phone, $roll_no, $event_name,
        $tm_name, $tm_email, $tm_roll, $tm_phone,
        $id
    ]);
    
    // 2. Update Firebase if ID exists
    if (!empty($reg['firebase_doc_id'])) {
        try {
            require_once 'firebase_sync.php';
            $fb = new FirebaseSync(FIREBASE_PROJECT_ID); 
            $fb->updateDocument('registrations', $reg['firebase_doc_id'], [
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
                    'rollNo' => $roll_no
                ],
                'events' => $_POST['events'] ?? []
            ]);
        } catch (Exception $e) {
            // Log or handle error
        }
    }
    
    // Redirect with success message
    header('Location: registrations.php?msg=updated');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Registration | Admin</title>
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
        <h1>Edit Registration</h1>
        <form method="POST">
            <div class="form-grid">
                <!-- Primary Member -->
                <div class="form-card">
                    <h3 class="section-title">Primary Member</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($reg['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email ID</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($reg['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Roll Number</label>
                        <input type="text" name="roll_no" value="<?php echo htmlspecialchars($reg['roll_no']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($reg['phone']); ?>" required>
                    </div>
                </div>

                <!-- Teammate & Events -->
                <div class="form-card">
                    <h3 class="section-title">Teammate Details</h3>
                    <div class="form-group">
                        <label>Teammate Name</label>
                        <input type="text" name="teammate_name" value="<?php echo htmlspecialchars($reg['teammate_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Teammate Email ID</label>
                        <input type="email" name="teammate_email" value="<?php echo htmlspecialchars($reg['teammate_email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Teammate Roll Number</label>
                        <input type="text" name="teammate_roll_no" value="<?php echo htmlspecialchars($reg['teammate_roll_no'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Teammate Phone</label>
                        <input type="text" name="teammate_phone" value="<?php echo htmlspecialchars($reg['teammate_phone'] ?? ''); ?>">
                    </div>

                    <h3 class="section-title" style="margin-top: 30px;">Event Selection</h3>
                    <div class="form-group">
                        <select name="event_name" required>
                            <?php 
                            $all_events = ["ProZone", "Incognito", "Inveringo", "TechRush", "Swaptics", "Fusion Frames", "GameHolix", "Tech Arcade"];
                            foreach ($all_events as $event): ?>
                                <option value="<?php echo $event; ?>" <?php echo ($reg['event_name'] == $event) ? 'selected' : ''; ?>><?php echo $event; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="max-width: 1000px;">
                <button type="submit" class="btn-submit">UPDATE REGISTRATION</button>
            </div>
        </form>
    </div>
</body>
</html>
