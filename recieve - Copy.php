<?php
// Database connection
include('db_config.php');

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Database connection failed!");
}

// Get data from NodeMCU
$ppm = $_POST['ppm'] ?? null; // PPM value
date_default_timezone_set('Asia/Kolkata'); // Set timezone to IST


$hour = date('H'); // Get current hour
$date = date('Y-m-d'); // Get current date

// Check if data is received
if ($ppm === null) {
    die("No data received!");
}

// Find active location
$query = "SELECT id FROM locations WHERE status = TRUE LIMIT 1";
$result = pg_query($conn, $query);
$location = pg_fetch_assoc($result);

if (!$location) {
    die("No active location found!");
}

$location_id = $location['id'];

// Check if record for the hour exists
$check_query = "SELECT id FROM pollution_data WHERE location_id = $1 AND recorded_date = $2 AND recorded_hour = $3";



    $insert_query = "
        INSERT INTO pollution_data (location_id, recorded_date, recorded_hour, average_ppm) 
        VALUES ($1, $2, $3, $4)";
    pg_query_params($conn, $insert_query, [$location_id, $date, $hour, $ppm]);


echo "Data received successfully!";
pg_close($conn);
?>
