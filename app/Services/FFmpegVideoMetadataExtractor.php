<?php

namespace App\Services;

use App\Contracts\VideoMetadataExtractorInterface;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Exception;

class FFmpegVideoMetadataExtractor implements VideoMetadataExtractorInterface
{
    private FFMpeg $ffmpeg;
    private FFProbe $ffprobe;

    public function __construct()
    {
        // Configuração do FFmpeg - ajuste os caminhos conforme necessário
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('app.ffmpeg_path', 'ffmpeg'),
            'ffprobe.binaries' => config('app.ffprobe_path', 'ffprobe'),
        ]);
        
        $this->ffprobe = FFProbe::create([
            'ffprobe.binaries' => config('app.ffprobe_path', 'ffprobe'),
        ]);
    }

    public function extract(string $filePath): array
    {
        try {
            $format = $this->ffprobe->format($filePath);
            $videoStream = $this->ffprobe->streams($filePath)->videos()->first();
            
            $metadata = [
                'duration' => (int) $format->get('duration'),
                'resolution' => null,
                'width' => null,
                'height' => null,
                'bitrate' => $format->get('bit_rate'),
                'format_name' => $format->get('format_name'),
            ];

            if ($videoStream) {
                $width = $videoStream->get('width');
                $height = $videoStream->get('height');
                
                if ($width && $height) {
                    $metadata['resolution'] = "{$width}x{$height}";
                    $metadata['width'] = $width;
                    $metadata['height'] = $height;
                }
            }

            return $metadata;
        } catch (Exception $e) {
            throw new Exception("Failed to extract video metadata: " . $e->getMessage());
        }
    }

    public function isValidVideo(string $filePath): bool
    {
        try {
            $streams = $this->ffprobe->streams($filePath);
            return $streams->videos()->count() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSupportedFormats(): array
    {
        return [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/webm',
            'video/x-ms-wmv',
            'video/x-flv',
        ];
    }
}
