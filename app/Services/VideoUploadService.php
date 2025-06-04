<?php

namespace App\Services;

use App\Contracts\FileStorageInterface;
use App\Contracts\VideoMetadataExtractorInterface;
use App\Contracts\QueueServiceInterface;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class VideoUploadService
{
    public function __construct(
        private FileStorageInterface $fileStorage,
        private VideoMetadataExtractorInterface $metadataExtractor,
        private QueueServiceInterface $queueService
    ) {}

    public function uploadVideo(UploadedFile $file): Video
    {
        DB::beginTransaction();
        
        try {
            // Validate video file
            $this->validateVideoFile($file);            // Store file in temporary location for metadata extraction
            // Use the actual file path instead of storing temporarily
            $originalFilePath = $file->getPathname();
            
            // Extract metadata directly from uploaded file
            $metadata = $this->metadataExtractor->extract($originalFilePath);
            
            // Upload to S3
            $storageData = $this->fileStorage->store($file, 'videos');
            
            // Create video record
            $video = Video::create([
                'uuid' => $storageData['uuid'],
                'original_name' => $storageData['original_name'],
                's3_path' => $storageData['s3_path'],
                's3_key' => $storageData['s3_key'],
                'resolution' => $metadata['resolution'],
                'duration' => $metadata['duration'],
                'mime_type' => $storageData['mime_type'],
                'file_size' => $storageData['file_size'],                'status' => 'uploaded'
            ]);
            
            // Send notification to queue
            $this->queueService->sendMessage('video-processing', [
                'video_id' => $video->id,
                'video_uuid' => $video->uuid,
                's3_path' => $video->s3_path,
                's3_key' => $video->s3_key,
                'event' => 'video_uploaded',
                'timestamp' => now()->toISOString()
            ]);
            
            DB::commit();
            
            return $video;
              } catch (Exception $e) {
            DB::rollBack();
            
            // Clean up S3 file if uploaded
            if (isset($storageData['s3_key'])) {
                $this->fileStorage->delete($storageData['s3_key']);
            }
            
            throw $e;
        }
    }

    private function validateVideoFile(UploadedFile $file): void
    {
        // Check if file is present
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file size (100MB max)
        $maxSize = 100 * 1024 * 1024; // 100MB in bytes
        if ($file->getSize() > $maxSize) {
            throw new Exception('File size exceeds maximum allowed size of 100MB');
        }

        // Check MIME type
        $allowedMimeTypes = [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/webm',
            'video/x-ms-wmv'
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new Exception('Invalid video file type. Allowed types: ' . implode(', ', $allowedMimeTypes));
        }
    }

    public function getVideoById(int $id): ?Video
    {
        return Video::find($id);
    }

    public function getVideoByUuid(string $uuid): ?Video
    {
        return Video::where('uuid', $uuid)->first();
    }

    public function deleteVideo(Video $video): bool
    {
        try {
            DB::beginTransaction();
            
            // Delete from S3
            $this->fileStorage->delete($video->s3_key);
            
            // Delete from database
            $video->delete();
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
