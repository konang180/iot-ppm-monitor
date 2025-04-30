<?php
include('db_config.php');

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

$hours = array_column($ppm_data, 'recorded_hour');
$average_ppms = array_column($ppm_data, 'average_ppm');

// Format hours to AM/PM
$hours_formatted = array_map(function($hour) {
    return date("g A", strtotime("$hour:00"));
}, $hours);
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PPM Graph - <?php echo htmlspecialchars($location_name); ?> on <?php echo htmlspecialchars($date); ?></title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Same styling for body, container, etc. */
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #0a0f2c, #162040);
      color: white;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
    }

    /* Flexbox style for header to align button and text */
    .header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    /* Make back button smaller and align with graph title */
    .back-btn {
      background: transparent;
      border: 2px solid white;
      color: white;
      padding: 8px 16px;
      font-size: 14px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    .back-btn:hover {
      background: white;
      color: #0a0f2c;
    }

    /* Heading for the graph */
    h1 {
      font-size: 20px;
      margin-bottom: 10px;
      text-align: left;
    }

    /* Styling the tabs and graph as before */
    .tab-menu {
      display: flex;
      justify-content: center;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 12px;
      gap: 12px;
      margin-top: 20px;
    }

    .tab-button {
      background: transparent;
      border: 2px solid white;
      color: white;
      padding: 10px 20px;
      font-weight: bold;
      cursor: pointer;
      border-radius: 8px;
      text-transform: uppercase;
      transition: 0.3s ease;
    }

    .tab-button.active {
      background: white;
      color: #0a0f2c;
    }

    /* Graph section and styling */
    .graph-section {
      display: none;
    }

    .graph-section.active {
      display: block;
    }

    canvas {
      width: 100% !important;
      max-width: 100%;
      height: auto !important;
    }

    .graph-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(8px);
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
      margin-top: 20px;
    }
    p{
        text-align: center;
        font-size:20px;
    }
  </style>
</head>
<body>
   <div class="container">
    <div class="header-row">
        <div>
        <h1>PPM Graph</h1>
        
      </div>
      <button class="back-btn" onclick="window.history.back()">← Back</button>
      
    </div>
    <p><?php echo htmlspecialchars($location_name); ?> on <?php echo date("j F Y", strtotime($date)); ?></p>
    <div class="tab-menu">
      <button class="tab-button active" onclick="switchTab('line')">Line Graph</button>
      <button class="tab-button" onclick="switchTab('bar')">Bar Graph</button>
    </div>

    <!-- Line Graph -->
    <div id="line" class="graph-section active">
      <div class="graph-container">
        <canvas id="lineChart"></canvas>
      </div>
    </div>

    <!-- Bar Graph -->
    <div id="bar" class="graph-section">
      <div class="graph-container">
        <canvas id="barChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    function switchTab(tabId) {
      document.querySelectorAll('.graph-section').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

      document.getElementById(tabId).classList.add('active');
      document.querySelector(`.tab-button[onclick="switchTab('${tabId}')"]`).classList.add('active');
    }

    const labels = <?php echo json_encode($hours_formatted); ?>;
    const data = <?php echo json_encode($average_ppms); ?>;

    // Line Chart
    new Chart(document.getElementById('lineChart'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Average PPM',
          data: data,
          borderColor: '#38f9d7',
          backgroundColor: 'rgba(56, 249, 215, 0.2)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#38f9d7',
          pointRadius: 5
        }]
      },
      options: {
        responsive: true,
        plugins: {
          tooltip: {
            mode: 'index',
            intersect: false
          }
        },
        scales: {
          x: {
            title: { display: true, text: 'Hour of the Day', color: 'white' },
            ticks: { color: 'white' }
          },
          y: {
            title: { display: true, text: 'PPM', color: 'white' },
            ticks: { color: 'white' }
          }
        }
      }
    });

    // Bar Chart
    new Chart(document.getElementById('barChart'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Average PPM',
          data: data,
          backgroundColor: 'rgba(255, 206, 86, 0.6)',
          borderColor: 'rgba(255, 206, 86, 1)',
          borderWidth: 1
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          tooltip: {
            mode: 'nearest',
            intersect: false
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            title: { display: true, text: 'PPM', color: 'white' },
            ticks: { color: 'white' }
          },
          y: {
            title: { display: true, text: 'Hour', color: 'white' },
            ticks: { color: 'white' }
          }
        }
      }
    });
  </script>
</body>
</html>