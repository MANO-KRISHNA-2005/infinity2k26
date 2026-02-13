<?php
require_once 'config.php';
check_auth();

// Fetch daily registrations count
$daily_query = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM registrations GROUP BY DATE(created_at) ORDER BY date ASC");
$daily_data = $daily_query->fetchAll();

// Fetch event-wise registration count
$event_query = $pdo->query("SELECT events, COUNT(*) as count FROM registrations GROUP BY events");
$event_data = $event_query->fetchAll();

// Flatten event counts (since multiple can be in one string)
$event_counts = [];
foreach ($event_data as $row) {
    if (empty($row['events'])) continue;
    $events = explode(", ", $row['events']);
    foreach ($events as $e) {
        $e = trim($e);
        if ($e) {
            $event_counts[$e] = ($event_counts[$e] ?? 0) + $row['count'];
        }
    }
}

// Convert to JS readable format
$daily_labels = json_encode(array_column($daily_data, 'date'));
$daily_values = json_encode(array_column($daily_data, 'count'));

$event_labels = json_encode(array_keys($event_counts));
$event_values = json_encode(array_values($event_counts));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Infinity 2k26</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .chart-card {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .chart-card h3 {
            margin-top: 0;
            font-size: 1rem;
            color: #eee;
            margin-bottom: 20px;
        }
        .btn-logout {
            color: #ff4d4d;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>INFINITY ADMIN</h2>
        <a href="index.php" class="nav-item active">Dashboard</a>
        <a href="manage_publicity.php" class="nav-item">Manage Publicity</a>
        <a href="registrations.php" class="nav-item">Registrations</a>
        <a href="alumni_list.php" class="nav-item">Alumni Registrations</a>
        <a href="../publicity.php" class="nav-item" target="_blank">Publicity Portal <i class="bi bi-box-arrow-up-right"></i></a>
        <a href="export.php" class="nav-item">Export Reports</a>
        <br><br>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Analytics Overview</h1>
        </div>

        <div class="stats-grid">
            <div class="chart-card">
                <h3>Daily Registration Trend</h3>
                <canvas id="dailyChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Event-wise Registration</h3>
                <canvas id="eventChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Daily Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php echo $daily_labels; ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo $daily_values; ?>,
                    borderColor: '#bc13fe',
                    backgroundColor: 'rgba(188, 19, 254, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { 
                responsive: true,
                scales: { 
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Event Chart
        const eventCtx = document.getElementById('eventChart').getContext('2d');
        new Chart(eventCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $event_labels; ?>,
                datasets: [{
                    label: 'Participants',
                    data: <?php echo $event_values; ?>,
                    backgroundColor: 'rgba(188, 19, 254, 0.6)',
                    borderColor: '#bc13fe',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: { 
                    x: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>
