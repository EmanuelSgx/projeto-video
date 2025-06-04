<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\S3\S3Client;
use App\Models\Video;

class ListS3Files extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:list-s3 {--bucket=} {--detailed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista arquivos de vídeo no bucket S3 e compara com banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bucket = $this->option('bucket') ?: config('filesystems.disks.s3.bucket');
        $detailed = $this->option('detailed');

        $this->info("🔍 Verificando bucket: {$bucket}");
        $this->newLine();

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            // Listar objetos do S3
            $objects = $s3Client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => 'videos/',
            ]);

            if (empty($objects['Contents'])) {
                $this->warn('❌ Nenhum arquivo encontrado no bucket S3');
                $this->newLine();
            } else {
                $this->info("📁 Arquivos encontrados no S3:");
                $this->table(
                    ['Arquivo', 'Tamanho', 'Última Modificação'],
                    collect($objects['Contents'])->map(function ($object) {
                        return [
                            $object['Key'],
                            $this->formatBytes($object['Size']),
                            $object['LastModified']->format('Y-m-d H:i:s')
                        ];
                    })->toArray()
                );
                $this->newLine();
            }

            // Comparar com banco de dados
            $this->info("💾 Comparando com registros do banco de dados:");
            $videos = Video::all();

            if ($videos->isEmpty()) {
                $this->warn('❌ Nenhum vídeo encontrado no banco de dados');
            } else {
                $tableData = [];
                foreach ($videos as $video) {
                    $existsInS3 = collect($objects['Contents'] ?? [])->firstWhere('Key', $video->s3_key);
                    
                    $status = $existsInS3 ? '✅ Sincronizado' : '❌ Ausente no S3';
                    
                    $tableData[] = [
                        $video->uuid,
                        $video->original_name,
                        $video->s3_key,
                        $status,
                        $video->created_at->format('Y-m-d H:i:s')
                    ];
                }

                $this->table(
                    ['UUID', 'Nome Original', 'Chave S3', 'Status', 'Criado em'],
                    $tableData
                );
            }

            // Mostrar estatísticas
            $s3Count = count($objects['Contents'] ?? []);
            $dbCount = $videos->count();
            
            $this->newLine();
            $this->info("📊 Estatísticas:");
            $this->line("• Arquivos no S3: {$s3Count}");
            $this->line("• Registros no DB: {$dbCount}");
            
            if ($s3Count === $dbCount) {
                $this->info("✅ Tudo sincronizado!");
            } else {
                $this->warn("⚠️  Possível inconsistência entre S3 e banco de dados");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro ao conectar com S3: " . $e->getMessage());
            
            // Mostrar apenas dados do banco
            $this->newLine();
            $this->info("💾 Registros apenas do banco de dados:");
            $videos = Video::all();
            
            if ($videos->isEmpty()) {
                $this->warn('❌ Nenhum vídeo encontrado no banco de dados');
            } else {
                $this->table(
                    ['UUID', 'Nome Original', 'Chave S3', 'Status', 'Criado em'],
                    $videos->map(function ($video) {
                        return [
                            $video->uuid,
                            $video->original_name,
                            $video->s3_key,
                            $video->status,
                            $video->created_at->format('Y-m-d H:i:s')
                        ];
                    })->toArray()
                );
            }
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
