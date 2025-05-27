<?php
include('db_config.php');

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$location = $_GET['location'] ?? '';

if (!$location) {
    http_response_code(400);
    echo json_encode(['error' => 'Location not specified']);
    exit;
}

// Get location_id
$loc_query = "SELECT id FROM locations WHERE name = $1";
$loc_result = pg_query_params($conn, $loc_query, [$location]);
$loc_data = pg_fetch_assoc($loc_result);

if (!$loc_data) {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid location']);
    exit;
}

$location_id = $loc_data['id'];

// Get latest recorded_date for this location
$date_query = "SELECT MAX(recorded_date) AS latest_date FROM pollution_data WHERE location_id = $1";
$date_result = pg_query_params($conn, $date_query, [$location_id]);
$date_data = pg_fetch_assoc($date_result);

if (!$date_data || !$date_data['latest_date']) {
    echo json_encode(['error' => 'No data available for this location']);
    exit;
}

$latest_date = $date_data['latest_date'];

// Fetch avg ppm per hour for latest_date
$data_query = "
    SELECT recorded_hour, AVG(average_ppm) AS average_ppm
    FROM pollution_data
    WHERE location_id = $1 AND recorded_date = $2
    GROUP BY recorded_hour
    ORDER BY recorded_hour ASC
";

$data_result = pg_query_params($conn, $data_query, [$location_id, $latest_date]);
$ppm_data = pg_fetch_all($data_result);

pg_close($conn);

if (!$ppm_data) {
    echo json_encode(['error' => 'No data available for this location on latest date']);
    exit;
}

echo json_encode([
    'location' => $location,
    'date' => $latest_date,
    'data' => $ppm_data,
]);
?>
