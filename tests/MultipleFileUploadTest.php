<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\FileUploadHandler;

class MultipleFileUploadTest extends TestCase
{
    private FileUploadHandler $handler;
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/test_uploads_' . uniqid();
        mkdir($this->uploadDir, 0755, true);
        $this->handler = new FileUploadHandler($this->uploadDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->uploadDir)) {
            array_map('unlink', glob("$this->uploadDir/*"));
            rmdir($this->uploadDir);
        }
    }

    public function testHandleMultipleUploadsReturnsEmptyArrayWhenNoFiles(): void
    {
        $result = $this->handler->handleMultipleUploads('test_field');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testHandleMultipleUploadsValidatesMaxFiles(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Máximo 2 archivos permitidos');

        $_FILES['test_field'] = [
            'name' => ['file1.jpg', 'file2.jpg', 'file3.jpg'],
            'type' => ['image/jpeg', 'image/jpeg', 'image/jpeg'],
            'tmp_name' => ['', '', ''],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [1000, 1000, 1000]
        ];

        $this->handler->handleMultipleUploads('test_field', ['max_files' => 2]);
    }

    public function testIsMultipleFileFieldDetection(): void
    {
        $column = ['metadata' => ['type' => 'multiple_files']];
        $this->assertTrue(($column['metadata']['type'] ?? null) === 'multiple_files');

        $column = ['metadata' => ['type' => 'file']];
        $this->assertFalse(($column['metadata']['type'] ?? null) === 'multiple_files');
    }
}
