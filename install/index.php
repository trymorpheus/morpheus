<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Morpheus\Installer\InstallerWizard;

session_start();

$wizard = new InstallerWizard();
$step = $wizard->getCurrentStep();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'check_system':
            echo json_encode($wizard->checkSystem());
            exit;
            
        case 'test_connection':
            echo json_encode($wizard->testDatabaseConnection($_POST));
            exit;
            
        case 'install':
            $result = $wizard->install($_POST);
            if ($result['success']) {
                $_SESSION['installed'] = true;
            }
            echo json_encode($result);
            exit;
    }
}

// Redirect if already installed
if (file_exists(__DIR__ . '/../config.php') && $step !== 'success') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD Installer</title>
    <link rel="stylesheet" href="assets/installer.css">
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>🚀 DynamicCRUD</h1>
            <p>Universal CMS Installation Wizard</p>
        </div>

        <div class="installer-progress">
            <?php
            $steps = ['welcome', 'system_check', 'database', 'site_info', 'content_type', 'theme', 'install', 'success'];
            $currentIndex = array_search($step, $steps);
            foreach ($steps as $index => $s) {
                $class = $index < $currentIndex ? 'completed' : ($index === $currentIndex ? 'active' : '');
                echo "<div class='step $class'>" . ($index + 1) . "</div>";
            }
            ?>
        </div>

        <div class="installer-content">
            <?php include __DIR__ . "/templates/$step.php"; ?>
        </div>
    </div>

    <script src="assets/installer.js"></script>
</body>
</html>
