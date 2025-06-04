<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = Str::uuid();
        $originalName = $this->faker->word . '.mp4';
        
        return [
            'uuid' => $uuid,
            'original_name' => $originalName,
            's3_path' => "https://bucket.s3.amazonaws.com/videos/{$uuid}/{$originalName}",
            's3_key' => "videos/{$uuid}/{$originalName}",
            'resolution' => $this->faker->randomElement(['1920x1080', '1280x720', '640x480']),
            'duration' => $this->faker->numberBetween(30, 3600), // 30 seconds to 1 hour
            'mime_type' => 'video/mp4',
            'file_size' => $this->faker->numberBetween(1000000, 100000000), // 1MB to 100MB
            'status' => $this->faker->randomElement(['uploaded', 'processing', 'processed']),
        ];
    }

    /**
     * Indicate that the video is uploaded.
     */
    public function uploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'uploaded',
        ]);
    }

    /**
     * Indicate that the video is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    /**
     * Indicate that the video is processed.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
        ]);
    }
}
