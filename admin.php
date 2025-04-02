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
        /* CSS Styles */
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Panel - Locations</h1>
    <form action="admin.php" method="POST">
        <input type="text" name="location_name" placeholder="Enter new location name" required>
        <button type="submit" name="add_location">Add Location</button>
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
                    <button onclick="editLocation('<?= $location['id'] ?>', '<?= htmlspecialchars($location['name']) ?>')">Update</button>
                    <?php if ($location['status'] == 'f') { ?>
                        <button onclick="setActiveLocation('<?= $location['id'] ?>')">Set Active</button>
                    <?php } ?>
                    <button onclick="deleteLocation('<?= $location['id'] ?>')" style="background-color:red;">Delete</button>
                </td>
            </tr>
            <?php }} else { echo "<tr><td colspan='4'>No locations found.</td></tr>"; } ?>
        </tbody>
    </table>
</div>

<script>
    function editLocation(id, name) {
        document.querySelector('[name="location_id"]').value = id;
        document.querySelector('[name="new_name"]').value = name;
    }

    function setActiveLocation(id) {
        if (confirm("Are you sure you want to make this location active?")) {
            submitForm('set_active_location', id);
        }
    }

    function deleteLocation(id) {
        if (confirm("Are you sure you want to delete this location? This action cannot be undone.")) {
            submitForm('delete_location', id);
        }
    }

    function submitForm(action, id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = action;
        input.value = id;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
</script>

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

</body>
</html>


