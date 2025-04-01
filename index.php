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

if (!$result_locations) {
    die("Error fetching locations: " . pg_last_error($conn));
}

// Fetch latest pollution data
$query_latest = "
    SELECT l.name AS location, p.recorded_date, p.recorded_hour, p.average_ppm 
    FROM pollution_data p
    JOIN locations l ON p.location_id = l.id
    ORDER BY p.recorded_date DESC, p.recorded_hour DESC
    LIMIT 10";  // Show only recent 10 entries

$result_latest = pg_query($conn, $query_latest);

if (!$result_latest) {
    die("Error fetching latest data: " . pg_last_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollution Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
        }
        .tab {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #333;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .tab.active {
            background: #555;
        }
        .content {
            display: none;
            margin-top: 20px;
        }
        .content.active {
            display: block;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
        }
        th {
            background-color: #333;
            color: white;
        }
        a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>

<h2>Pollution Data</h2>

<!-- Tab Menu -->
<div>
    <div class="tab active" onclick="showTab('live')">Live Data</div>
    <div class="tab" onclick="showTab('history')">History</div>
</div>

<!-- Live Data -->
<div id="live" class="content active">
    <h3>Live Data (Latest Records)</h3>
    <table>
        <tr>
            <th>Location</th>
            <th>Date</th>
            <th>Hour</th>
            <th>Average PPM</th>
        </tr>
        <?php while ($row = pg_fetch_assoc($result_latest)) : ?>
        <tr>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= htmlspecialchars($row['recorded_date']) ?></td>
            <td><?= htmlspecialchars($row['recorded_hour']) ?>:00</td>
            <td><?= htmlspecialchars($row['average_ppm']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- History -->
<div id="history" class="content">
    <h3>Pollution History by Location</h3>
    <ul>
        <?php while ($row = pg_fetch_assoc($result_locations)) : ?>
            <li><a href="dates.php?location=<?= urlencode($row['location']) ?>"><?= htmlspecialchars($row['location']) ?></a></li>
        <?php endwhile; ?>
    </ul>
</div>

<script>
    function showTab(tabName) {
        document.querySelectorAll('.content').forEach(div => div.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        document.querySelector(`.tab[onclick="showTab('${tabName}')"]`).classList.add('active');
    }
</script>

</body>
</html>

<?php pg_close($conn); ?>
