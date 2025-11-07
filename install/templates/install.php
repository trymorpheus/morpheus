<?php
$params = $_GET;
?>

<h2>Ready to Install</h2>
<p>Review your configuration and click "Install" to begin.</p>

<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3>Configuration Summary:</h3>
    <ul style="line-height: 2;">
        <li><strong>Database:</strong> <?= htmlspecialchars($params['driver'] ?? 'mysql') ?> @ <?= htmlspecialchars($params['host'] ?? 'localhost') ?></li>
        <li><strong>Site Title:</strong> <?= htmlspecialchars($params['site_title'] ?? 'My Site') ?></li>
        <li><strong>Admin Email:</strong> <?= htmlspecialchars($params['admin_email'] ?? '') ?></li>
        <li><strong>Language:</strong> <?= htmlspecialchars($params['language'] ?? 'en') ?></li>
        <li><strong>Content Type:</strong> <?= htmlspecialchars($params['content_type'] ?? 'none') ?></li>
        <li><strong>Theme:</strong> <?= htmlspecialchars($params['theme'] ?? 'minimal') ?></li>
    </ul>
</div>

<form id="install-form" method="POST">
    <input type="hidden" name="action" value="install">
    <?php foreach ($params as $key => $value): ?>
        <?php if ($key !== 'step'): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endif; ?>
    <?php endforeach; ?>

    <div id="install-progress" class="progress-bar" style="display: none;">
        <div class="progress-fill" style="width: 0%;">0%</div>
    </div>

    <div id="install-result" class="alert" style="display: none;"></div>

    <div class="button-group">
        <a href="?step=theme&<?= http_build_query($params) ?>" class="btn btn-secondary">← Back</a>
        <button type="submit" id="install-btn" class="btn btn-success">🚀 Install Now</button>
    </div>
</form>
