<h2>Database Configuration</h2>
<p>Enter your database connection details.</p>

<form method="GET" action="index.php">
    <input type="hidden" name="step" value="site_info">
    
    <div class="form-group">
        <label for="driver">Database Driver</label>
        <select id="driver" name="driver" required>
            <option value="mysql">MySQL</option>
            <option value="pgsql">PostgreSQL</option>
        </select>
    </div>

    <div class="form-group">
        <label for="host">Database Host</label>
        <input type="text" id="host" name="host" value="localhost" required>
        <small>Usually "localhost" or "127.0.0.1"</small>
    </div>

    <div class="form-group">
        <label for="database">Database Name</label>
        <input type="text" id="database" name="database" required>
        <small>The database must already exist</small>
    </div>

    <div class="form-group">
        <label for="username">Database Username</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
        <label for="password">Database Password</label>
        <input type="password" id="password" name="password">
    </div>

    <div id="connection-result" class="alert" style="display: none;"></div>

    <button type="button" id="test-connection" class="btn btn-secondary">Test Connection</button>

    <div class="button-group">
        <a href="?step=system_check" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Next →</button>
    </div>
</form>
