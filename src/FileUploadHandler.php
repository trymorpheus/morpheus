<?php

namespace DynamicCRUD;

class FileUploadHandler
{
    private string $uploadDir;
    private array $allowedMimes;
    private int $maxSize;

    public function __construct(?string $uploadDir = null, array $allowedMimes = [], int $maxSize = 5242880)
    {
        $this->uploadDir = rtrim($uploadDir ?? 'uploads', '/\\');
        $this->allowedMimes = $allowedMimes;
        $this->maxSize = $maxSize; // 5MB por defecto
        
        $this->ensureUploadDirectoryExists();
        $this->ensureUploadDirectoryWritable();
    }

    private function ensureUploadDirectoryExists(): void
    {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                throw new \Exception("No se pudo crear el directorio de uploads: {$this->uploadDir}");
            }
        }
    }

    private function ensureUploadDirectoryWritable(): void
    {
        if (!is_writable($this->uploadDir)) {
            throw new \Exception("El directorio de uploads no tiene permisos de escritura: {$this->uploadDir}");
        }
    }

    public function handleUpload(string $fieldName, array $metadata = []): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $this->processFile($_FILES[$fieldName], $metadata);
    }

    public function processFile(array $file, array $metadata = []): string
    {
        $this->validateUploadError($file['error']);
        $this->validateFileSize($file['size'], $metadata);
        $this->validateMimeType($file['tmp_name'], $metadata);
        
        return $this->saveFile($file, $metadata);
    }

    private function validateUploadError(int $errorCode): void
    {
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \Exception($this->getUploadErrorMessage($errorCode));
        }
    }

    private function validateFileSize(int $size, array $metadata): void
    {
        $maxSize = $metadata['max_size'] ?? $this->maxSize;
        
        if ($size > $maxSize) {
            throw new \Exception(
                "El archivo excede el tamaño máximo permitido de " . $this->formatBytes($maxSize)
            );
        }
    }

    private function validateMimeType(string $tmpName, array $metadata): void
    {
        $allowedMimes = $metadata['allowed_mimes'] ?? $this->allowedMimes;
        
        if (empty($allowedMimes)) {
            return;
        }
        
        $mimeType = $this->detectMimeType($tmpName);
        
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception(
                "Tipo de archivo no permitido. Permitidos: " . implode(', ', $allowedMimes)
            );
        }
    }

    private function detectMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return $mimeType;
    }

    private function saveFile(array $file, array $metadata): string
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateUniqueFilename($extension);
        $destination = $this->getDestinationPath($filename);
        
        if (!$this->moveFile($file['tmp_name'], $destination)) {
            throw new \Exception("Error al mover el archivo subido");
        }
        
        return $this->getPublicPath($filename);
    }

    private function getDestinationPath(string $filename): string
    {
        return $this->uploadDir . '/' . $filename;
    }

    private function getPublicPath(string $filename): string
    {
        return '../uploads/' . $filename;
    }

    public function generateUniqueFilename(string $extension): string
    {
        return uniqid() . '_' . time() . '.' . $extension;
    }

    protected function moveFile(string $source, string $destination): bool
    {
        return move_uploaded_file($source, $destination);
    }

    public function handleMultipleUploads(string $fieldName, array $metadata = []): array
    {
        if (!$this->hasMultipleFiles($fieldName)) {
            return [];
        }

        $files = $_FILES[$fieldName];
        $this->validateFileCount($files, $metadata);
        
        return $this->processMultipleFiles($files, $metadata);
    }

    private function hasMultipleFiles(string $fieldName): bool
    {
        return isset($_FILES[$fieldName]) && is_array($_FILES[$fieldName]['name']);
    }

    private function validateFileCount(array $files, array $metadata): void
    {
        $maxFiles = $metadata['max_files'] ?? 10;
        $fileCount = count($files['name']);
        
        if ($fileCount > $maxFiles) {
            throw new \Exception("Máximo {$maxFiles} archivos permitidos");
        }
    }

    private function processMultipleFiles(array $files, array $metadata): array
    {
        $uploadedFiles = [];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($this->isEmptyFile($files['error'][$i])) {
                continue;
            }
            
            $file = $this->extractFileData($files, $i);
            $uploadedFiles[] = $this->processFile($file, $metadata);
        }
        
        return $uploadedFiles;
    }

    private function isEmptyFile(int $errorCode): bool
    {
        return $errorCode === UPLOAD_ERR_NO_FILE;
    }

    private function extractFileData(array $files, int $index): array
    {
        return [
            'name' => $files['name'][$index],
            'type' => $files['type'][$index],
            'tmp_name' => $files['tmp_name'][$index],
            'error' => $files['error'][$index],
            'size' => $files['size'][$index],
        ];
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida',
            default => 'Error desconocido al subir el archivo'
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function deleteFile(string $path): bool
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}
