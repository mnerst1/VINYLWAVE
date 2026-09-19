<?php
declare(strict_types=1);

/*
 * ImageOptimizer — converts uploaded images to WebP and generates thumbnails.
 * Uses GD (bundled with OpenServer PHP builds). When GD lacks WebP support,
 * the original image is kept so uploads never fail because of it.
 */
class ImageOptimizer {
    public const THUMB_WIDTH = 400;

    /** @var array<string> relative paths (uploads/...) created by this instance */
    private array $created = [];

    public function __construct(private string $publicDir) {}

    /**
     * Optimize an uploaded image in place: convert to WebP (replacing the
     * original file) and create a thumbnail.
     *
     * @param string $absolutePath absolute path to the uploaded image
     * @param string $relativeDir  e.g. "uploads/covers"
     * @return array{webp: string|null, thumb: string|null, size_before: int, size_after: int}
     */
    public function optimize(string $absolutePath, string $relativeDir): array {
        $result = [
            'webp' => null,
            'thumb' => null,
            'size_before' => (int)@filesize($absolutePath),
            'size_after' => (int)@filesize($absolutePath),
        ];

        if (!is_file($absolutePath)) {
            return $result;
        }

        $info = @getimagesize($absolutePath);
        if (!$info) {
            return $result; // not a real image, leave as-is
        }

        $image = $this->loadImage($absolutePath, $info[2]);
        if (!$image) {
            return $result;
        }

        $base = pathinfo($absolutePath, PATHINFO_FILENAME);

        // 1. Convert to WebP (replace original file) if GD supports it
        if (function_exists('imagewebp')) {
            $webpPath = pathinfo($absolutePath, PATHINFO_DIRNAME) . '/' . $base . '.webp';
            if (@imagewebp($image, $webpPath, 82) && is_file($webpPath)) {
                if ($webpPath !== $absolutePath) {
                    @unlink($absolutePath);
                }
                $result['webp'] = $relativeDir . '/' . $base . '.webp';
                $result['size_after'] = (int)@filesize($webpPath);
                $this->created[] = $result['webp'];

                // Re-open the WebP as source for the thumbnail
                $thumbImage = $this->loadImage($webpPath, IMAGETYPE_WEBP);
                if ($thumbImage) {
                    imagedestroy($image);
                    $image = $thumbImage;
                }
            }
        }

        // 2. Generate thumbnail
        $srcW = imagesx($image);
        $srcH = imagesy($image);
        if ($srcW > self::THUMB_WIDTH) {
            $dstW = self::THUMB_WIDTH;
            $dstH = max(1, (int)round($srcH * (self::THUMB_WIDTH / $srcW)));
            $thumb = imagecreatetruecolor($dstW, $dstH);

            // Preserve transparency for PNG/WebP sources
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $dstW, $dstH, $transparent);

            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

            $thumbPath = pathinfo($absolutePath, PATHINFO_DIRNAME) . '/' . $base . '_thumb.webp';
            if (function_exists('imagewebp')) {
                $thumbRel = $relativeDir . '/' . $base . '_thumb.webp';
                if (@imagewebp($thumb, $thumbPath, 80)) {
                    $result['thumb'] = $thumbRel;
                    $this->created[] = $thumbRel;
                }
            } elseif (is_file($thumbPath . '.jpg') === false) {
                imagejpeg($thumb, $thumbPath, 80);
                $result['thumb'] = $relativeDir . '/' . $base . '_thumb.webp.jpg';
                $this->created[] = $result['thumb'];
            }
            imagedestroy($thumb);
        }

        imagedestroy($image);
        return $result;
    }

    /** @return array<string> relative paths created (for cleanup on rollback) */
    public function createdFiles(): array {
        return $this->created;
    }

    private function loadImage(string $path, int $type) {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }
}
