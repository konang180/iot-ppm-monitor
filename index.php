<?php
// Database connection
$host = "dpg-cvlai4vgi27c73dm6eb0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "mydb_xyz123";
$user = "konang";
$password = "HbpK0zGuFkHURjPM9pK5c5fGZo6pIjOU";

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Database connection failed!");
}

// Fetch available locations
$location_query = "SELECT DISTINCT location_id FROM pollution_data ORDER BY location_id";
$location_result = pg_query($conn, $location_query);
$locations = pg_fetch_all($location_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollution Monitoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, black, #1a1a40);
            color: white;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 30px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .tab-menu {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .tab-button {
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            background: linear-gradient(to right, #018141, #014122);
            color: white;
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s;
            font-weight: bold;
        }
        .tab-button:hover {
            background: linear-gradient(to right, #feb47b, #ff7e5f);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.6);
        }
        .tab-content {
            display: none;
        }
        .active {
            display: block;
        }
        .location-button {
            padding: 12px 30px;
            margin: 10px;
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s;
        }
        .location-button:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 20px;
            }
            .tab-menu {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="tab-menu">
        <button class="tab-button" onclick="showTab('live')">Live Data</button>
        <button class="tab-button" onclick="showTab('history')">History</button>
    </div>
    
    <div id="live" class="tab-content active">
        <h2>Live Air Pollution Data</h2>
        <p><strong>Location:</strong> <span id="live-location">Fetching...</span></p>
        <p><strong>PPM:</strong> <span id="live-ppm">Fetching...</span></p>
    </div>
    
    <div id="history" class="tab-content">
        <h2>Location History</h2>
        <?php if ($locations): ?>
            <?php foreach ($locations as $loc): ?>
                <button class="location-button" onclick="window.location.href='dates.php?location=<?= urlencode($loc['location_id']) ?>'">
    Location <?= htmlspecialchars($loc['location_id']) ?>
</button>
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No historical data available.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.getElementById(tab).classList.add('active');
    }

    function fetchLiveData() {
        fetch('fetch_live_data.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('live-location').textContent = data.location || 'N/A';
                document.getElementById('live-ppm').textContent = data.average_ppm || 'N/A';
            })
            .catch(error => console.error('Error fetching live data:', error));
    }

    setInterval(fetchLiveData, 5000);
</script>

</body>
</html>
<?php pg_close($conn); ?>
