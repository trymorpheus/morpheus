<?php

namespace Morpheus\Media;

class ImageEditor
{
    public function resize(string $sourcePath, string $destPath, int $width, int $height, bool $crop = false): bool
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [$srcWidth, $srcHeight, $type] = $imageInfo;

        $source = $this->createImageFromType($sourcePath, $type);
        if (!$source) {
            return false;
        }

        if ($crop) {
            $ratio = max($width / $srcWidth, $height / $srcHeight);
            $newWidth = (int) ($srcWidth * $ratio);
            $newHeight = (int) ($srcHeight * $ratio);
            
            $temp = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($temp, $source, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
            
            $x = ($newWidth - $width) / 2;
            $y = ($newHeight - $height) / 2;
            
            $dest = imagecreatetruecolor($width, $height);
            imagecopy($dest, $temp, 0, 0, (int) $x, (int) $y, $width, $height);
            imagedestroy($temp);
        } else {
            $ratio = min($width / $srcWidth, $height / $srcHeight);
            $newWidth = (int) ($srcWidth * $ratio);
            $newHeight = (int) ($srcHeight * $ratio);
            
            $dest = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        }

        $result = $this->saveImageByType($dest, $destPath, $type);
        
        imagedestroy($source);
        imagedestroy($dest);

        return $result;
    }

    public function crop(string $sourcePath, string $destPath, int $x, int $y, int $width, int $height): bool
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [, , $type] = $imageInfo;

        $source = $this->createImageFromType($sourcePath, $type);
        if (!$source) {
            return false;
        }

        $dest = imagecreatetruecolor($width, $height);
        imagecopy($dest, $source, 0, 0, $x, $y, $width, $height);

        $result = $this->saveImageByType($dest, $destPath, $type);

        imagedestroy($source);
        imagedestroy($dest);

        return $result;
    }

    public function thumbnail(string $sourcePath, string $destPath, int $size = 150): bool
    {
        return $this->resize($sourcePath, $destPath, $size, $size, true);
    }

    private function createImageFromType(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => false,
        };
    }

    private function saveImageByType($image, string $path, int $type): bool
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, 90),
            IMAGETYPE_PNG => imagepng($image, $path, 9),
            IMAGETYPE_GIF => imagegif($image, $path),
            IMAGETYPE_WEBP => imagewebp($image, $path, 90),
            default => false,
        };
    }
}
