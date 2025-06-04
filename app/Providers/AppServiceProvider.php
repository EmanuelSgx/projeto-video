<?php

namespace App\Providers;

use App\Contracts\FileStorageInterface;
use App\Contracts\VideoMetadataExtractorInterface;
use App\Contracts\QueueServiceInterface;
use App\Services\S3FileStorageService;
use App\Services\FFmpegVideoMetadataExtractor;
use App\Services\MockVideoMetadataExtractor;
use App\Services\LaravelQueueService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations (Dependency Inversion Principle)
        $this->app->bind(FileStorageInterface::class, S3FileStorageService::class);
        $this->app->bind(QueueServiceInterface::class, LaravelQueueService::class);
        
        // Condicional para VideoMetadataExtractor baseado na disponibilidade do FFmpeg
        $this->app->bind(VideoMetadataExtractorInterface::class, function () {
            if ($this->isFFmpegAvailable()) {
                return new FFmpegVideoMetadataExtractor();
            } else {
                return new MockVideoMetadataExtractor();
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Check if FFmpeg is available in the system
     */
    private function isFFmpegAvailable(): bool
    {
        try {
            // Tenta executar ffmpeg para verificar se está disponível
            $output = shell_exec('ffmpeg -version 2>&1');
            return $output !== null && strpos($output, 'ffmpeg version') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
