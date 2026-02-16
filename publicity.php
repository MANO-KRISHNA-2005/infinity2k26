<?php
require_once 'admin/config.php';

// Handle Desk Login
if (isset($_POST['desk_password'])) {
    if ($_POST['desk_password'] === DESK_PASSWORD) {
        $_SESSION['desk_logged_in'] = true;
    } else {
        $error = "Incorrect Desk Password";
    }
}

// Check if either Admin or Desk is logged in
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_desk = isset($_SESSION['desk_logged_in']) && $_SESSION['desk_logged_in'] === true;

if (!$is_admin && !$is_desk): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Publicity Access | Infinity 6.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { background: #0a0510; color: white; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-box { background: rgba(255,255,255,0.03); padding: 40px; border-radius: 20px; border: 1px solid #bc13fe; text-align: center; max-width: 400px; width: 90%; }
        h1 { font-family: 'Orbitron', sans-serif; color: #bc13fe; font-size: 1.5rem; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; margin-bottom: 20px; box-sizing: border-box; text-align: center; }
        button { width: 100%; padding: 12px; background: #bc13fe; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .error { color: #ff4d4d; margin-bottom: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>DESK ACCESS</h1>
        <?php if (isset($error)): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <input type="password" name="desk_password" placeholder="Enter Desk Password" required autofocus>
            <button type="submit">UNLOCK PORTAL</button>
        </form>
    </div>
</body>
</html>
<?php 
exit;
endif; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicity Portal | Infinity 6.0</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@300;500;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon-purple: #bc13fe;
            --bg-dark: #0a0510;
        }
        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            padding: 20px 40px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(188, 19, 254, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .portal-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-purple);
            font-size: 1.2rem;
            letter-spacing: 2px;
        }
        .container {
            flex: 1;
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 8px;
            color: #888;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        input, select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: var(--neon-purple);
            box-shadow: 0 0 15px rgba(188, 19, 254, 0.2);
        }
        select option {
            background: #0a0510;
            color: white;
        }
        .event-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 15px;
        }
        .event-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .event-item input { width: auto; }
        

        #resultOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .result-card {
            background: #1a0b2e;
            padding: 60px;
            border-radius: 30px;
            border: 1px solid var(--neon-purple);
            max-width: 500px;
            width: 90%;
        }
        .team-id-display {
            font-size: 3rem;
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-purple);
            margin: 20px 0;
            text-shadow: 0 0 20px rgba(188, 19, 254, 0.5);
        }
        .btn-close {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="portal-title">PUBLICITY DESK <span style="color: white; opacity: 0.5;">|Walk-in Portal|</span></div>
        <div>
            <a href="admin/logout.php" style="color: #ff4d4d; text-decoration: none;">Logout</a>
        </div>
    </header>

    <div class="container">
        <form id="publicityForm" class="form-card">
            <h2 style="font-family: 'Orbitron', sans-serif; margin-bottom: 30px;">Direct Entry</h2>
            
            <div class="form-grid">
                <div class="form-section">
                    <div class="form-group">
                        <label>Publicity Member (Who is registering?)</label>
                        <select id="publicityMember" required style="border-color: #bc13fe; background: rgba(188, 19, 254, 0.1);">
                            <option value="">Select Member</option>
                            <!-- Populated by JS -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Team Leader Name</label>
                        <input type="text" id="regName" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="regPhone" required>
                    </div>
                    <div class="form-group">
                        <label>Roll Number</label>
                        <input type="text" id="regRollNo" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address (@psgtech.ac.in)</label>
                        <input type="email" id="regEmail" value="@psgtech.ac.in" style="border-color: rgba(188, 19, 254, 0.4);" required>
                        <small style="font-size: 0.75rem; opacity: 0.6; color: var(--neon-purple);">Must be full @psgtech.ac.in address</small>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" id="regCourse" placeholder="e.g. CSE / IT" required>
                    </div>
                </div>

                <div class="form-section">
                    <!-- Teammate Section -->
                    <div id="teammateSection">
                        <h3 style="font-family: 'Orbitron', sans-serif; font-size: 1rem; color: #aaa; margin-bottom: 20px; border-bottom: 1px solid #444; padding-bottom: 10px;">Teammate Details <span style="font-size: 0.7rem; color: #666;">(Optional for GameHolix)</span></h3>
                        <div class="form-group">
                            <label>Teammate Name</label>
                            <input type="text" id="tmName">
                        </div>
                        <div class="form-group">
                            <label>Teammate Phone</label>
                            <input type="tel" id="tmPhone">
                        </div>
                        <div class="form-group">
                            <label>Teammate Roll No</label>
                            <input type="text" id="tmRollNo">
                        </div>
                        <div class="form-group">
                            <label>Teammate Email</label>
                            <input type="email" id="tmEmail" value="@psgtech.ac.in" style="border-color: rgba(188, 19, 254, 0.4);">
                        </div>
                    </div>
                    <br>

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Degree</label>
                            <select id="regDegree" required>
                                <option value="B.E">B.E</option>
                                <option value="B.Tech">B.Tech</option>
                                <option value="B.E (Sandwich)">B.E (Sandwich)</option>
                                <option value="B.Sc">B.Sc</option>
                                <option value="M.Sc">M.Sc</option>
                                <option value="MCA">MCA</option>
                                <option value="M.E">M.E</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year</label>
                            <select id="regYear" required>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                        </div>
                    </div>

                    <label>Events Selection</label>
                    <div class="event-grid">
                        <?php 
                        $events = ["ProZone", "Incognito", "Inveringo", "TechRush", "Swaptics", "Fusion Frames", "GameHolix", "Tech Arcade"];
                        foreach ($events as $e): ?>
                            <label class="event-item">
                                <input type="checkbox" name="eventCheck" value="<?php echo $e; ?>">
                                <?php echo $e; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" id="submitBtn" class="confirm-btn">CONFIRM REGISTRATION</button>
        </form>
    </div>

    <div id="resultOverlay">
        <div class="result-card">
            <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #4ade80;"></i>
            <h2 style="font-family: 'Orbitron', sans-serif; margin: 20px 0;">ENTRY SUCCESSFUL</h2>
            <p id="resultText"></p>
            <div id="idList" style="margin-top: 20px;"></div>
            <br>
            <button class="btn-close" onclick="location.reload()">NEXT REGISTRATION</button>
        </div>
    </div>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-firestore-compat.js"></script>
    
    <script src="js/firebase-config.js?v=<?php echo time(); ?>"></script>
    <script src="js/publicity.js?v=<?php echo time(); ?>"></script>
</body>
</html>
