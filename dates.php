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

// Fetch unique dates for this location
$query = "SELECT DISTINCT DATE(timestamp) AS date FROM air_quality_data WHERE location = '$location' ORDER BY date DESC";
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
        }
        .date-list {
            list-style: none;
            padding: 0;
        }
        .date-list li {
            margin: 10px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            cursor: pointer;
            display: inline-block;
        }
        .date-list li:hover {
            background: #e2e6ea;
        }
    </style>
</head>
<body>

<h1>Recorded Dates for <?php echo htmlspecialchars($location); ?></h1>

<ul class="date-list">
    <?php
    if ($dates) {
        foreach ($dates as $row) {
            echo "<li onclick=\"window.location.href='graph.php?location=" . urlencode($location) . "&date=" . urlencode($row['date']) . "'\">" . htmlspecialchars($row['date']) . "</li>";
        }
    } else {
        echo "<p>No data recorded for this location.</p>";
    }
    ?>
</ul>

</body>
</html>
