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

// Fetch pollution data
$query = "
    SELECT l.name AS location, p.recorded_date, p.recorded_hour, p.average_ppm 
    FROM pollution_data p
    JOIN locations l ON p.location_id = l.id
    ORDER BY p.recorded_date DESC, p.recorded_hour DESC";
$result = pg_query($conn, $query);
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
    </style>
</head>
<body>

<h2>Pollution Data</h2>
<table>
    <tr>
        <th>Location</th>
        <th>Date</th>
        <th>Hour</th>
        <th>Average PPM</th>
    </tr>
    <?php while ($row = pg_fetch_assoc($result)) : ?>
    <tr>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td><?= htmlspecialchars($row['recorded_date']) ?></td>
        <td><?= htmlspecialchars($row['recorded_hour']) ?>:00</td>
        <td><?= htmlspecialchars($row['average_ppm']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

<?php pg_close($conn); ?>
