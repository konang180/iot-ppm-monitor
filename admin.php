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

// Handle activating a location (set status to 't' for selected location, 'f' for others)
if (isset($_POST['set_active_location'])) {
    $location_id = $_POST['location_id'];

    // Set all locations to inactive
    pg_query($conn, "UPDATE locations SET status = 'f'");

    // Activate the selected location
    pg_query($conn, "UPDATE locations SET status = 't' WHERE id = '$location_id'");
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
        .location-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .location-table th, .location-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .location-table th {
            background-color: #f4f4f4;
        }
        .location-table td button {
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
        }
        .location-table td button:hover {
            background-color: #45a049;
        }
        .add-location-form, .update-location-form {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"] {
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Panel - Locations</h1>

    <!-- Add Location Form -->
    <form action="admin.php" method="POST" class="add-location-form">
        <div class="form-group">
            <input type="text" name="location_name" placeholder="Enter new location name" required>
        </div>
        <button type="submit" name="add_location" class="form-btn">Add Location</button>
    </form>

    <!-- Update Location Form (Triggered on edit button) -->
    <form action="admin.php" method="POST" class="update-location-form">
        <div class="form-group">
            <input type="text" name="new_name" placeholder="Enter new location name" required>
        </div>
        <input type="hidden" name="location_id" id="location_id">
        <button type="submit" name="update_location" class="form-btn">Update Location</button>
    </form>

    <!-- Locations Table -->
    <table class="location-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Location Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($locations) {
                foreach ($locations as $location) {
                    echo "<tr>";
                    echo "<td>" . $location['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($location['name']) . "</td>";
                    echo "<td>" . ($location['status'] == 't' ? 'Active' : 'Inactive') . "</td>";
                    echo "<td>";
                    // Update button
                    echo "<button type='button' onclick=\"editLocation('{$location['id']}', '{$location['name']}')\">Update</button>";

                    // Set active button
                    if ($location['status'] == 'f') {
                        echo " <button type='button' onclick=\"setActiveLocation('{$location['id']}')\">Set Active</button>";
                    }

                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No locations found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    function editLocation(id, name) {
        document.querySelector('.update-location-form').style.display = 'block';
        document.querySelector('[name="location_id"]').value = id;
        document.querySelector('[name="new_name"]').value = name;
    }

    function setActiveLocation(id) {
        if (confirm("Are you sure you want to make this location active?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'admin.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'set_active_location';
            input.value = id;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

</body>
</html>
