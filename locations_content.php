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
    <div class="update-section" id="updateSection" style="display:none;">
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
