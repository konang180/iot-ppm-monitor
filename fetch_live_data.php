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

// Fetch the active location from the database
$query = "SELECT name FROM locations WHERE status = TRUE LIMIT 1";
$result = pg_query($conn, $query);

if (!$result || pg_num_rows($result) == 0) {
    echo json_encode(["error" => "No active location found"]);
    pg_close($conn);
    exit;
}

$location = pg_fetch_assoc($result);
$location_name = $location['name'];

// Get the ppm data from the POST request (simulating NodeMCU data)
$ppm = $_POST['ppm'] ?? null;

if ($ppm === null) {
    echo json_encode(["error" => "No PPM data received"]);
    pg_close($conn);
    exit;
}

// Prepare the data to send back (with location and ppm)
$data = [
    "average_ppm" => $ppm,
    "location" => $location_name,
    "timestamp" => date('Y-m-d H:i:s') // Add current timestamp
];

echo json_encode($data);

// Close the database connection
pg_close($conn);
?>
