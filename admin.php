<?php
// Database connection details
$host = "dpg-cvlai4vgi27c73dm6eb0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "mydb_xyz123";
$user = "konang";
$password = "HbpK0zGuFkHURjPM9pK5c5fGZo6pIjOU";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Handle adding a new location
if (isset($_POST['add_location'])) {
    $location_name = pg_escape_string($_POST['location_name']);
    $query = "INSERT INTO locations (name, status) VALUES ('$location_name', 'f')";
    pg_query($conn, $query);
}

// Handle updating location name
if (isset($_POST['update_location'])) {
    $location_id = $_POST['location_id'];
    $new_name = pg_escape_string($_POST['new_name']);
    $query = "UPDATE locations SET name = '$new_name' WHERE id = '$location_id'";
    pg_query($conn, $query);
}

// Handle activating a location
if (isset($_POST['set_active_location'])) {
    $location_id = $_POST['set_active_location'];
    if (is_numeric($location_id) && intval($location_id) > 0) {
        pg_query($conn, "UPDATE locations SET status = 'f'");
        pg_query($conn, "UPDATE locations SET status = 't' WHERE id = '$location_id'");
    }
}

// Handle deleting a location
if (isset($_POST['delete_location'])) {
    $location_id = $_POST['delete_location'];
    if (is_numeric($location_id) && intval($location_id) > 0) {
        pg_query($conn, "DELETE FROM pollution_data WHERE location_id = '$location_id'");
        pg_query($conn, "DELETE FROM locations WHERE id = '$location_id'");
    }
}

// Fetch all locations
$query = "SELECT * FROM locations";
$result = pg_query($conn, $query);
$locations = pg_fetch_all($result);
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Locations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        button {
            padding: 7px 12px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            color: white;
        }
        .btn-update { background: #ff9800; }
        .btn-active { background: #4CAF50; }
        .btn-delete { background: red; }
        .btn:hover { opacity: 0.8; }
        .update-form {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Admin Panel - Locations</h1>
    <form action="admin.php" method="POST">
        <input type="text" name="location_name" placeholder="Enter new location name" required>
        <button type="submit" name="add_location" class="btn btn-add">Add Location</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Location Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($locations) { foreach ($locations as $location) { ?>
            <tr>
                <td><?= $location['id'] ?></td>
                <td><?= htmlspecialchars($location['name']) ?></td>
                <td><?= $location['status'] == 't' ? 'Active' : 'Inactive' ?></td>
                <td>
                    <button class="btn btn-update" onclick="showUpdateForm('<?= $location['id'] ?>', '<?= htmlspecialchars($location['name']) ?>')">Update</button>
                    <?php if ($location['status'] == 'f') { ?>
                        <button class="btn btn-active" onclick="setActiveLocation('<?= $location['id'] ?>')">Set Active</button>
                    <?php } ?>
                    <button class="btn btn-delete" onclick="deleteLocation('<?= $location['id'] ?>')">Delete</button>
                </td>
            </tr>
            <?php }} else { echo "<tr><td colspan='4'>No locations found.</td></tr>"; } ?>
        </tbody>
    </table>

    <div class="update-form" id="updateForm">
        <h3>Update Location</h3>
        <form action="admin.php" method="POST">
            <input type="hidden" name="location_id" id="locationId">
            <input type="text" name="new_name" id="newLocationName" required>
            <button type="submit" name="update_location" class="btn btn-update">Update</button>
            <button type="button" onclick="hideUpdateForm()">Cancel</button>
        </form>
    </div>
</div>

<script>
    function showUpdateForm(id, name) {
        document.getElementById('locationId').value = id;
        document.getElementById('newLocationName').value = name;
        document.getElementById('updateForm').style.display = 'block';
    }

    function hideUpdateForm() {
        document.getElementById('updateForm').style.display = 'none';
    }
</script>
</body>
</html>
