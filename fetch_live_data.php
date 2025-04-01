<?php
// Database connection
$host = "dpg-cvlai4vgi27c73dm6eb0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "mydb_xyz123";
$user = "konang";
$password = "HbpK0zGuFkHURjPM9pK5c5fGZo6pIjOU";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die(json_encode(["error" => "Database connection failed!"]));
}

// Get the latest active location
$location_query = "SELECT id, name FROM locations ORDER BY updated_at DESC LIMIT 1";
$location_result = pg_query($conn, $location_query);
$location_data = pg_fetch_assoc($location_result);

if (!$location_data) {
    echo json_encode(["error" => "No active location found"]);
    exit;
}

$location_id = $location_data['id'];
$location_name = $location_data['name'];

// Fetch latest real-time pollution data for the active location
$query = "
    SELECT ppm_value 
    FROM pollution_data 
    WHERE location_id = $location_id 
    ORDER BY recorded_date DESC, recorded_hour DESC, id DESC 
    LIMIT 1";

$result = pg_query($conn, $query);
$data = pg_fetch_assoc($result);

// Return data as JSON
echo json_encode([
    "location" => $location_name,
    "ppm" => $data['ppm_value'] ?? "--"
]);

pg_close($conn);
?>
