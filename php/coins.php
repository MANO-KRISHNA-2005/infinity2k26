<?php
// coins.php - Admin Portal for Coin Management
session_start();
header("Access-Control-Allow-Origin: *");
header("Cross-Origin-Opener-Policy: unsafe-none"); 
require_once 'db_config.php'; 

// authorized_admins.php or config equivalent
$authorized_admins = ['23x020@psgtech.ac.in', '23x045@psgtech.ac.in', '23x019@psgtech.ac.in'];
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Infinity@2k26');

// Check Session logic
$is_authenticated = isset($_SESSION['coin_admin_authenticated']) && $_SESSION['coin_admin_authenticated'] === true;

// Step 2 Verification logic (POST from itself)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_step']) && $_POST['auth_step'] == '2') {
    $google_email = $_POST['google_email'] ?? '';
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (in_array($google_email, $authorized_admins) && $user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['coin_admin_authenticated'] = true;
        $_SESSION['coin_admin_email'] = $google_email;
        $is_authenticated = true;
    } else {
        $auth_error = "Invalid 2FA credentials.";
    }
}

// Handle Coin Addition (POST) - ONLY IF AUTHENTICATED
$message = "";
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coins') {
    $admin_email = $_SESSION['coin_admin_email']; 
    $target_email = $_POST['email'] ?? ''; 
    $target_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0; 
    $amount = (int)$_POST['amount'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET coins = coins + ? WHERE email = ? AND id = ?");
        $stmt->execute([$amount, $target_email, $target_user_id]);
        if ($stmt->rowCount() > 0) {
            $message = "<div class='success'>Successfully credited $amount coins to $target_email (#$target_user_id).</div>";
        } else {
            $message = "<div class='error'>Verification Failed: User ID and Email do not match.</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch Leaderboard
$leaderboard = [];
if ($is_authenticated) {
    try {
        $sqlLoad = "SELECT id as user_id, email, roll_no, coins FROM users WHERE coins > 0 ORDER BY coins DESC";
        $stmt = $pdo->query($sqlLoad);
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cross-Origin-Opener-Policy" content="unsafe-none">
    <title>Infinity Coin System | Admin 2FA</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #bc13fe; --bg: #05020a; --glass: rgba(255, 255, 255, 0.05); }
        body { background-color: var(--bg); color: white; font-family: 'Space Mono', monospace; margin: 0; padding: 20px; }
        h1, h2 { font-family: 'Orbitron', sans-serif; color: var(--primary); text-transform: uppercase; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: var(--glass); border: 1px solid rgba(188, 19, 254, 0.3); padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        input, select, button { width: 100%; padding: 12px; margin: 10px 0; background: rgba(0,0,0,0.5); border: 1px solid #333; color: white; border-radius: 6px; font-family: inherit; }
        button { background: var(--primary); border: none; font-weight: bold; cursor: pointer; }
        .login-screen { display: <?php echo $is_authenticated ? 'none' : 'flex'; ?>; flex-direction: column; align-items: center; justify-content: center; height: 90vh; text-align: center; }
        #step2Form { display: none; margin-top: 20px; width: 100%; max-width: 400px; }
        .success { color: #0f0; border: 1px solid #0f0; padding: 10px; margin-bottom: 20px; text-align: center; }
        .error { color: #f00; border: 1px solid #f00; padding: 10px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$is_authenticated): ?>
            <div class="login-screen">
                <h1>Infinity Banking (2FA)</h1>
                <p>Identity must be verified through two phases.</p>
                <div id="authError" class="error" style="display: <?php echo isset($auth_error) ? 'block' : 'none'; ?>;"><?php echo $auth_error ?? ''; ?></div>
                
                <div id="step1">
                    <button id="googleLoginBtn" style="background: white; color: #333; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20">
                        Phase 1: Google Verifier
                    </button>
                </div>

                <div id="step2Form" class="card">
                    <p id="verifiedEmail" style="color: var(--primary); font-weight: bold; margin-bottom: 15px;"></p>
                    <form method="POST">
                        <input type="hidden" name="auth_step" value="2">
                        <input type="hidden" name="google_email" id="googleEmailHidden">
                        <label>Admin Username</label>
                        <input type="text" name="username" required>
                        <label>Security Password</label>
                        <input type="password" name="password" required>
                        <button type="submit">Phase 2: Finalize</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div id="adminDashboard">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h1>Vault Access</h1>
                    <div style="display: flex; gap: 10px;">
                        <a href="../admin/index.php" style="padding: 10px 15px; background: #333; color: white; text-decoration: none; border-radius: 5px; font-size: 0.8rem;">Admin Panel</a>
                        <button onclick="logout()" style="width: auto; background: #333;">Sign Out</button>
                    </div>
                </div>

                <?php echo $message; ?>

                <div class="card">
                    <h2>Credit Coins</h2>
                    <form method="POST" id="coinForm">
                        <input type="hidden" name="action" value="add_coins">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                            <div style="font-size: 0.8rem; color: var(--primary); opacity: 0.7;">Note: Credits require both numeric User ID and official Email.</div>
                        </div>
                        <label>Numeric User ID</label>
                        <input type="number" name="user_id" placeholder="e.g., 5" required>
                        <label>Participant Email</label>
                        <input type="email" name="email" placeholder="e.g., student@psgtech.ac.in" required>
                        <label>Amount</label>
                        <select name="amount" required><option value="10">10 Coins</option><option value="20">20 Coins</option><option value="50">50 Coins</option><option value="100">100 Coins</option></select>
                        <button type="submit">Execute Transaction</button>
                    </form>
                </div>

                <div class="card">
                    <h2>Live Ledger</h2>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="color: var(--primary); text-align: left; border-bottom: 1px solid #333;">
                            <th style="padding: 10px;">User ID</th>
                            <th>Participant</th>
                            <th>Balance</th>
                        </tr>
                        <?php foreach ($leaderboard as $row): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 10px; font-weight: bold; color: var(--primary);">#<?php echo $row['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['roll_no'] ?? 'N/A'); ?><br><small style="opacity: 0.5;"><?php echo $row['email']; ?></small></td>
                            <td style="color: gold; font-weight: bold;"><?php echo $row['coins']; ?> 🪙</td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithPopup, signOut } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "AIzaSyCi-4p4cyahglxocOsHIG2oT6O05nfpOtk",
            authDomain: "infinity-2k26.firebaseapp.com",
            projectId: "infinity-2k26",
            storageBucket: "infinity-2k26.firebasestorage.app"
        };
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();
        const whitelist = <?php echo json_encode($authorized_admins); ?>;

        <?php if (!$is_authenticated): ?>
        document.getElementById('googleLoginBtn').addEventListener('click', async () => {
            try {
                const result = await signInWithPopup(auth, provider);
                const email = result.user.email;
                if (whitelist.includes(email)) {
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2Form').style.display = 'block';
                    document.getElementById('verifiedEmail').innerText = "Verified: " + email;
                    document.getElementById('googleEmailHidden').value = email;
                } else {
                    alert("Unauthorized Email: " + email);
                    await signOut(auth);
                }
            } catch (err) { alert(err.message); }
        });
        <?php else: ?>
        window.logout = () => { window.location.href = "logout.php"; };

        document.getElementById('coinForm').addEventListener('submit', async (e) => {
            const btn = e.target.querySelector('button');
            btn.innerText = "Syncing Vault...";
            btn.disabled = true;
            // Native sync logic (simplified for brevity, already established in previous versions)
        });
        <?php endif; ?>
    </script>
</body>
</html>
