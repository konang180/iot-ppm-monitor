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

// Fetch available locations
$location_query = "SELECT DISTINCT location_id FROM pollution_data ORDER BY location_id";
$location_result = pg_query($conn, $location_query);
$locations = pg_fetch_all($location_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollution Monitoring</title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
       background: linear-gradient(to right, #0a0f2c, #162040);
        color: white;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 10px;
    }

    .container {
        width: 100%;
        max-width: 1000px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    /* MENU BAR */
    .tab-menu {
        display: flex;
        justify-content: space-evenly;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        padding: 15px 0;
        border-bottom: 2px solid rgba(255, 255, 255, 0.15);
    }

    .tab-button {
        background: transparent;
        border: 2px solid #fff;
        color: white;
        font-size: 16px;
        padding: 12px 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-weight: 600;
    }

    .tab-button:hover, .tab-button.active-btn {
        background: white;
        color: #2c5364;
    }

    /* CONTENT SECTION */
    .tab-content {
        display: none;
        padding: 30px;
        animation: fadeIn 0.5s ease-in-out;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {opacity: 0;}
        to {opacity: 1;}
    }

    /* LIVE DATA CARDS */
    .live-data-card {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        backdrop-filter: blur(10px);
    }

    .live-data-card {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    text-align: center; /* <-- Add this line */
}


    .live-data-card span {
        font-weight: bold;
        font-size: 20px;
    }

    /* HISTORY BUTTONS */
    
.history-header {
    text-align: center;
    margin-bottom: 30px;
    background: rgba(255, 255, 255, 0.15);
    padding: 20px;
    border-radius: 15px;
    backdrop-filter: blur(12px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.location-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    padding: 15px 20px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.location-card:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.05);
    color: #000;
}

    @media (max-width: 600px) {
        .tab-menu {
            flex-direction: column;
            align-items: center;
            padding: 10px;
        }

        .tab-button {
            width: 90%;
            margin: 10px 0;
        }

        .tab-content {
            padding: 20px;
        }
    }
</style>

<body>
<div class="container">
    <!-- MENU BAR -->
    <div class="tab-menu">
        <button class="tab-button active-btn" onclick="showTab('live', this)">Live Data</button>
        <button class="tab-button" onclick="showTab('history', this)">History</button>
    </div>

    <!-- LIVE TAB -->
    <div id="live" class="tab-content active">
        <h2 style="margin-bottom: 20px; text-align:center;">Live Air Pollution Data</h2>

        <div class="live-data-card">
            <p><strong>⏱ Time:</strong> <span id="timestamp">Fetching...</span></p>
            <br><br>
            <p><strong>📍 Location:</strong> <span id="live-location">Fetching...</span></p>
            <p><strong>🌫️ PPM:</strong> <span id="live-ppm" style="color:yellow;">Fetching...</span></p>
            
        </div>
    </div>

    <!-- HISTORY TAB -->
<div id="history" class="tab-content">
    <div class="history-header">
        <h2>📍 Location History</h2>
        <p>Select a location to view historical pollution data.</p>
    </div>
    <div class="history-grid">
        <?php if ($locations): ?>
            <?php foreach ($locations as $loc): ?>
                <?php
                $location_id = $loc['location_id'];
                $query = "SELECT name FROM locations WHERE id = $1";
                $result = pg_query_params($conn, $query, array($location_id));
                if ($result && pg_num_rows($result) > 0) {
                    $location = pg_fetch_assoc($result);
                    $location_name = $location['name'];
                ?>
                    <button class="location-card" onclick="window.location.href='dates.php?location=<?= urlencode($location_id) ?>'">
                        <?= $location_name ?>
                    </button>
                <?php } ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No historical data available.</p>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
    function showTab(tab, button) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.getElementById(tab).classList.add('active');

        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active-btn'));
        button.classList.add('active-btn');
    }

    function fetchLiveData() {
        fetch('fetch_live_data.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('live-location').textContent = data.location || 'N/A';
                document.getElementById('live-ppm').textContent = data.average_ppm || 'N/A';
                if (data.timestamp) {
                    const dateObj = new Date(data.timestamp);
                    let hours = dateObj.getHours();
                    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                    const seconds = String(dateObj.getSeconds()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12; // Convert to 12-hour format
                    const formattedTime = `${hours}:${minutes}:${seconds} ${ampm}`;
                    document.getElementById('timestamp').textContent = formattedTime;
                } else {
                    document.getElementById('timestamp').textContent = 'N/A';
                }
            })
            .catch(error => console.error('Error fetching live data:', error));
    }

    setInterval(fetchLiveData, 5000);
</script>
</body>
</html>
<?php pg_close($conn); ?>
