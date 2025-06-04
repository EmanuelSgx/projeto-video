<?php

namespace App\Services;

use App\Contracts\VideoMetadataExtractorInterface;
use Exception;

class MockVideoMetadataExtractor implements VideoMetadataExtractorInterface
{
    public function extract(string $filePath): array
    {
        // Para desenvolvimento/teste, retorna dados simulados
        $fileSize = filesize($filePath);
        
        return [
            'duration' => rand(30, 300), // Entre 30 segundos e 5 minutos
            'resolution' => '1920x1080',
            'width' => 1920,
            'height' => 1080,
            'bitrate' => '2000000',
            'format_name' => 'mp4',
        ];
    }

    public function isValidVideo(string $filePath): bool
    {
        // Verifica se o arquivo existe e tem extensão de vídeo
        if (!file_exists($filePath)) {
            return false;
        }

        $allowedExtensions = ['mp4', 'mov', 'avi', 'webm', 'wmv'];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        return in_array($extension, $allowedExtensions);
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
