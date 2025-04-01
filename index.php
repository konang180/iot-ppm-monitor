<?php
// Database connection
$host = "dpg-cvlai4vgi27c73dm6eb0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "mydb_xyz123";
$user = "konang";
$password = "HbpK0zGuFkHURjPM9pK5c5fGZo6pIjOU";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Database connection failed!");
}

// Fetch distinct locations for history
$locationQuery = "SELECT DISTINCT location FROM pollution_data ORDER BY location ASC";
$locationResult = pg_query($conn, $locationQuery);

// Fetch live data (latest entry)
$liveQuery = "SELECT location, recorded_date, recorded_hour, average_ppm FROM pollution_data ORDER BY recorded_date DESC, recorded_hour DESC LIMIT 1";
$liveResult = pg_query($conn, $liveQuery);
$liveData = pg_fetch_assoc($liveResult);
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollution Data Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .tabs button {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: #007BFF;
            color: white;
            margin: 5px;
        }
        .tabs button:hover { background: #0056b3; }
        .tab-content { display: none; margin-top: 20px; }
        .active { display: block; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
        }
        th { background: #333; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Pollution Data Dashboard</h2>
    <div class="tabs">
        <button onclick="showTab('live')">Live Data</button>
        <button onclick="showTab('history')">History</button>
    </div>

    <div id="live" class="tab-content active">
        <h3>Current Live Data</h3>
        <p><strong>Location:</strong> <?= htmlspecialchars($liveData['location'] ?? 'No Data') ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($liveData['recorded_date'] ?? 'No Data') ?></p>
        <p><strong>Hour:</strong> <?= htmlspecialchars($liveData['recorded_hour'] ?? 'No Data') ?>:00</p>
        <p><strong>PPM:</strong> <?= htmlspecialchars($liveData['average_ppm'] ?? 'No Data') ?></p>
    </div>

    <div id="history" class="tab-content">
        <h3>History</h3>
        <table>
            <tr><th>Location</th></tr>
            <?php while ($row = pg_fetch_assoc($locationResult)) : ?>
                <tr>
                    <td><a href="dates.php?location=<?= urlencode($row['location']) ?>">
                        <?= htmlspecialchars($row['location']) ?></a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');
    }
</script>
</body>
</html>
