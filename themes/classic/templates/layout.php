<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Blog') ?></title>
    <?php if (isset($theme_styles) && $theme_styles): ?>
    <style><?= $theme_styles ?></style>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/">My Blog</a></h1>
            <nav>
                <a href="/">Home</a>
                <a href="/blog">Blog</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <main>
            <?= $content ?>
        </main>
        <aside class="sidebar">
            <h3>About</h3>
            <p>Welcome to my blog.</p>
            <h3>Categories</h3>
            <ul>
                <li><a href="/blog">All Posts</a></li>
            </ul>
        </aside>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> My Blog. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
