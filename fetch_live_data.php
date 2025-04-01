<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = "dpg-cvlai4vgi27c73dm6eb0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "mydb_xyz123";
$user = "konang";
$password = "HbpK0zGuFkHURjPM9pK5c5fGZo6pIjOU";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Fetch the latest active location
$query = "SELECT id, name FROM locations ORDER BY id DESC LIMIT 1";
$result = pg_query($conn, $query);

if (!$result || pg_num_rows($result) == 0) {
    echo json_encode(["error" => "No active location found"]);
    exit;
}

$location = pg_fetch_assoc($result);
$location_id = $location['id'];

// Fetch the latest pollution data for this location
$query = "
    SELECT average_ppm, recorded_date, recorded_hour 
    FROM pollution_data 
    WHERE location_id = $location_id 
    ORDER BY recorded_date DESC, recorded_hour DESC 
    LIMIT 1";
$result = pg_query($conn, $query);

if (!$result || pg_num_rows($result) == 0) {
    echo json_encode(["error" => "No data recorded for this location"]);
    exit;
}

$data = pg_fetch_assoc($result);
$data['location'] = $location['name'];

echo json_encode($data);

pg_close($conn);
?>
