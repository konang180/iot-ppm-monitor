<?php
// Database connection
include('db_config.php');

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Database connection failed!");
}
// Get data from NodeMCU
$ppm = $_POST['ppm'] ?? null;
date_default_timezone_set('Asia/Kolkata');

$hour = date('H');
$date = date('Y-m-d');
$now = date('Y-m-d H:i:s');

if ($ppm === null) {
    die("No data received!");
}

// Find active location
$query = "SELECT id, name FROM locations WHERE status = TRUE LIMIT 1";

$result = pg_query($conn, $query);
$location = pg_fetch_assoc($result);

if (!$location) {
    die("No active location found!");
}

$location_id = $location['id'];
$location_name = $location['name'];

// Insert pollution data
$insert_query = "
    INSERT INTO pollution_data (location_id, recorded_date, recorded_hour, average_ppm) 
    VALUES ($1, $2, $3, $4)";
pg_query_params($conn, $insert_query, [$location_id, $date, $hour, $ppm]);

// 🚨 FAST2SMS ALERT LOGIC
if ($ppm > 700) {
    // Check last alert time
    $alert_check_query = "SELECT last_sent FROM sms_alerts WHERE location_id = $1 ORDER BY last_sent DESC LIMIT 1";
    $alert_result = pg_query_params($conn, $alert_check_query, [$location_id]);
    $alert_data = pg_fetch_assoc($alert_result);

    $send_sms = false;

    if (!$alert_data) {
        $send_sms = true;
    } else {
        $last_sent = strtotime($alert_data['last_sent']);
        $current_time = time();
        $diff_minutes = ($current_time - $last_sent) / 60;

        if ($diff_minutes >= 30) {
            $send_sms = true;
        }
    }

    if ($send_sms) {
        // Send SMS using Fast2SMS API with quick_sms route
        $fields = array(
            "sender_id" => "FSTSMS",
            "message" => "⚠️ ALERT: High pollution at $location_name. PPM: $ppm at $now.",
            "language" => "english",
            "route" => "quick_sms",    // Updated route here
            "numbers" => "7085472046" // Replace with actual recipient number(s)
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                "authorization: UbOyyvutZSUWqw9aVPqijviBtCKMR2GKGTvAikbWkdA7CGhSbSjGnid7UTaq", // Replace this with your API key
                "accept: */*",
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        if(curl_errno($curl)) {
            error_log("Fast2SMS cURL error: " . curl_error($curl));
        }
        curl_close($curl);

        // Record this alert
        $update_alert_query = "
            INSERT INTO sms_alerts (location_id, last_sent) VALUES ($1, $2)";
        pg_query_params($conn, $update_alert_query, [$location_id, $now]);
    }
}

echo "Data received successfully!";
pg_close($conn);
?>
