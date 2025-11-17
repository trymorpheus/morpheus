<?php

namespace Morpheus\Tests;

use Morpheus\FileUploadHandler;
use PHPUnit\Framework\TestCase;

class FileUploadHandlerTest extends TestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = __DIR__ . '/temp_uploads';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->uploadDir)) {
            $files = glob($this->uploadDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->uploadDir);
        }
    }

    public function testConstructorCreatesUploadDirectory(): void
    {
        $newDir = __DIR__ . '/new_upload_dir';

        if (is_dir($newDir)) {
            rmdir($newDir);
        }

        new FileUploadHandler($newDir);

        $this->assertDirectoryExists($newDir);

        rmdir($newDir);
    }

    public function testHandleUploadWithNoFile(): void
    {
        $handler = new FileUploadHandler($this->uploadDir);
        $result = $handler->handleUpload('nonexistent_field', []);
        $this->assertNull($result);
    }

    public function testProcessFileWithUploadError(): void
    {
        $file = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_INI_SIZE,
            'size' => 0
        ];

        $this->expectException(\Exception::class);
        $handler = new FileUploadHandler($this->uploadDir);
        $handler->processFile($file);
    }

    public function testValidateFileSizeExceedsLimit(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, str_repeat('x', 2048));

        $file = [
            'name' => 'large.txt',
            'type' => 'text/plain',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 2048
        ];

        $metadata = ['max_size' => 1024];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/tamaño/i');

        try {
            $handler = new FileUploadHandler($this->uploadDir);
            $handler->processFile($file, $metadata);
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    public function testGenerateUniqueFilename(): void
    {
        $handler = new FileUploadHandler($this->uploadDir);
        $filename = $handler->generateUniqueFilename('txt');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{13}_[0-9]{10}\.txt$/', $filename);
    }

    public function testProcessFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'test content');

        $file = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 12
        ];

        // Create a mock for FileUploadHandler and mock only the moveFile method
        $handler = $this->getMockBuilder(FileUploadHandler::class)
            ->setConstructorArgs([$this->uploadDir])
            ->onlyMethods(['moveFile'])
            ->getMock();

        $handler->expects($this->once())
            ->method('moveFile')
            ->willReturn(true);

        $result = $handler->processFile($file);

        $this->assertNotNull($result);
        $this->assertStringStartsWith('../uploads/', $result);
        $this->assertStringEndsWith('.txt', $result);

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }

    public function testUploadDirectoryNotWritable(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Test not applicable on Windows');
        }

        $readOnlyDir = __DIR__ . '/readonly_dir';
        mkdir($readOnlyDir, 0755, true);
        chmod($readOnlyDir, 0444);

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessageMatches('/permisos/i');
            new FileUploadHandler($readOnlyDir);
        } finally {
            chmod($readOnlyDir, 0777);
            rmdir($readOnlyDir);
        }
    }
}
