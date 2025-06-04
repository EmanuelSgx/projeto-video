<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoUploadRequest;
use App\Services\VideoUploadService;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class VideoController extends Controller
{
    public function __construct(
        private VideoUploadService $videoUploadService
    ) {}

    /**
     * Display a listing of videos.
     */
    public function index(): JsonResponse
    {
        try {
            $videos = Video::orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $videos,
                'message' => 'Videos retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve videos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly uploaded video.
     */
    public function store(VideoUploadRequest $request): JsonResponse
    {
        try {
            $video = $this->videoUploadService->uploadVideo($request->file('video'));

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $video->id,
                    'uuid' => $video->uuid,
                    'original_name' => $video->original_name,
                    'resolution' => $video->resolution,
                    'duration' => $video->duration,
                    'formatted_duration' => $video->formatted_duration,
                    'file_size' => $video->file_size,
                    'formatted_file_size' => $video->formatted_file_size,
                    'mime_type' => $video->mime_type,
                    'status' => $video->status,
                    'created_at' => $video->created_at,
                ],
                'message' => 'Video uploaded successfully'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified video.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $video = $this->videoUploadService->getVideoByUuid($uuid);

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $video->id,
                    'uuid' => $video->uuid,
                    'original_name' => $video->original_name,
                    'resolution' => $video->resolution,
                    'duration' => $video->duration,
                    'formatted_duration' => $video->formatted_duration,
                    'file_size' => $video->file_size,
                    'formatted_file_size' => $video->formatted_file_size,
                    'mime_type' => $video->mime_type,
                    'status' => $video->status,
                    'created_at' => $video->created_at,
                    'updated_at' => $video->updated_at,
                ],
                'message' => 'Video retrieved successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified video.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $video = $this->videoUploadService->getVideoByUuid($uuid);

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found'
                ], 404);
            }

            $this->videoUploadService->deleteVideo($video);

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate S3 storage synchronization.
     */
    public function validateS3(): JsonResponse
    {
        try {
            $videos = Video::all();
            $validationResults = [];

            foreach ($videos as $video) {
                $s3Client = new \Aws\S3\S3Client([
                    'version' => 'latest',
                    'region' => config('filesystems.disks.s3.region'),
                    'credentials' => [
                        'key' => config('filesystems.disks.s3.key'),
                        'secret' => config('filesystems.disks.s3.secret'),
                    ],
                ]);

                try {
                    $s3Client->headObject([
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key' => $video->s3_key,
                    ]);
                    $existsInS3 = true;
                } catch (\Exception $e) {
                    $existsInS3 = false;
                }

                $validationResults[] = [
                    'uuid' => $video->uuid,
                    'original_name' => $video->original_name,
                    's3_key' => $video->s3_key,
                    's3_path' => $video->s3_path,
                    'exists_in_s3' => $existsInS3,
                    'status' => $video->status,
                    'created_at' => $video->created_at,
                ];
            }

            $totalVideos = count($validationResults);
            $existingInS3 = count(array_filter($validationResults, fn($v) => $v['exists_in_s3']));
            $missingInS3 = $totalVideos - $existingInS3;

            return response()->json([
                'success' => true,
                'data' => [
                    'videos' => $validationResults,
                    'summary' => [
                        'total_videos' => $totalVideos,
                        'existing_in_s3' => $existingInS3,
                        'missing_in_s3' => $missingInS3,
                        'sync_percentage' => $totalVideos > 0 ? round(($existingInS3 / $totalVideos) * 100, 2) : 0
                    ]
                ],
                'message' => 'S3 validation completed'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate S3 storage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
