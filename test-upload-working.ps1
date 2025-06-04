# Teste de Upload Simples
Write-Host "=== TESTE DE UPLOAD LARAVEL ===" -ForegroundColor Green

# Iniciar servidor em background se não estiver rodando
$job = Start-Job -ScriptBlock { 
    Set-Location "c:\projetos\projeto-video"
    php artisan serve --host=127.0.0.1 --port=8000 
}

Write-Host "Aguardando servidor iniciar..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Criar arquivo de teste
$testFile = "test-video-$(Get-Date -Format 'yyyyMMdd-HHmmss').mp4"
$videoBytes = [byte[]](0x00,0x00,0x00,0x18,0x66,0x74,0x79,0x70,0x6D,0x70,0x34,0x31,0x00,0x00,0x00,0x00,0x6D,0x70,0x34,0x31,0x69,0x73,0x6F,0x6D)
[System.IO.File]::WriteAllBytes($testFile, $videoBytes)

Write-Host "Arquivo criado: $testFile ($($videoBytes.Length) bytes)" -ForegroundColor Cyan

try {
    # Upload via API
    $uri = "http://127.0.0.1:8000/api/videos"
    $form = @{ video = Get-Item $testFile }
    
    Write-Host "Enviando para: $uri" -ForegroundColor Yellow
    
    $response = Invoke-RestMethod -Uri $uri -Method POST -Form $form -ContentType "multipart/form-data"
    
    Write-Host "✅ UPLOAD BEM-SUCEDIDO!" -ForegroundColor Green
    Write-Host "Resposta da API:" -ForegroundColor Cyan
    $response | ConvertTo-Json -Depth 3
    
    if ($response.success -and $response.data.uuid) {
        Write-Host "`n🔍 Verificando no S3..." -ForegroundColor Yellow
        Start-Sleep -Seconds 2
        
        # Verificar via comando artisan
        php artisan videos:list-s3 --detailed
    }
    
} catch {
    Write-Host "❌ ERRO:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode
        Write-Host "Status Code: $statusCode" -ForegroundColor Yellow
        
        try {
            $errorStream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorStream)
            $errorContent = $reader.ReadToEnd()
            Write-Host "Erro detalhado:" -ForegroundColor Yellow
            Write-Host $errorContent -ForegroundColor Red
        } catch {
            Write-Host "Não foi possível ler o erro detalhado" -ForegroundColor Gray
        }
    }
} finally {
    # Cleanup
    if (Test-Path $testFile) {
        Remove-Item $testFile -Force
        Write-Host "Arquivo de teste removido" -ForegroundColor Gray
    }
    
    # Parar servidor
    Stop-Job $job -ErrorAction SilentlyContinue
    Remove-Job $job -ErrorAction SilentlyContinue
}

Write-Host "`n=== FIM DO TESTE ===" -ForegroundColor Green
