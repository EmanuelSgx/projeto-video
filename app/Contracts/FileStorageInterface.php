<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    /**
     * Store file and return the storage path
     */
    public function store(UploadedFile $file, string $directory = ''): array;

    /**
     * Delete file from storage
     */
    public function delete(string $path): bool;

    /**
     * Get file URL
     */
    public function getUrl(string $path): string;

    /**
     * Check if file exists
     */
    public function exists(string $path): bool;
}
