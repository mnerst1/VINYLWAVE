<?php
declare(strict_types=1);

class UploadService {
    private static array $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private static array $audioExts = ['mp3', 'wav', 'ogg', 'm4a'];
    private static array $imageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private static array $audioMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a'];
    private static array $uploadedFiles = [];
    private static ?string $lastThumb = null;

    private static function validateMime(string $tmpPath, array $allowedMimes): void {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);
        if (!in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException("Недопустимый тип файла: {$mime}. Разрешены: " . implode(', ', $allowedMimes));
        }
    }

    private static function registerUploadedFile(string $path): void {
        self::$uploadedFiles[] = $path;
    }

    public static function getUploadedFiles(): array {
        return self::$uploadedFiles;
    }

    public static function clearUploadedFiles(): void {
        self::$uploadedFiles = [];
    }

    public static function cleanupUploadedFiles(): void {
        foreach (self::$uploadedFiles as $file) {
            $fullPath = dirname(__DIR__) . '/public/' . $file;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        self::$uploadedFiles = [];
    }

    public static function handleImageUpload(array $file, string $subfolder = 'covers', ?string $urlFallback = null): ?string {
        if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::$imageExts, true)) {
                throw new InvalidArgumentException("Недопустимый формат изображения. Разрешены: " . implode(', ', self::$imageExts));
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new InvalidArgumentException("Файл изображения слишком большой (максимум 10 МБ)");
            }

            // Validate MIME type
            self::validateMime($file['tmp_name'], self::$imageMimes);

            $uploadDir = dirname(__DIR__) . '/public/uploads/' . trim($subfolder, '/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = bin2hex(random_bytes(10)) . '_' . time() . '.' . $ext;
            $destination = $uploadDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $relativePath = 'uploads/' . trim($subfolder, '/') . '/' . $filename;
                self::registerUploadedFile($relativePath);

                // Optimize: WebP conversion + thumbnail generation
                $optimizer = new ImageOptimizer(dirname(__DIR__) . '/public');
                $optimized = $optimizer->optimize($destination, 'uploads/' . trim($subfolder, '/'));
                foreach ($optimizer->createdFiles() as $extra) {
                    self::registerUploadedFile($extra);
                }
                self::$lastThumb = $optimized['thumb'];

                return $optimized['webp'] ?? $relativePath;
            }
        }

        if ($urlFallback !== null && trim($urlFallback) !== '') {
            return trim($urlFallback);
        }

        return null;
    }

    /** Relative path of the most recently generated thumbnail (or null). */
    public static function lastThumbnail(): ?string {
        return self::$lastThumb;
    }

    public static function handleAudioUpload(array $file, ?string $urlFallback = null): ?string {
        if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::$audioExts, true)) {
                throw new InvalidArgumentException("Недопустимый аудио формат. Разрешены: " . implode(', ', self::$audioExts));
            }
            if ($file['size'] > 25 * 1024 * 1024) {
                throw new InvalidArgumentException("Аудиофайл слишком большой (максимум 25 МБ)");
            }

            // Validate MIME type
            self::validateMime($file['tmp_name'], self::$audioMimes);

            $uploadDir = dirname(__DIR__) . '/public/uploads/audio';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = bin2hex(random_bytes(10)) . '_' . time() . '.' . $ext;
            $destination = $uploadDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $relativePath = 'uploads/audio/' . $filename;
                self::registerUploadedFile($relativePath);
                return $relativePath;
            }
        }

        if ($urlFallback !== null && trim($urlFallback) !== '') {
            return trim($urlFallback);
        }

        return null;
    }

    /**
     * Handle an uploaded MP4 video: store it and generate a poster frame
     * via ffmpeg (if available).
     *
     * @return array{url: string, thumb: string|null}
     */
    public static function handleVideoUpload(array $file): array {
        if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['url' => '', 'thumb' => null];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'mp4') {
            throw new InvalidArgumentException("Для видео поддерживается только формат MP4.");
        }
        if ($file['size'] > 100 * 1024 * 1024) {
            throw new InvalidArgumentException("Видеофайл слишком большой (максимум 100 МБ)");
        }

        // Accept both video/mp4 and common browser variants
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!str_starts_with((string)$mime, 'video/')) {
            throw new InvalidArgumentException("Недопустимый тип файла: {$mime}. Ожидался видеофайл MP4.");
        }

        $uploadDir = dirname(__DIR__) . '/public/uploads/videos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = bin2hex(random_bytes(10)) . '_' . time() . '.mp4';
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['url' => '', 'thumb' => null];
        }

        $relativePath = 'uploads/videos/' . $filename;
        self::registerUploadedFile($relativePath);

        // Generate poster frame with ffmpeg (silently skipped when unavailable)
        $generator = new VideoThumbnail(dirname(__DIR__) . '/public');
        $thumb = $generator->generate($destination, 'uploads/videos');
        if ($thumb !== null) {
            self::registerUploadedFile($thumb);
        }

        return ['url' => $relativePath, 'thumb' => $thumb];
    }
}
