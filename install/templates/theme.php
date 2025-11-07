<?php
$params = $_GET;
?>

<h2>Select Theme</h2>
<p>Choose a theme for your site. You can change it later.</p>

<form method="GET" action="index.php">
    <input type="hidden" name="step" value="install">
    <?php foreach ($params as $key => $value): ?>
        <?php if ($key !== 'step' && $key !== 'theme'): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="theme-grid">
        <div class="theme-card">
            <input type="radio" name="theme" value="minimal" id="theme-minimal" style="display: none;" checked>
            <label for="theme-minimal" style="cursor: pointer;">
                <div class="icon">🎨</div>
                <h3>Minimal</h3>
                <p>Clean and simple design</p>
            </label>
        </div>

        <div class="theme-card">
            <input type="radio" name="theme" value="modern" id="theme-modern" style="display: none;">
            <label for="theme-modern" style="cursor: pointer;">
                <div class="icon">✨</div>
                <h3>Modern</h3>
                <p>Contemporary and professional</p>
            </label>
        </div>

        <div class="theme-card">
            <input type="radio" name="theme" value="classic" id="theme-classic" style="display: none;">
            <label for="theme-classic" style="cursor: pointer;">
                <div class="icon">📚</div>
                <h3>Classic</h3>
                <p>Traditional and elegant</p>
            </label>
        </div>
    </div>

    <div class="button-group">
        <a href="?step=content_type&<?= http_build_query($params) ?>" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Next →</button>
    </div>
</form>
