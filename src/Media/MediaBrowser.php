<?php

namespace Morpheus\Media;

class MediaBrowser
{
    private MediaLibrary $library;

    public function __construct(MediaLibrary $library)
    {
        $this->library = $library;
    }

    public function render(string $mode = 'grid', string $folder = '/'): string
    {
        $result = $this->library->getFiles($folder);
        $folders = $this->library->getFolders();
        $stats = $this->library->getStats();

        $html = $this->renderStyles();
        $html .= '<div class="media-browser">';
        $html .= $this->renderHeader($stats);
        $html .= '<div class="media-content">';
        $html .= $this->renderSidebar($folders, $folder);
        $html .= '<div class="media-main">';
        $html .= $this->renderToolbar($folder);
        $html .= $this->renderUploadArea();
        
        if ($mode === 'grid') {
            $html .= $this->renderGrid($result['files']);
        } else {
            $html .= $this->renderList($result['files']);
        }
        
        $html .= $this->renderPagination($result);
        $html .= '</div></div></div>';
        $html .= $this->renderScripts();

        return $html;
    }

    private function renderStyles(): string
    {
        return <<<'CSS'
<style>
.media-browser { font-family: -apple-system, sans-serif; background: #f5f5f5; }
.media-header { background: white; padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
.media-stats { display: flex; gap: 30px; }
.stat { text-align: center; }
.stat-value { font-size: 24px; font-weight: bold; color: #667eea; }
.stat-label { font-size: 12px; color: #666; }
.media-content { display: flex; min-height: 600px; }
.media-sidebar { width: 200px; background: white; border-right: 1px solid #ddd; padding: 20px; }
.folder-item { padding: 8px; cursor: pointer; border-radius: 4px; margin-bottom: 5px; }
.folder-item:hover { background: #f0f0f0; }
.folder-item.active { background: #667eea; color: white; }
.media-main { flex: 1; padding: 20px; }
.media-toolbar { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; }
.upload-area { background: white; border: 2px dashed #ddd; border-radius: 8px; padding: 40px; text-align: center; margin-bottom: 20px; cursor: pointer; }
.upload-area:hover { border-color: #667eea; background: #f8f9ff; }
.media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
.media-item { background: white; border-radius: 8px; overflow: hidden; cursor: pointer; transition: transform 0.2s; }
.media-item:hover { transform: translateY(-4px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.media-thumb { width: 100%; height: 150px; object-fit: cover; background: #f0f0f0; }
.media-info { padding: 10px; }
.media-name { font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.media-size { font-size: 11px; color: #999; }
.btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
.btn-primary { background: #667eea; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.pagination { display: flex; gap: 5px; justify-content: center; margin-top: 20px; }
.page-btn { padding: 8px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; }
.page-btn.active { background: #667eea; color: white; border-color: #667eea; }
</style>
CSS;
    }

    private function renderHeader(array $stats): string
    {
        $totalSize = $this->formatBytes($stats['total_size']);
        
        return <<<HTML
<div class="media-header">
    <h2>📁 Media Library</h2>
    <div class="media-stats">
        <div class="stat">
            <div class="stat-value">{$stats['total_files']}</div>
            <div class="stat-label">Files</div>
        </div>
        <div class="stat">
            <div class="stat-value">{$stats['images']}</div>
            <div class="stat-label">Images</div>
        </div>
        <div class="stat">
            <div class="stat-value">$totalSize</div>
            <div class="stat-label">Storage</div>
        </div>
    </div>
</div>
HTML;
    }

    private function renderSidebar(array $folders, string $currentFolder): string
    {
        $html = '<div class="media-sidebar"><h3>Folders</h3>';
        
        foreach ($folders as $folder) {
            $active = $folder === $currentFolder ? 'active' : '';
            $name = $folder === '/' ? 'Root' : trim($folder, '/');
            $html .= sprintf(
                '<div class="folder-item %s" onclick="location.href=\'?folder=%s\'">📁 %s</div>',
                $active,
                urlencode($folder),
                htmlspecialchars($name)
            );
        }
        
        $html .= '</div>';
        return $html;
    }

    private function renderToolbar(string $folder): string
    {
        return <<<HTML
<div class="media-toolbar">
    <button class="btn btn-primary" onclick="document.getElementById('file-input').click()">
        📤 Upload Files
    </button>
    <button class="btn btn-secondary" onclick="createFolder()">
        📁 New Folder
    </button>
    <input type="search" placeholder="Search files..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
</div>
HTML;
    }

    private function renderUploadArea(): string
    {
        return <<<'HTML'
<div class="upload-area" onclick="document.getElementById('file-input').click()">
    <div style="font-size: 48px; margin-bottom: 10px;">📤</div>
    <div style="font-size: 18px; margin-bottom: 5px;">Drop files here or click to upload</div>
    <div style="font-size: 14px; color: #999;">Supports: Images, PDFs, Videos</div>
</div>
<form id="upload-form" style="display: none;">
    <input type="file" id="file-input" name="files[]" multiple>
</form>
HTML;
    }

    private function renderGrid(array $files): string
    {
        $html = '<div class="media-grid">';
        
        foreach ($files as $file) {
            $thumb = $this->getThumbnail($file);
            $size = $this->formatBytes($file['file_size']);
            
            $html .= sprintf(
                '<div class="media-item" onclick="selectFile(%d)">
                    <img src="%s" class="media-thumb" alt="%s">
                    <div class="media-info">
                        <div class="media-name" title="%s">%s</div>
                        <div class="media-size">%s</div>
                    </div>
                </div>',
                $file['id'],
                htmlspecialchars($thumb),
                htmlspecialchars($file['original_filename']),
                htmlspecialchars($file['original_filename']),
                htmlspecialchars($file['original_filename']),
                $size
            );
        }
        
        $html .= '</div>';
        return $html;
    }

    private function renderList(array $files): string
    {
        return '<div>List view coming soon...</div>';
    }

    private function renderPagination(array $result): string
    {
        if ($result['total_pages'] <= 1) {
            return '';
        }

        $html = '<div class="pagination">';
        
        for ($i = 1; $i <= $result['total_pages']; $i++) {
            $active = $i === $result['page'] ? 'active' : '';
            $html .= sprintf(
                '<button class="page-btn %s" onclick="location.href=\'?page=%d\'">%d</button>',
                $active,
                $i,
                $i
            );
        }
        
        $html .= '</div>';
        return $html;
    }

    private function renderScripts(): string
    {
        return <<<'JS'
<script>
const fileInput = document.getElementById('file-input');
const uploadForm = document.getElementById('upload-form');

fileInput.addEventListener('change', async function() {
    const formData = new FormData();
    for (let file of this.files) {
        formData.append('files[]', file);
    }
    
    try {
        const response = await fetch('?action=upload', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (error) {
        alert('Upload failed');
    }
});

function selectFile(id) {
    console.log('Selected file:', id);
    // Implement file selection logic
}

function createFolder() {
    const name = prompt('Folder name:');
    if (name) {
        location.href = '?action=create_folder&name=' + encodeURIComponent(name);
    }
}
</script>
JS;
    }

    private function getThumbnail(array $file): string
    {
        if (strpos($file['mime_type'], 'image/') === 0) {
            return $file['url'];
        }
        
        return match (true) {
            strpos($file['mime_type'], 'video/') === 0 => 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="40">🎥</text></svg>',
            strpos($file['mime_type'], 'application/pdf') === 0 => 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="40">📄</text></svg>',
            default => 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="40">📎</text></svg>',
        };
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
