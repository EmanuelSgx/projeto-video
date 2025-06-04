<?php

namespace App\Contracts;

interface VideoMetadataExtractorInterface
{
    /**
     * Extract metadata from video file
     */
    public function extract(string $filePath): array;

    /**
     * Check if file is a valid video
     */
    public function isValidVideo(string $filePath): bool;
}
