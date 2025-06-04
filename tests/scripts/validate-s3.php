<?php
/**
 * Script simples para validar conectividade e conteúdo do S3
 * Execute: php validate-s3.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

echo "🔍 Validando conectividade e conteúdo do S3...\n\n";

// Configurações do S3
$bucket = $_ENV['AWS_BUCKET'] ?? 'projeto-videos';
$region = $_ENV['AWS_DEFAULT_REGION'] ?? 'sa-east-1';
$accessKey = $_ENV['AWS_ACCESS_KEY_ID'] ?? '';
$secretKey = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '';

echo "📋 Configurações:\n";
echo "• Bucket: {$bucket}\n";
echo "• Região: {$region}\n";
echo "• Access Key: " . substr($accessKey, 0, 5) . "***\n";
echo "• Secret Key: " . substr($secretKey, 0, 5) . "***\n\n";

try {
    // Criar cliente S3
    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => $region,
        'credentials' => [
            'key' => $accessKey,
            'secret' => $secretKey,
        ],
    ]);

    echo "✅ Cliente S3 criado com sucesso!\n\n";

    // Verificar se o bucket existe
    echo "🔍 Verificando bucket...\n";
    $bucketExists = $s3Client->doesBucketExist($bucket);
    
    if ($bucketExists) {
        echo "✅ Bucket '{$bucket}' existe e é acessível!\n\n";
    } else {
        echo "❌ Bucket '{$bucket}' não existe ou não é acessível!\n\n";
        exit(1);
    }

    // Listar objetos
    echo "📁 Listando objetos no bucket...\n";
    $objects = $s3Client->listObjectsV2([
        'Bucket' => $bucket,
        'MaxKeys' => 50,
    ]);

    if (empty($objects['Contents'])) {
        echo "📭 Bucket está vazio (nenhum objeto encontrado)\n";
    } else {
        echo "📄 Objetos encontrados:\n";
        foreach ($objects['Contents'] as $object) {
            $size = round($object['Size'] / 1024, 2);
            $date = $object['LastModified']->format('Y-m-d H:i:s');
            echo "  • {$object['Key']} ({$size} KB) - {$date}\n";
        }
    }

    echo "\n";

    // Listar apenas pasta de vídeos
    echo "🎥 Verificando pasta 'videos/'...\n";
    $videoObjects = $s3Client->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => 'videos/',
        'MaxKeys' => 50,
    ]);

    if (empty($videoObjects['Contents'])) {
        echo "📭 Nenhum vídeo encontrado na pasta 'videos/'\n";
    } else {
        echo "🎬 Vídeos encontrados:\n";
        foreach ($videoObjects['Contents'] as $object) {
            $size = round($object['Size'] / (1024 * 1024), 2); // MB
            $date = $object['LastModified']->format('Y-m-d H:i:s');
            echo "  • {$object['Key']} ({$size} MB) - {$date}\n";
        }
    }

    echo "\n✅ Validação concluída com sucesso!\n";

} catch (AwsException $e) {
    echo "❌ Erro AWS: " . $e->getMessage() . "\n";
    echo "📝 Código do erro: " . $e->getAwsErrorCode() . "\n";
    
    if ($e->getAwsErrorCode() === 'InvalidAccessKeyId') {
        echo "💡 Dica: Verifique se AWS_ACCESS_KEY_ID está correto\n";
    } elseif ($e->getAwsErrorCode() === 'SignatureDoesNotMatch') {
        echo "💡 Dica: Verifique se AWS_SECRET_ACCESS_KEY está correto\n";
    } elseif ($e->getAwsErrorCode() === 'AccessDenied') {
        echo "💡 Dica: Verifique as permissões da conta AWS\n";
    }
    
    exit(1);
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
    exit(1);
}
