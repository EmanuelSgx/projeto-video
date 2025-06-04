<?php

namespace App\Services;

use App\Contracts\FileStorageInterface;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Exception;

class S3FileStorageService implements FileStorageInterface
{
    private S3Client $s3Client;
    private string $bucket;
    private string $region;

    public function __construct()
    {
        $this->bucket = config('filesystems.disks.s3.bucket');
        $this->region = config('filesystems.disks.s3.region');
        
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    public function store(UploadedFile $file, string $directory = ''): array
    {
        try {
            $uuid = Str::uuid();
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            
            $fileName = "{$originalName}_{$timestamp}.{$extension}";
            $key = $directory ? "{$directory}/{$uuid}/{$fileName}" : "videos/{$uuid}/{$fileName}";

            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => fopen($file->getPathname(), 'r'),
                'ContentType' => $file->getMimeType(),
                'ACL' => 'private',
            ]);

            return [
                's3_path' => $result['ObjectURL'],
                's3_key' => $key,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uuid' => $uuid,
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to upload file to S3: " . $e->getMessage());
        }
    }    public function delete(string $key): bool
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function get(string $key): string
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return $result['Body']->getContents();
        } catch (Exception $e) {
            throw new Exception("Failed to get file from S3: " . $e->getMessage());
        }
    }

    public function getUrl(string $key): string
    {
        return $this->s3Client->getObjectUrl($this->bucket, $key);
    }

    public function exists(string $key): bool
    {
        try {
            $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSignedUrl(string $key, int $expiresIn = 3600): string
    {
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $request = $this->s3Client->createPresignedRequest($command, "+{$expiresIn} seconds");
        
        return (string) $request->getUri();
    }
}
