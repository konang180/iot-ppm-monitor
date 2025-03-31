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

// Get data from NodeMCU
$ppm = $_POST['ppm'] ?? null; // PPM value
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
$check_result = pg_query_params($conn, $check_query, [$location_id, $date, $hour]);

if (pg_num_rows($check_result) > 0) {
    // Update existing record (calculate new average)
    $update_query = "
        UPDATE pollution_data 
        SET average_ppm = (average_ppm + $1) / 2 
        WHERE location_id = $2 AND recorded_date = $3 AND recorded_hour = $4";
    pg_query_params($conn, $update_query, [$ppm, $location_id, $date, $hour]);
} else {
    // Insert new record
    $insert_query = "
        INSERT INTO pollution_data (location_id, recorded_date, recorded_hour, average_ppm) 
        VALUES ($1, $2, $3, $4)";
    pg_query_params($conn, $insert_query, [$location_id, $date, $hour, $ppm]);
}

echo "Data received successfully!";
pg_close($conn);
?>
