# Teste de Upload via PowerShell
$host = "127.0.0.1:8000"
$uri = "http://${host}/api/videos"

Write-Host "=== TESTE DE UPLOAD VIA API ===" -ForegroundColor Yellow
Write-Host "URI: $uri"

# Criar um arquivo de teste simples
$testVideoPath = "test-video.mp4"
$videoContent = [byte[]](0x00,0x00,0x00,0x20,0x66,0x74,0x79,0x70,0x6D,0x70,0x34,0x31)
[System.IO.File]::WriteAllBytes($testVideoPath, $videoContent)

Write-Host "Arquivo de teste criado: $testVideoPath ($($videoContent.Length) bytes)"

try {
    # Fazer upload usando Invoke-RestMethod
    $formData = @{
        video = Get-Item $testVideoPath
    }
    
    Write-Host "Enviando requisição..." -ForegroundColor Green
    
    $response = Invoke-RestMethod -Uri $uri -Method POST -Form $formData -ContentType "multipart/form-data" -ErrorAction Stop
    
    Write-Host "✅ Upload bem-sucedido!" -ForegroundColor Green
    Write-Host "Resposta:" -ForegroundColor Cyan
    $response | ConvertTo-Json -Depth 5 | Write-Host
    
} catch {
    Write-Host "❌ Erro no upload:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Corpo da resposta de erro:" -ForegroundColor Yellow
        Write-Host $responseBody -ForegroundColor Yellow
    }
} finally {
    # Limpar arquivo de teste
    if (Test-Path $testVideoPath) {
        Remove-Item $testVideoPath -Force
        Write-Host "Arquivo de teste removido" -ForegroundColor Gray
    }
}

Write-Host "`n=== FIM DO TESTE ===" -ForegroundColor Yellow
