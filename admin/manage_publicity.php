<?php
require_once 'config.php';
check_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Publicity Members | Infinity 2k26</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            margin-bottom: 40px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            margin: 0;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
        }
        input {
            flex: 1;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 8px;
        }
        button {
            padding: 12px 25px;
            background: var(--neon-purple);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .member-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .member-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid var(--neon-purple);
        }
        .btn-delete {
            background: rgba(255, 77, 77, 0.2);
            color: #ff4d4d;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>INFINITY ADMIN</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="manage_publicity.php" class="nav-item active">Manage Publicity</a>
        <a href="registrations.php" class="nav-item">Registrations</a>
        <a href="alumni_list.php" class="nav-item">Alumni Registrations</a>
        <a href="../publicity.php" class="nav-item" target="_blank">Publicity Portal <i class="bi bi-box-arrow-up-right"></i></a>
        <a href="export.php" class="nav-item">Export Reports</a>
        <br><br>
        <a href="logout.php" class="nav-item" style="color: #ff4d4d;">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Publicity Members</h1>
        </div>

        <div class="form-card">
            <input type="text" id="memberName" placeholder="Enter new member name">
            <button onclick="addMember()">ADD MEMBER</button>
        </div>

        <div id="membersList" class="member-list">
            <!-- Loaded via JS -->
        </div>
    </div>

    <script>
        const API_URL = '../php/api_publicity_members.php';

        async function fetchMembers() {
            const res = await fetch(API_URL);
            const members = await res.json();
            const container = document.getElementById('membersList');
            container.innerHTML = '';
            
            members.forEach(m => {
                container.innerHTML += `
                    <div class="member-item">
                        <span>${m.name}</span>
                        <button class="btn-delete" onclick="removeMember(${m.id})"><i class="bi bi-trash"></i> Remove</button>
                    </div>
                `;
            });
        }

        async function addMember() {
            const name = document.getElementById('memberName').value;
            if(!name) return;

            await fetch(API_URL, {
                method: 'POST',
                body: JSON.stringify({ name })
            });

            document.getElementById('memberName').value = '';
            fetchMembers();
        }

        async function removeMember(id) {
            if(!confirm("Are you sure?")) return;
            
            await fetch(API_URL, {
                method: 'DELETE',
                body: JSON.stringify({ id })
            });
            fetchMembers();
        }

        fetchMembers();
    </script>
</body>
</html>
