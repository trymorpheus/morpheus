// Installer JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Content type selection
    const contentTypeCards = document.querySelectorAll('.content-type-card');
    contentTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            contentTypeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const input = this.querySelector('input[type="radio"]');
            if (input) input.checked = true;
        });
    });

    // Theme selection
    const themeCards = document.querySelectorAll('.theme-card');
    themeCards.forEach(card => {
        card.addEventListener('click', function() {
            themeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const input = this.querySelector('input[type="radio"]');
            if (input) input.checked = true;
        });
    });

    // Test database connection
    const testConnectionBtn = document.getElementById('test-connection');
    if (testConnectionBtn) {
        testConnectionBtn.addEventListener('click', async function() {
            const btn = this;
            const originalText = btn.textContent;
            btn.textContent = 'Testing...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'test_connection');
            formData.append('host', document.getElementById('host').value);
            formData.append('database', document.getElementById('database').value);
            formData.append('username', document.getElementById('username').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('driver', document.getElementById('driver').value);

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                const alertDiv = document.getElementById('connection-result');
                if (result.success) {
                    alertDiv.className = 'alert alert-success';
                    alertDiv.textContent = '✅ ' + result.message;
                } else {
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = '❌ ' + result.message;
                }
                alertDiv.style.display = 'block';
            } catch (error) {
                const alertDiv = document.getElementById('connection-result');
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = '❌ Connection test failed';
                alertDiv.style.display = 'block';
            }

            btn.textContent = originalText;
            btn.disabled = false;
        });
    }

    // System check
    const systemCheckBtn = document.getElementById('run-system-check');
    if (systemCheckBtn) {
        systemCheckBtn.addEventListener('click', async function() {
            const formData = new FormData();
            formData.append('action', 'check_system');

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                displaySystemCheck(result);
            } catch (error) {
                console.error('System check failed:', error);
            }
        });

        // Auto-run on page load
        systemCheckBtn.click();
    }

    // Install
    const installForm = document.getElementById('install-form');
    if (installForm) {
        installForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('install-btn');
            btn.textContent = 'Installing...';
            btn.disabled = true;

            const formData = new FormData(installForm);
            formData.append('action', 'install');

            const progressBar = document.getElementById('install-progress');
            const progressFill = progressBar.querySelector('.progress-fill');
            progressBar.style.display = 'block';

            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 5;
                if (progress <= 90) {
                    progressFill.style.width = progress + '%';
                    progressFill.textContent = progress + '%';
                }
            }, 200);

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                progressFill.textContent = '100%';

                if (result.success) {
                    setTimeout(() => {
                        window.location.href = 'index.php?step=success';
                    }, 500);
                } else {
                    const alertDiv = document.getElementById('install-result');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = '❌ ' + (result.error || 'Installation failed');
                    alertDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Try Again';
                }
            } catch (error) {
                clearInterval(progressInterval);
                const alertDiv = document.getElementById('install-result');
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = '❌ Installation failed';
                alertDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Try Again';
            }
        });
    }
});

function displaySystemCheck(result) {
    const container = document.getElementById('system-check-results');
    if (!container) return;

    let html = '';

    // PHP Version
    html += `<div class="system-check-item">
        <span>PHP Version (${result.php_version.required}+)</span>
        <span class="check-status ${result.php_version.passed ? 'pass' : 'fail'}">
            ${result.php_version.passed ? '✅' : '❌'} ${result.php_version.current}
        </span>
    </div>`;

    // Extensions
    html += `<div class="system-check-item">
        <span>Required Extensions</span>
        <span class="check-status ${result.extensions.passed ? 'pass' : 'fail'}">
            ${result.extensions.passed ? '✅ All loaded' : '❌ Missing: ' + result.extensions.missing.join(', ')}
        </span>
    </div>`;

    // Writable directories
    html += `<div class="system-check-item">
        <span>Writable Directories</span>
        <span class="check-status ${result.writable_dirs.passed ? 'pass' : 'fail'}">
            ${result.writable_dirs.passed ? '✅ All writable' : '❌ Not writable: ' + result.writable_dirs.not_writable.join(', ')}
        </span>
    </div>`;

    container.innerHTML = html;

    // Enable/disable next button
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) {
        nextBtn.disabled = !result.overall;
    }
}
