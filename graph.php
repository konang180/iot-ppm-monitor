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
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!$location || !$date) {
    echo "Location or Date not specified.";
    exit;
}

// Fetch location_id based on location name
$loc_query = "SELECT id, name FROM locations WHERE name = '$location'";
$loc_result = pg_query($conn, $loc_query);
$loc_data = pg_fetch_assoc($loc_result);

if (!$loc_data) {
    echo "Invalid location.";
    exit;
}

$location_id = $loc_data['id'];
$location_name = $loc_data['name'];

// Fetch average ppm for each hour on the selected date
$query = "
    SELECT recorded_hour, AVG(average_ppm) AS average_ppm
    FROM pollution_data
    WHERE location_id = '$location_id' AND recorded_date = '$date'
    GROUP BY recorded_hour
    ORDER BY recorded_hour ASC
";
$result = pg_query($conn, $query);
$ppm_data = pg_fetch_all($result);
pg_close($conn);

if (!$ppm_data) {
    echo "No data available for this location and date.";
    exit;
}

// Prepare data for the chart with AM/PM formatting
$hours = array_column($ppm_data, 'recorded_hour');
$average_ppms = array_column($ppm_data, 'average_ppm');

// Convert 24-hour format to 12-hour AM/PM format
$hours_formatted = array_map(function($hour) {
    return date("g A", strtotime("$hour:00"));
}, $hours);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPM Graph - <?php echo htmlspecialchars($location_name); ?> on <?php echo htmlspecialchars($date); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            max-width: 800px;
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
        canvas {
            max-width: 100%;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>PPM Graph for <?php echo htmlspecialchars($location_name); ?> on <?php echo htmlspecialchars($date); ?></h1>
    <canvas id="ppmChart"></canvas>
</div>

<script>
    var ctx = document.getElementById('ppmChart').getContext('2d');
    var ppmChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($hours_formatted); ?>,  // Now uses AM/PM format
            datasets: [{
                label: 'Average PPM',
                data: <?php echo json_encode($average_ppms); ?>,  // Average PPM values
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: false,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Hour of the Day'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Average PPM'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
