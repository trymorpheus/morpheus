<?php
// Preserve all previous form data
$params = $_GET;
?>

<h2>Choose Content Type</h2>
<p>Select what type of site you want to create.</p>

<form method="GET" action="index.php">
    <input type="hidden" name="step" value="theme">
    <?php foreach ($params as $key => $value): ?>
        <?php if ($key !== 'step' && $key !== 'content_type'): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="content-type-grid">
        <div class="content-type-card">
            <input type="radio" name="content_type" value="blog" id="ct-blog" style="display: none;" checked>
            <label for="ct-blog" style="cursor: pointer;">
                <div class="icon">📝</div>
                <h3>Blog</h3>
                <p>WordPress-style blog with posts, categories, and tags</p>
                <small>5 tables</small>
            </label>
        </div>

        <div class="content-type-card">
            <input type="radio" name="content_type" value="none" id="ct-none" style="display: none;">
            <label for="ct-none" style="cursor: pointer;">
                <div class="icon">🎯</div>
                <h3>Empty Site</h3>
                <p>Start with a clean slate and build your own structure</p>
                <small>0 tables</small>
            </label>
        </div>
    </div>

    <div class="button-group">
        <a href="?step=site_info&<?= http_build_query($params) ?>" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Next →</button>
    </div>
</form>
