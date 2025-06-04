<?php

require_once '../../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

echo "Iniciando teste...\n";

// Carregar configurações do .env manualmente
$envFile = file_get_contents('../../.env');
$envLines = explode("\n", $envFile);
$env = [];
foreach ($envLines as $line) {
    if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

echo "=== TESTE DE CONECTIVIDADE S3 ===\n\n";

// Configurações S3
$config = [
    'version' => 'latest',
    'region' => $env['AWS_DEFAULT_REGION'],
    'credentials' => [
        'key' => $env['AWS_ACCESS_KEY_ID'],
        'secret' => $env['AWS_SECRET_ACCESS_KEY'],
    ],
];

echo "Região: {$env['AWS_DEFAULT_REGION']}\n";
echo "Bucket: {$env['AWS_BUCKET']}\n";
echo "Access Key: " . substr($env['AWS_ACCESS_KEY_ID'], 0, 8) . "...\n\n";

try {
    // Criar cliente S3
    $s3Client = new S3Client($config);
    echo "✅ Cliente S3 criado com sucesso\n";
    
    // Testar listagem de buckets
    echo "\n--- Testando listagem de buckets ---\n";
    $result = $s3Client->listBuckets();
    echo "✅ Conexão S3 bem-sucedida\n";
    
    $foundBucket = false;
    foreach ($result['Buckets'] as $bucket) {
        echo "📦 Bucket encontrado: {$bucket['Name']}\n";
        if ($bucket['Name'] === $_ENV['AWS_BUCKET']) {
            $foundBucket = true;
            echo "✅ Bucket '{$_ENV['AWS_BUCKET']}' encontrado!\n";
        }
    }
      if (!$foundBucket) {
        echo "❌ Bucket '{$env['AWS_BUCKET']}' NÃO encontrado!\n";
        echo "📋 Buckets disponíveis:\n";
        foreach ($result['Buckets'] as $bucket) {
            echo "   - {$bucket['Name']}\n";
        }
    }
    
    // Testar upload de arquivo de teste
    echo "\n--- Testando upload ---\n";
    $testContent = "Teste de upload - " . date('Y-m-d H:i:s');
    $testKey = 'test/test-' . time() . '.txt';
      $uploadResult = $s3Client->putObject([
        'Bucket' => $env['AWS_BUCKET'],
        'Key' => $testKey,
        'Body' => $testContent,
        'ContentType' => 'text/plain',
        'ACL' => 'private',
    ]);
    
    echo "✅ Upload de teste bem-sucedido!\n";
    echo "📁 Arquivo criado: {$testKey}\n";
    echo "🔗 URL: {$uploadResult['ObjectURL']}\n";
    
    // Verificar se o arquivo existe
    echo "\n--- Verificando arquivo ---\n";
    $exists = $s3Client->doesObjectExist($env['AWS_BUCKET'], $testKey);
    if ($exists) {
        echo "✅ Arquivo confirmado no S3\n";
    } else {
        echo "❌ Arquivo NÃO encontrado no S3\n";
    }
    
    // Limpar arquivo de teste
    echo "\n--- Limpando arquivo de teste ---\n";
    $s3Client->deleteObject([
        'Bucket' => $env['AWS_BUCKET'],
        'Key' => $testKey,
    ]);
    echo "🗑️ Arquivo de teste removido\n";
    
    echo "\n🎉 TODOS OS TESTES PASSARAM! S3 está funcionando corretamente.\n";
    
} catch (AwsException $e) {
    echo "❌ ERRO AWS: " . $e->getMessage() . "\n";
    echo "💡 Código de erro: " . $e->getAwsErrorCode() . "\n";
    echo "💡 Tipo de erro: " . $e->getAwsErrorType() . "\n";
} catch (Exception $e) {
    echo "❌ ERRO GERAL: " . $e->getMessage() . "\n";
    echo "💡 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
