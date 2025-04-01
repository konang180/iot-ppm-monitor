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

// Fetch all distinct locations
$query_locations = "
    SELECT DISTINCT l.name AS location 
    FROM pollution_data p
    JOIN locations l ON p.location_id = l.id
    ORDER BY location ASC";
$result_locations = pg_query($conn, $query_locations);

// Fetch latest pollution data
$query_latest = "
    SELECT l.name AS location, p.average_ppm 
    FROM pollution_data p
    JOIN locations l ON p.location_id = l.id
    ORDER BY p.recorded_date DESC, p.recorded_hour DESC
    LIMIT 1"; // Get only the latest record

$result_latest = pg_query($conn, $query_latest);
$latest_data = pg_fetch_assoc($result_latest);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Pollution Monitor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: linear-gradient(to bottom, black, #0b1a30);
            color: white;
            margin: 0;
            padding: 0;
        }

        /* Option menu (Tab buttons) */
        .tab-container {
            margin: 20px auto;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .tab {
            padding: 10px 20px;
            border: 2px solid white;
            background: transparent;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .tab.active {
            background: white;
            color: black;
        }

        .content {
            display: none;
            margin-top: 20px;
        }

        .content.active {
            display: block;
        }

        /* Live Data Section */
        .air-quality {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            width: 80%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }

        .air-quality h2 {
            margin: 10px 0;
            font-size: 22px;
        }

        .ppm {
            font-size: 24px;
            font-weight: bold;
            color: #ff4d4d;
        }

        /* History Section */
        .history-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .history-btn {
            padding: 10px 15px;
            border: none;
            background: white;
            color: black;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .history-btn:hover {
            background: #ddd;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .tab-container {
                flex-direction: column;
                gap: 5px;
            }
            .tab {
                width: 80%;
            }
            .air-quality {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<h2>Air Pollution Monitor</h2>

<!-- Tab Navigation (Option Menu) -->
<div class="tab-container">
    <button class="tab active" onclick="showTab('live')">Live Data</button>
    <button class="tab" onclick="showTab('history')">History</button>
</div>

<!-- Live Data -->
<div id="live" class="content active">
 <div class="air-quality">
    <h2>Live Air Pollution Data</h2>
    <p>Location: <strong id="location-name">Loading...</strong></p>
    <p class="ppm">Pollution Level: <span id="ppm-value">--</span></p>
</div>

</div>

<!-- History -->
<div id="history" class="content">
    <h3>Pollution History by Location</h3>
    <div class="history-list">
        <?php while ($row = pg_fetch_assoc($result_locations)) : ?>
            <button class="history-btn" onclick="window.location.href='dates.php?location=<?= urlencode($row['location']) ?>'">
                <?= htmlspecialchars($row['location']) ?>
            </button>
        <?php endwhile; ?>
    </div>
</div>

<script>
    function showTab(tabName) {
        document.querySelectorAll('.content').forEach(div => div.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        document.querySelector(`button[onclick="showTab('${tabName}')"]`).classList.add('active');
    }
</script>
<script>
function fetchLiveData() {
    fetch("fetch_live_data.php")
        .then(response => response.json())
        .then(data => {
            document.getElementById("location-name").innerText = data.location;
            document.getElementById("ppm-value").innerText = data.ppm;
        })
        .catch(error => console.error("Error fetching live data:", error));
}

// Refresh data every 15 seconds
setInterval(fetchLiveData, 15000);

// Fetch data when the page loads
fetchLiveData();
</script>


</body>
</html>

<?php pg_close($conn); ?>
