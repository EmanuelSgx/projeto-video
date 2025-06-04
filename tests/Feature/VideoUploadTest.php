<?php

namespace Tests\Feature;

use App\Models\Video;
use App\Services\VideoUploadService;
use App\Contracts\FileStorageInterface;
use App\Contracts\VideoMetadataExtractorInterface;
use App\Contracts\QueueServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the external services
        $this->mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $this->mockMetadataExtractor = Mockery::mock(VideoMetadataExtractorInterface::class);
        $this->mockQueueService = Mockery::mock(QueueServiceInterface::class);
        
        $this->app->instance(FileStorageInterface::class, $this->mockFileStorage);
        $this->app->instance(VideoMetadataExtractorInterface::class, $this->mockMetadataExtractor);
        $this->app->instance(QueueServiceInterface::class, $this->mockQueueService);
    }

    public function test_can_upload_valid_video_file()
    {
        // Create a fake video file
        $file = UploadedFile::fake()->create('test_video.mp4', 1000, 'video/mp4');
        
        // Mock the services
        $this->mockMetadataExtractor
            ->shouldReceive('extract')
            ->once()
            ->andReturn([
                'duration' => 120,
                'resolution' => '1920x1080',
                'width' => 1920,
                'height' => 1080
            ]);

        $this->mockFileStorage
            ->shouldReceive('store')
            ->once()
            ->andReturn([
                'uuid' => 'test-uuid-123',
                's3_path' => 'https://bucket.s3.amazonaws.com/videos/test-uuid-123/test_video.mp4',
                's3_key' => 'videos/test-uuid-123/test_video.mp4',
                'original_name' => 'test_video.mp4',
                'mime_type' => 'video/mp4',
                'file_size' => 1024000
            ]);

        $this->mockQueueService
            ->shouldReceive('sendMessage')
            ->once()
            ->with('video-processing', Mockery::any());

        // Make the request
        $response = $this->postJson('/api/videos', [
            'video' => $file
        ]);

        // Assert response
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Video uploaded successfully'
                ]);

        // Assert database
        $this->assertDatabaseHas('videos', [
            'original_name' => 'test_video.mp4',
            'mime_type' => 'video/mp4',
            'duration' => 120,
            'resolution' => '1920x1080',
            'status' => 'uploaded'
        ]);
    }

    public function test_rejects_invalid_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->postJson('/api/videos', [
            'video' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['video']);
    }

    public function test_rejects_file_too_large()
    {
        $file = UploadedFile::fake()->create('large_video.mp4', 102401, 'video/mp4'); // 100MB + 1KB

        $response = $this->postJson('/api/videos', [
            'video' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['video']);
    }

    public function test_requires_video_file()
    {
        $response = $this->postJson('/api/videos', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['video']);
    }

    public function test_can_list_videos()
    {
        // Create some test videos
        Video::factory()->create([
            'original_name' => 'test1.mp4',
            'status' => 'uploaded'
        ]);
        
        Video::factory()->create([
            'original_name' => 'test2.mp4',
            'status' => 'processed'
        ]);

        $response = $this->getJson('/api/videos');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Videos retrieved successfully'
                ]);
    }

    public function test_can_show_video_by_uuid()
    {
        $video = Video::factory()->create([
            'uuid' => 'test-uuid-123',
            'original_name' => 'test.mp4'
        ]);

        $response = $this->getJson("/api/videos/{$video->uuid}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'uuid' => 'test-uuid-123',
                        'original_name' => 'test.mp4'
                    ]
                ]);
    }

    public function test_returns_404_for_nonexistent_video()
    {
        $response = $this->getJson('/api/videos/nonexistent-uuid');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Video not found'
                ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
