<?php
// Database connection
include('db_config.php');

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Database connection failed!");
}

// Fetch available locations
$location_query = "SELECT DISTINCT location_id FROM pollution_data ORDER BY location_id";
$location_result = pg_query($conn, $location_query);
$locations = pg_fetch_all($location_result);

// Fetch all locations (id and name only) for Compare tab
$loc_query = "SELECT id, name FROM locations ORDER BY name";
$loc_result = pg_query($conn, $loc_query);
$all_locations = pg_fetch_all($loc_result);

function getHourlyData($conn, $location_id) {
    $query = "
        SELECT recorded_hour, AVG(average_ppm) AS average_ppm, recorded_date
        FROM pollution_data
        WHERE location_id = $1
        GROUP BY recorded_hour, recorded_date
        ORDER BY recorded_hour
    ";
    $result = pg_query_params($conn, $query, [$location_id]);
    return pg_fetch_all($result);
}

$compare_result = null;
$loc1_id = $loc2_id = null;
$loc1_date = $loc2_date = '';

if (isset($_GET['location1']) && isset($_GET['location2'])) {
    $loc1_id = intval($_GET['location1']);
    $loc2_id = intval($_GET['location2']);

    $loc1_data = getHourlyData($conn, $loc1_id);
    $loc2_data = getHourlyData($conn, $loc2_id);

    // Get location names
    $loc1_name = '';
    $loc2_name = '';
    foreach ($all_locations as $loc) {
        if ($loc['id'] == $loc1_id) $loc1_name = $loc['name'];
        if ($loc['id'] == $loc2_id) $loc2_name = $loc['name'];
    }

    // Combine unique hours from both locations
    $hours = [];
    if ($loc1_data) foreach ($loc1_data as $d) $hours[$d['recorded_hour']] = true;
    if ($loc2_data) foreach ($loc2_data as $d) $hours[$d['recorded_hour']] = true;
    $hours = array_keys($hours);
    sort($hours);

    // Initialize ppm arrays for both locations with 0
    $loc1_ppm = array_fill_keys($hours, 0);
    $loc2_ppm = array_fill_keys($hours, 0);

    // Fill ppm data from DB
    if ($loc1_data) {
        foreach ($loc1_data as $row) {
            $loc1_ppm[$row['recorded_hour']] = round(floatval($row['average_ppm']), 2);
        }
        $loc1_date = $loc1_data[0]['recorded_date'] ?? '';
    }
    if ($loc2_data) {
        foreach ($loc2_data as $row) {
            $loc2_ppm[$row['recorded_hour']] = round(floatval($row['average_ppm']), 2);
        }
        $loc2_date = $loc2_data[0]['recorded_date'] ?? '';
    }

    // Prepare data for JavaScript
    $compare_result = [
        'hours' => $hours,
        'loc1' => ['name' => $loc1_name, 'ppm' => $loc1_ppm, 'date' => $loc1_date],
        'loc2' => ['name' => $loc2_name, 'ppm' => $loc2_ppm, 'date' => $loc2_date],
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
    /* Slimmer tab buttons — adjust padding and font size, keep existing design */
.tab-button {
    font-size: 16px;
    padding: 12px 25px;
}

#compareForm {
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center; /* center items horizontally */
    max-width: 500px;
    margin: 0 auto 30px auto;
}

#compareForm label {
    width: 100%;
    text-align: center;
    font-weight: 600;
    color: white;
}

#compareForm select {
    min-width: 200px;
    width: 100%;
    max-width: 300px;
    padding: 7px 12px;
    font-size: 14px;
    border: 1.5px solid rgba(255, 255, 255, 0.6);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.15);
    
    outline-offset: 2px;
    cursor: pointer;
    backdrop-filter: blur(10px);
}


#compareForm button {
    padding: 10px 26px;
    font-weight: 700;
    font-size: 15px;
    color: #162040;
    background: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(255, 255, 255, 0.4);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

#compareForm button:hover {
    background-color: #cce5ff;
    box-shadow: 0 8px 20px rgba(204, 229, 255, 0.7);
}

.graph-container {
  height: 10px;  /* reduce this */
  width: 100%;
}


</style>
</head>
<body>

<div class="container">
    <!-- MENU BAR -->
    <div class="tab-menu">
        <button class="tab-button active-btn" data-tab="live" onclick="showTab('live', this)">Live Data</button>
        <button class="tab-button" data-tab="history" onclick="showTab('history', this)">History</button>
        <button class="tab-button" data-tab="compare" onclick="showTab('compare', this)">Compare</button>
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
                            <?= htmlspecialchars($location_name) ?>
                        </button>
                    <?php } ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No historical data available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- COMPARE TAB -->
    <div id="compare" class="tab-content" style="padding:20px; max-width:900px; margin:auto;">
        <!-- Compare Form -->

   <form id="compareForm" method="GET">
    <label for="location1">Select Location 1:</label>
    <select name="location1" id="location1" required>
        <option value="" disabled <?= $loc1_id === null ? 'selected' : '' ?>>-- Select --</option>
        <?php foreach ($all_locations as $loc): ?>
            <option value="<?= (int)$loc['id'] ?>" <?= ($loc1_id !== null && $loc1_id == $loc['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="location2">Select Location 2:</label>
    <select name="location2" id="location2" required>
        <option value="" disabled <?= $loc2_id === null ? 'selected' : '' ?>>-- Select --</option>
        <?php foreach ($all_locations as $loc): ?>
            <option value="<?= (int)$loc['id'] ?>" <?= ($loc2_id !== null && $loc2_id == $loc['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Compare</button>
</form>


        <?php if ($compare_result): ?>
            <canvas id="compareChart" height="200" style="max-width:100%;"></canvas>

            <p style="margin-top:15px; font-style: italic; color:#555; text-align:center;">
                <?= htmlspecialchars($compare_result['loc1']['name']) ?> recorded on <?= htmlspecialchars($compare_result['loc1']['date']) ?><br>
                <?= htmlspecialchars($compare_result['loc2']['name']) ?> recorded on <?= htmlspecialchars($compare_result['loc2']['date']) ?>
            </p>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('compareChart').getContext('2d');

                // Format hours like 7:00 AM etc
                const labels = <?= json_encode(array_map(function($h) {
                    $hour24 = intval($h);
                    $ampm = $hour24 >= 12 ? 'PM' : 'AM';
                    $hour12 = $hour24 % 12 === 0 ? 12 : $hour24 % 12;
                    return $hour12 . ':00 ' . $ampm;
                }, $compare_result['hours'])) ?>;

                const loc1Data = <?= json_encode(array_values($compare_result['loc1']['ppm'])) ?>;
                const loc2Data = <?= json_encode(array_values($compare_result['loc2']['ppm'])) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: <?= json_encode($compare_result['loc1']['name']) ?>,
                data: loc1Data,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                barThickness: 20  // <-- Add this line here
            },
            {
                label: <?= json_encode($compare_result['loc2']['name']) ?>,
                data: loc2Data,
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                barThickness: 20  // <-- And here
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                title: { display: true, text: 'Average PPM' }
            },
            y: {
                title: { display: true, text: 'Hour of Day' }
            }
        },
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        }
    }
});

            </script>
        <?php endif; ?>
    </div>
</div>

<script>
    function showTab(tab, button) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.getElementById(tab).classList.add('active');

        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active-btn'));
        button.classList.add('active-btn');
    }

    // Automatically switch to Compare tab if compare query params present
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        if (params.has('location1') && params.has('location2')) {
            const compareBtn = document.querySelector('.tab-button[data-tab="compare"]');
            if (compareBtn) {
                showTab('compare', compareBtn);
            }
        }
    });

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
                    hours = hours % 12 || 12;
                    document.getElementById('timestamp').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
                } else {
                    document.getElementById('timestamp').textContent = 'N/A';
                }
            })
            .catch(err => {
                document.getElementById('live-location').textContent = 'Error fetching data';
                document.getElementById('live-ppm').textContent = 'Error fetching data';
                document.getElementById('timestamp').textContent = 'Error fetching data';
                console.error(err);
            });
    }

    fetchLiveData();
    setInterval(fetchLiveData, 15000);
</script>

</body>
</html>
