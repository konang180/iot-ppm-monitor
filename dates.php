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

$location = isset($_GET['location']) ? $_GET['location'] : '';

if (!$location) {
    echo "Location not specified.";
    exit;
}

// Fetch location_id based on location name
$loc_query = "SELECT id FROM locations WHERE name = '$location'";
$loc_result = pg_query($conn, $loc_query);
$loc_data = pg_fetch_assoc($loc_result);

if (!$loc_data) {
    echo "Invalid location.";
    exit;
}

$location_id = $loc_data['id'];

// Fetch unique dates for this location
$query = "SELECT DISTINCT DATE(recorded_date) AS date FROM pollution_data WHERE location_id = '$location_id' ORDER BY date DESC";
$result = pg_query($conn, $query);
$dates = pg_fetch_all($result);
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recorded Dates - <?php echo htmlspecialchars($location); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: linear-gradient(to bottom, black, #0a1f44);
            color: white;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 22px;
        }
        .date-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding: 0;
        }
        .date-list button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            color: white;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }
        .date-list button:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 15px;
            }
            .date-list {
                flex-direction: column;
            }
            .date-list button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Recorded Dates for <?php echo htmlspecialchars($location); ?></h1>

    <div class="date-list">
        <?php
        if ($dates) {
            foreach ($dates as $row) {
                echo "<button onclick=\"window.location.href='graph.php?location=" . urlencode($location) . "&date=" . urlencode($row['date']) . "'\">" . htmlspecialchars($row['date']) . "</button>";
            }
        } else {
            echo "<p>No data recorded for this location.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>
