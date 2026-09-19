<?php
declare(strict_types=1);

/*
 * VideoThumbnail — extracts a poster frame from an uploaded MP4 via ffmpeg.
 * ffmpeg is optional: when the binary is missing (typical local OpenServer),
 * upload succeeds and the poster simply stays null.
 */
class VideoThumbnail {
    public const WIDTH = 800;

    public function __construct(
        private string $publicDir,
        private ?string $ffmpegPath = null
    ) {
        $this->ffmpegPath = $this->ffmpegPath
            ?? (getenv('FFMPEG_PATH') ?: null)
            ?? (PHP_OS_FAMILY === 'Windows' ? 'C:\\ffmpeg\\bin\\ffmpeg.exe' : '/usr/bin/ffmpeg');
    }

    public function isAvailable(): bool {
        $cmd = '"' . $this->ffmpegPath . '" -version';
        $output = [];
        $code = 1;
        @exec($cmd . ' 2>&1', $output, $code);
        return $code === 0;
    }

    /**
     * Extract a poster frame at ~1s from the video.
     *
     * @param string $videoAbsPath absolute path to the MP4
     * @param string $relativeDir  e.g. "uploads/videos"
     * @return string|null relative path (uploads/...) of the generated thumbnail
     */
    public function generate(string $videoAbsPath, string $relativeDir): ?string {
        if (!$this->isAvailable() || !is_file($videoAbsPath)) {
            return null;
        }

        $dir = dirname($videoAbsPath);
        $base = pathinfo($videoAbsPath, PATHINFO_FILENAME);
        $thumbPath = $dir . '/' . $base . '_thumb.jpg';

        $cmd = sprintf(
            '"%s" -y -ss 1 -i "%s" -vframes 1 -vf "scale=%d:-2" -q:v 3 "%s" 2>&1',
            $this->ffmpegPath,
            $videoAbsPath,
            self::WIDTH,
            $thumbPath
        );

        @exec($cmd, $output, $code);
        if ($code !== 0 || !is_file($thumbPath)) {
            return null;
        }

        // Ensure the directory part of relativeDir exists in URL space
        $relative = $relativeDir . '/' . basename($thumbPath);
        return $relative;
    }
}
