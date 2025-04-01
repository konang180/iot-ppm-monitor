<?php
$host = "dpg-cvlai4vgi27c73dm6eb0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "mydb_xyz123";
$user = "konang";
$password = "HbpK0zGuFkHURjPM9pK5c5fGZo6pIjOU";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Fetch unique locations
$query = "SELECT DISTINCT location FROM air_quality_data";
$result = pg_query($conn, $query);
$locations = pg_fetch_all($result);
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Quality Monitoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .tab-container {
            margin: 20px;
        }
        .tab {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            background: #007bff;
            color: white;
            border-radius: 5px;
        }
        .tab:hover {
            background: #0056b3;
        }
        .tab-content {
            display: none;
            margin-top: 20px;
        }
        .active {
            display: block;
        }
        .location-list {
            list-style: none;
            padding: 0;
        }
        .location-list li {
            margin: 10px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            cursor: pointer;
            display: inline-block;
        }
        .location-list li:hover {
            background: #e2e6ea;
        }
    </style>
    <script>
        function showTab(tabId) {
            document.getElementById('live-tab').classList.remove('active');
            document.getElementById('history-tab').classList.remove('active');
            document.getElementById(tabId).classList.add('active');
        }
    </script>
</head>
<body>

<h1>Air Quality Monitoring</h1>

<div class="tab-container">
    <div class="tab" onclick="showTab('live-tab')">Live Data</div>
    <div class="tab" onclick="showTab('history-tab')">History</div>
</div>

<!-- Live Data (To be implemented later) -->
<div id="live-tab" class="tab-content">
    <h2>Live Data</h2>
    <p>Coming soon...</p>
</div>

<!-- History Tab -->
<div id="history-tab" class="tab-content active">
    <h2>Recorded Locations</h2>
    <ul class="location-list">
        <?php
        if ($locations) {
            foreach ($locations as $row) {
                echo "<li onclick=\"window.location.href='dates.php?location=" . urlencode($row['location']) . "'\">" . htmlspecialchars($row['location']) . "</li>";
            }
        } else {
            echo "<p>No data recorded yet.</p>";
        }
        ?>
    </ul>
</div>

</body>
</html>
