<?php
$driver = $_GET['driver'] ?? 'mysql';
$host = $_GET['host'] ?? 'localhost';
$database = $_GET['database'] ?? '';
$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';
?>

<h2>Site Information</h2>
<p>Configure your site details and admin account.</p>

<form method="GET" action="index.php">
    <input type="hidden" name="step" value="content_type">
    <input type="hidden" name="driver" value="<?= htmlspecialchars($driver) ?>">
    <input type="hidden" name="host" value="<?= htmlspecialchars($host) ?>">
    <input type="hidden" name="database" value="<?= htmlspecialchars($database) ?>">
    <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
    <input type="hidden" name="password" value="<?= htmlspecialchars($password) ?>">

    <h3>Site Settings</h3>
    
    <div class="form-group">
        <label for="site_title">Site Title</label>
        <input type="text" id="site_title" name="site_title" value="My Site" required>
    </div>

    <div class="form-group">
        <label for="site_url">Site URL</label>
        <input type="url" id="site_url" name="site_url" value="http://localhost" required>
        <small>Your site's full URL (e.g., https://example.com)</small>
    </div>

    <div class="form-group">
        <label for="language">Language</label>
        <select id="language" name="language">
            <option value="en">English</option>
            <option value="es">Español</option>
            <option value="fr">Français</option>
        </select>
    </div>

    <h3 style="margin-top: 30px;">Admin Account</h3>

    <div class="form-group">
        <label for="admin_name">Admin Name</label>
        <input type="text" id="admin_name" name="admin_name" value="Admin" required>
    </div>

    <div class="form-group">
        <label for="admin_email">Admin Email</label>
        <input type="email" id="admin_email" name="admin_email" required>
    </div>

    <div class="form-group">
        <label for="admin_password">Admin Password</label>
        <input type="password" id="admin_password" name="admin_password" required minlength="8">
        <small>Minimum 8 characters</small>
    </div>

    <div class="button-group">
        <a href="?step=database" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Next →</button>
    </div>
</form>
