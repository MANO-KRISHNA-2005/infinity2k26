<?php
require_once 'config.php';

$error = '';

// Step 2: Handle Username/Password verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_step']) && $_POST['login_step'] == '2') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $google_email = $_POST['google_email'] ?? '';

    // Verify Google email is in whitelist and credentials match
    if (in_array($google_email, AUTHORIZED_ADMINS) && $user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $google_email;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials or unauthorized email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cross-Origin-Opener-Policy" content="unsafe-none">
    <title>Admin 2FA Login | Infinity 2k26</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --neon-purple: #bc13fe; --bg-dark: #0a050f; }
        body { background-color: var(--bg-dark); color: white; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { background: rgba(255, 255, 255, 0.05); padding: 40px; border-radius: 15px; border: 1px solid var(--neon-purple); box-shadow: 0 0 20px rgba(188, 19, 254, 0.2); width: 100%; max-width: 400px; text-align: center; }
        h1 { font-family: 'Orbitron', sans-serif; color: var(--neon-purple); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 8px; color: #ccc; }
        input { width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 5px; color: white; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: var(--neon-purple); border: none; border-radius: 5px; color: white; font-weight: bold; cursor: pointer; font-family: 'Orbitron', sans-serif; margin-top: 10px; }
        .btn-google { background: white; color: #333; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .error { color: #ff4d4d; margin-bottom: 15px; font-size: 0.9em; }
        #step2Form { display: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>ADMIN 2FA</h1>
        <div id="errorMessage" class="error"><?php echo $error; ?></div>

        <!-- Step 1: Google Login -->
        <div id="step1">
            <p style="margin-bottom: 25px; opacity: 0.8;">Phase 1: Identity Verification</p>
            <button id="googleLoginBtn" class="btn btn-google">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20">
                Sign in with Google
            </button>
        </div>

        <!-- Step 2: Password Login -->
        <div id="step2Form">
            <p id="googleEmailDisplay" style="color: var(--neon-purple); font-weight: bold; margin-bottom: 20px;"></p>
            <form method="POST">
                <input type="hidden" name="login_step" value="2">
                <input type="hidden" name="google_email" id="googleEmailHidden">
                <div class="form-group">
                    <label>Internal Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Security Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">VERIFY & ENTER</button>
            </form>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

        // Reuse config but we need it here for step 1
        const firebaseConfig = {
            apiKey: "AIzaSyCi-4p4cyahglxocOsHIG2oT6O05nfpOtk",
            authDomain: "infinity-2k26.firebaseapp.com",
            projectId: "infinity-2k26",
            storageBucket: "infinity-2k26.firebasestorage.app",
            messagingSenderId: "620576782678",
            appId: "1:620576782678:web:5d6f36a182465d26ee7ef7"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();

        const whitelist = <?php echo json_encode(AUTHORIZED_ADMINS); ?>;
        const googleLoginBtn = document.getElementById('googleLoginBtn');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2Form');
        const emailDisp = document.getElementById('googleEmailDisplay');
        const emailHidden = document.getElementById('googleEmailHidden');

        googleLoginBtn.addEventListener('click', async () => {
            googleLoginBtn.disabled = true;
            googleLoginBtn.innerText = "Checking...";
            
            try {
                const result = await signInWithPopup(auth, provider);
                const email = result.user.email;
                
                if (whitelist.includes(email)) {
                    step1.style.display = 'none';
                    step2.style.display = 'block';
                    emailDisp.innerText = "Verified: " + email;
                    emailHidden.value = email;
                } else {
                    document.getElementById('errorMessage').innerText = "Access Denied: " + email + " is not authorized.";
                    googleLoginBtn.disabled = false;
                    googleLoginBtn.innerHTML = `<img src=\"https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg\" width=\"20\"> Sign in with Google`;
                }
            } catch (error) {
                document.getElementById('errorMessage').innerText = "Auth Error: " + error.message;
                googleLoginBtn.disabled = false;
                googleLoginBtn.innerHTML = `<img src=\"https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg\" width=\"20\"> Sign in with Google`;
            }
        });
    </script>
</body>
</html>
