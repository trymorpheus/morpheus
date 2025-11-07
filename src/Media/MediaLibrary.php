<?php

namespace DynamicCRUD\Media;

use PDO;

class MediaLibrary
{
    private PDO $pdo;
    private string $uploadDir;
    private string $baseUrl;

    public function __construct(PDO $pdo, string $uploadDir = 'uploads', string $baseUrl = '/uploads')
    {
        $this->pdo = $pdo;
        $this->uploadDir = rtrim($uploadDir, '/');
        $this->baseUrl = rtrim($baseUrl, '/');
        
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS _media (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                filepath VARCHAR(500) NOT NULL,
                url VARCHAR(500) NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                file_size INT NOT NULL,
                width INT NULL,
                height INT NULL,
                folder VARCHAR(255) DEFAULT '/',
                uploaded_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_folder (folder),
                INDEX idx_mime (mime_type)
            )
        ");
    }

    public function upload(array $file, string $folder = '/', ?int $userId = null): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload failed'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $ext;
        
        $folder = trim($folder, '/');
        $targetDir = $this->uploadDir . ($folder ? '/' . $folder : '');
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to move file'];
        }

        $url = $this->baseUrl . ($folder ? '/' . $folder : '') . '/' . $filename;
        $dimensions = $this->getImageDimensions($targetPath, $mimeType);

        $stmt = $this->pdo->prepare("
            INSERT INTO _media (filename, original_filename, filepath, url, mime_type, file_size, width, height, folder, uploaded_by)
            VALUES (:filename, :original, :filepath, :url, :mime, :size, :width, :height, :folder, :user)
        ");

        $stmt->execute([
            'filename' => $filename,
            'original' => $file['name'],
            'filepath' => $targetPath,
            'url' => $url,
            'mime' => $mimeType,
            'size' => $file['size'],
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'folder' => '/' . $folder,
            'user' => $userId,
        ]);

        return [
            'success' => true,
            'id' => (int) $this->pdo->lastInsertId(),
            'url' => $url,
            'filename' => $filename,
        ];
    }

    public function getFiles(string $folder = '/', int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM _media 
            WHERE folder = :folder 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':folder', $folder, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM _media WHERE folder = :folder");
        $countStmt->execute(['folder' => $folder]);
        $total = (int) $countStmt->fetchColumn();

        return [
            'files' => $files,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM _media 
            WHERE original_filename LIKE :query 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT filepath FROM _media WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            return false;
        }

        if (file_exists($file['filepath'])) {
            unlink($file['filepath']);
        }

        $stmt = $this->pdo->prepare("DELETE FROM _media WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getFolders(): array
    {
        $stmt = $this->pdo->query("SELECT DISTINCT folder FROM _media ORDER BY folder");
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'folder');
    }

    public function createFolder(string $folder): bool
    {
        $folder = trim($folder, '/');
        $targetDir = $this->uploadDir . '/' . $folder;
        
        if (!is_dir($targetDir)) {
            return mkdir($targetDir, 0755, true);
        }
        
        return true;
    }

    private function getImageDimensions(string $path, string $mimeType): array
    {
        if (strpos($mimeType, 'image/') !== 0) {
            return ['width' => null, 'height' => null];
        }

        $size = @getimagesize($path);
        
        return [
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
        ];
    }

    public function getFile(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM _media WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $file ?: null;
    }

    public function getStats(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_files,
                SUM(file_size) as total_size,
                COUNT(CASE WHEN mime_type LIKE 'image/%' THEN 1 END) as images,
                COUNT(CASE WHEN mime_type LIKE 'video/%' THEN 1 END) as videos,
                COUNT(CASE WHEN mime_type LIKE 'application/pdf' THEN 1 END) as pdfs
            FROM _media
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
