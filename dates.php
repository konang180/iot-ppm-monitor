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


// ... (connection code remains the same)
$location_id = isset($_GET['location']) ? $_GET['location'] : '';
if (empty($location_id)) {
    echo "Invalid location!";
    exit;
}
$loc_query = "SELECT id, name FROM locations WHERE id = $1";
$loc_result = pg_query_params($conn, $loc_query, array($location_id));
$loc_data = pg_fetch_assoc($loc_result);
if (!$loc_data) {
    echo "Invalid location.";
    exit;
}
$location_id = $loc_data['id'];
$location_name = $loc_data['name'];
$query = "SELECT DISTINCT DATE(recorded_date) AS date FROM pollution_data WHERE location_id = $1 ORDER BY date DESC";
$result = pg_query_params($conn, $query, array($location_id));
$dates = pg_fetch_all($result);
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Recorded Dates - <?php echo htmlspecialchars($location_name); ?></title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #0a0f2c, #162040);
      color: white;
    }

    .wrapper {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 40px 20px;
    }

    .container {
      max-width: 850px;
      width: 100%;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 20px;
      padding: 30px;
      backdrop-filter: blur(15px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
      margin-top: 0;
    }
   .header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }



     h1 {
      font-size: 20px;
      margin-bottom: 10px;
      text-align: left;
    }

    .location-subheading {

      text-align: center;
      font-size: 20px;
      font-weight: normal;
      margin-top: 10px;
      margin-bottom: 30px;
      color: #ddd;
    }

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


    .dates-container {
      background: rgba(255, 255, 255, 0.05);
      padding: 20px;
      border-radius: 15px;
      backdrop-filter: blur(8px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .date-list {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
    }

    .date-list button {
      background: rgba(255, 255, 255, 0.12);
      border: none;
      padding: 12px 22px;
      font-size: 16px;
      color: white;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      backdrop-filter: blur(6px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      min-width: 130px;
      text-transform: capitalize;
    }

    .date-list button:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.05);
    }

    @media (max-width: 600px) {
      .header-row {
        flex-direction: column;
        align-items: flex-start;
      }

      .container {
        padding: 20px;
      }

      .date-list {
        flex-direction: column;
        align-items: center;
      }

      .date-list button {
        width: 100%;
      }

     
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
     <div class="header-row">
        <div>
        <h1>Recorded dates</h1>
        
      </div>
      <button class="back-btn" onclick="window.history.back()">← Back</button>
      
    </div>
      <div class="location-subheading"><?php echo htmlspecialchars($location_name); ?></div>

      <div class="dates-container">
        <div class="date-list">
          <?php
          if ($dates) {
              foreach ($dates as $row) {
                  $dateObj = new DateTime($row['date']);
                  $formattedDate = $dateObj->format("j F Y"); // e.g., 4 April 2025
                  echo "<button onclick=\"window.location.href='graph.php?location=" . urlencode($location_name) . "&date=" . urlencode($row['date']) . "'\">" . htmlspecialchars($formattedDate) . "</button>";
              }
          } else {
              echo "<p>No data recorded for this location.</p>";
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>