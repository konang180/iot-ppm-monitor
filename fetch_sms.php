<?php
// fetch_sms.php
include('db_config.php');

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

$query = "SELECT * FROM sms_alerts ORDER BY last_sent DESC";
$result = pg_query($conn, $query);
$sms_alerts = pg_fetch_all($result);

pg_close($conn);
?>

<div class="container">
    <h1>SMS Alerts</h1>
    <?php if ($sms_alerts): ?>
    <table style="width:100%; border-collapse: collapse; color:#fff;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #fff; padding: 8px; text-align:left;">ID</th>
                <th style="border-bottom: 1px solid #fff; padding: 8px; text-align:left;">Location ID</th>
                <th style="border-bottom: 1px solid #fff; padding: 8px; text-align:left;">Last Sent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sms_alerts as $sms): ?>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #555;"><?= htmlspecialchars($sms['id']) ?></td>
                <td style="padding: 8px; border-bottom: 1px solid #555;"><?= htmlspecialchars($sms['location_id']) ?></td>
                <td style="padding: 8px; border-bottom: 1px solid #555;"><?= htmlspecialchars($sms['last_sent']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No SMS alerts found.</p>
    <?php endif; ?>
</div>
