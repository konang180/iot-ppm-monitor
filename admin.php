<?php
include('db_config.php');
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) die("Connection failed: " . pg_last_error());

if (isset($_POST['add_location'])) {
    $location_name = pg_escape_string($_POST['location_name']);
    pg_query($conn, "INSERT INTO locations (name, status) VALUES ('$location_name', 'f')");
}

if (isset($_POST['update_location'])) {
    $location_id = $_POST['location_id'];
    $new_name = pg_escape_string($_POST['new_name']);
    pg_query($conn, "UPDATE locations SET name = '$new_name' WHERE id = '$location_id'");
}

if (isset($_POST['set_active_location'])) {
    $location_id = $_POST['set_active_location'];
    pg_query($conn, "UPDATE locations SET status = 'f'");
    pg_query($conn, "UPDATE locations SET status = 't' WHERE id = '$location_id'");
}

if (isset($_POST['delete_location'])) {
    $location_id = $_POST['delete_location'];
    pg_query($conn, "DELETE FROM pollution_data WHERE location_id = '$location_id'");
    pg_query($conn, "DELETE FROM locations WHERE id = '$location_id'");
}

$query = "SELECT * FROM locations";
$result = pg_query($conn, $query);
$locations = pg_fetch_all($result);
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Locations</title>
    <style>
        body {
    font-family: 'Segoe UI', sans-serif;
    background: #0a1f44;
    color: #fff;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    min-height: 100vh;
}

.container {
    width: 90%;
    max-width: 1000px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 15px;
    margin: 40px 0;
    padding: 30px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
}

h1, h2 {
    text-align: center;
}

form {
    margin-bottom: 20px;
}

.inline-form {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

input[type="text"] {
    padding: 10px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    flex: 1;
    max-width: 300px;
}

button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    min-width: 100px;
    height: 44px;
    font-size: 15px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
    text-align: center;
    white-space: nowrap;
}

/* Optional: Force consistent width for all buttons */
.actions button {
    flex: 1 1 auto;
    max-width: 120px;
}


/* Button Colors */
.btn-add {
    background: #2196F3;
    color: white;
}
.btn-add:hover {
    background: #1976D2;
}

.btn-update {
    background: #4CAF50;
    color: white;
}
.btn-update:hover {
    background: #388E3C;
}

.btn-cancel {
    background: #9E9E9E;
    color: white;
}
.btn-cancel:hover {
    background: #757575;
}

.btn-set {
    background: #00BCD4;
    color: white;
}
.btn-set:hover {
    background: #0097A7;
}

.btn-delete {
    background: #F44336;
    color: white;
}
.btn-delete:hover {
    background: #D32F2F;
}

/* Card-based Layout */
.card-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 20px;
}

.card {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.card-header h3 {
    margin: 0;
    font-size: 20px;
}

.badge {
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 14px;
    color: white;
}

.badge.active {
    background-color: #4CAF50;
}

.badge.inactive {
    background-color: #9E9E9E;
}

.card-body p {
    margin: 5px 0;
}

.actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Update Section */
.update-section {
    display: none;
    margin-top: 30px;
}

.update-section h2 {
    margin-bottom: 10px;
}

.update-form {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.no-locations {
    text-align: center;
    padding: 20px;
    background-color: rgba(255,255,255,0.05);
    border-radius: 10px;
}

/* Responsive Grid for Cards */
@media (min-width: 600px) {
    .card-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
}


    </style>
</head>
<body>

<div class="container">
    <h1>Admin Panel - Locations</h1>

    <!-- Add Location -->
    <form method="POST" class="inline-form">
        <input type="text" name="location_name" placeholder="Enter new location" required>
        <button type="submit" name="add_location" class="btn-add">Add Location</button>
    </form>

    <!-- Locations as Cards -->
    <div class="card-list">
        <?php if ($locations): foreach ($locations as $loc): ?>
        <div class="card">
            <div class="card-header">
                <h3><?= htmlspecialchars($loc['name']) ?></h3>
                <span class="badge <?= $loc['status'] == 't' ? 'active' : 'inactive' ?>">
                    <?= $loc['status'] == 't' ? 'Active' : 'Inactive' ?>
                </span>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> <?= $loc['id'] ?></p>
                <div class="actions">
                    <button class="btn-update" onclick="showUpdateForm('<?= $loc['id'] ?>', '<?= htmlspecialchars($loc['name']) ?>')">Update</button>
                    <?php if ($loc['status'] == 'f'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="set_active_location" value="<?= $loc['id'] ?>">
                            <button type="submit" class="btn-set">Set Active</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this location?');">
                        <input type="hidden" name="delete_location" value="<?= $loc['id'] ?>">
                        <button type="submit" class="btn-delete">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <p class="no-locations">No locations found.</p>
        <?php endif; ?>
    </div>

    <!-- Update Location Section -->
    <div class="update-section" id="updateSection">
        <h2>Update Location</h2>
        <form method="POST" class="update-form">
            <input type="hidden" name="location_id" id="locationId">
            <input type="text" name="new_name" id="newLocationName" placeholder="Enter new name" required>
            <button type="submit" name="update_location" class="btn-update">Update</button>
            <button type="button" class="btn-cancel" onclick="hideUpdateForm()">Cancel</button>
        </form>
    </div>
</div>

<script>
    function showUpdateForm(id, name) {
        document.getElementById('locationId').value = id;
        document.getElementById('newLocationName').value = name;
        document.getElementById('updateSection').style.display = 'block';
        document.getElementById('newLocationName').focus();
        window.scrollTo({
            top: document.getElementById('updateSection').offsetTop - 20,
            behavior: 'smooth'
        });
    }

    function hideUpdateForm() {
        document.getElementById('updateSection').style.display = 'none';
    }
</script>

</body>
</html>
