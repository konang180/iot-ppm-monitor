<?php
// admin.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Panel</title>
<style>
    /* Basic reset */
    * {
        box-sizing: border-box;
    }
    body, html {
        margin: 0; padding: 0; height: 100%;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #0a1f44;
        color: #fff;
        overflow: hidden;
    }

    /* Sidebar styles */
    .sidebar {
        position: fixed;
        left: 0; top: 0; bottom: 0;
        width: 220px;
        background: rgba(255,255,255,0.08);
        box-shadow: 2px 0 8px rgba(0,0,0,0.5);
        transition: width 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .sidebar.collapsed {
        width: 60px;
    }
    .sidebar .toggle-btn {
        background: #2196F3;
        color: white;
        cursor: pointer;
        font-size: 24px;
        padding: 12px 0;
        text-align: center;
        user-select: none;
    }
    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
    }
    .sidebar li {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .sidebar li:hover, .sidebar li.active {
        background: #2196F3;
    }
    .sidebar li i {
        font-style: normal;
        font-size: 20px;
        width: 24px;
        display: inline-block;
        text-align: center;
        margin-right: 12px;
    }
    .sidebar.collapsed li span.label {
        display: none;
    }
    .sidebar.collapsed li i {
        margin-right: 0;
    }

    /* Main content */
    .main-content {
        margin-left: 220px;
        padding: 20px;
        height: 100vh;
        overflow-y: auto;
        transition: margin-left 0.3s ease;
    }
    .sidebar.collapsed ~ .main-content {
        margin-left: 60px;
    }

    /* Re-use your existing styles for locations */
    <?php
    // Copy your existing container and related styles here for Locations tab content
    ?>
    body, html {
        font-family: 'Segoe UI', sans-serif;
        background: #0a1f44;
        color: #fff;
    }

    .container {
        width: 90%;
        max-width: 1000px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 15px;
        margin: 0 auto 40px;
        padding: 30px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
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

    .actions button {
        flex: 1 1 auto;
        max-width: 120px;
    }

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

    .update-form input[type="text"] {
        max-width: 300px;
        flex-grow: 1;
    }

    .no-locations {
        text-align: center;
        font-style: italic;
        color: #ccc;
    }
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="toggle-btn" id="toggleBtn">&#9776;</div>
    <ul>
        <li id="tab-locations" class="active" title="Locations">
            <i>📍</i><span class="label">Locations</span>
        </li>
        <li id="tab-sms" title="SMS Alerts">
            <i>📩</i><span class="label">SMS Alerts</span>
        </li>
    </ul>
</div>

<div class="main-content" id="mainContent">
    <!-- Dynamic content loaded here -->
    <p>Loading...</p>
</div>

<script>
// Elements
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleBtn');
const mainContent = document.getElementById('mainContent');
const tabLocations = document.getElementById('tab-locations');
const tabSms = document.getElementById('tab-sms');

function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
}
toggleBtn.addEventListener('click', toggleSidebar);

// Load content by tab
function setActiveTab(tab) {
    // Remove active from all
    tabLocations.classList.remove('active');
    tabSms.classList.remove('active');

    if (tab === 'locations') {
        tabLocations.classList.add('active');
        loadContent('locations_content.php');
    } else if (tab === 'sms') {
        tabSms.classList.add('active');
        loadContent('fetch_sms.php');
    }
}

// Load content via AJAX fetch
function loadContent(url) {
    mainContent.innerHTML = '<p>Loading...</p>';
    fetch(url, {method:'GET'})
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
    })
    .then(html => {
        mainContent.innerHTML = html;
        // Rebind any needed event listeners here if needed
    })
    .catch(error => {
        mainContent.innerHTML = `<p style="color:#f00;">Error loading content: ${error.message}</p>`;
    });
}

// Tab click handlers
tabLocations.addEventListener('click', () => setActiveTab('locations'));
tabSms.addEventListener('click', () => setActiveTab('sms'));

// Initial load - locations tab
setActiveTab('locations');
</script>

</body>
</html>
