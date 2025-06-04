# Script para upload de vídeo via API
# Execute: .\upload-test.ps1

param(
    [string]$VideoPath = "C:\Users\Emanuel\Videos\video-teste.mp4",
    [string]$ApiUrl = "http://127.0.0.1:8000/api/videos"
)

Write-Host "🎬 Testando upload de vídeo..." -ForegroundColor Cyan
Write-Host "📁 Arquivo: $VideoPath" -ForegroundColor Yellow
Write-Host "🌐 API: $ApiUrl" -ForegroundColor Yellow
Write-Host ""

# Verificar se o arquivo existe
if (-not (Test-Path $VideoPath)) {
    Write-Host "❌ Arquivo não encontrado: $VideoPath" -ForegroundColor Red
    exit 1
}

# Obter informações do arquivo
$fileInfo = Get-Item $VideoPath
$fileSizeMB = [math]::Round($fileInfo.Length / 1MB, 2)
Write-Host "📊 Tamanho do arquivo: $fileSizeMB MB" -ForegroundColor Green

try {
    # Usar curl se disponível, caso contrário usar Invoke-RestMethod
    $curlAvailable = Get-Command curl -ErrorAction SilentlyContinue
    
    if ($curlAvailable) {
        Write-Host "🚀 Fazendo upload via curl..." -ForegroundColor Blue
        
        $result = & curl -X POST $ApiUrl `
            -F "video=@$VideoPath" `
            -H "Accept: application/json" `
            --silent --show-error
            
        Write-Host "✅ Upload realizado com sucesso!" -ForegroundColor Green
        Write-Host ""
        Write-Host "📋 Resposta da API:" -ForegroundColor Cyan
        $result | ConvertFrom-Json | ConvertTo-Json -Depth 10
        
    } else {
        Write-Host "❌ curl não disponível. Usando método alternativo..." -ForegroundColor Yellow
        
        # Método alternativo usando Add-Type
        Add-Type -AssemblyName System.Net.Http
        
        $httpClient = New-Object System.Net.Http.HttpClient
        $multipartContent = New-Object System.Net.Http.MultipartFormDataContent
        
        $fileStream = [System.IO.File]::OpenRead($VideoPath)
        $fileContent = New-Object System.Net.Http.StreamContent($fileStream)
        $fileContent.Headers.ContentType = [System.Net.Http.Headers.MediaTypeHeaderValue]::Parse("video/mp4")
        
        $multipartContent.Add($fileContent, "video", $fileInfo.Name)
        
        $response = $httpClient.PostAsync($ApiUrl, $multipartContent).Result
        $responseContent = $response.Content.ReadAsStringAsync().Result
        
        $fileStream.Close()
        $httpClient.Dispose()
        
        if ($response.IsSuccessStatusCode) {
            Write-Host "✅ Upload realizado com sucesso!" -ForegroundColor Green
            Write-Host ""
            Write-Host "📋 Resposta da API:" -ForegroundColor Cyan
            $responseContent | ConvertFrom-Json | ConvertTo-Json -Depth 10
        } else {
            Write-Host "❌ Erro no upload:" -ForegroundColor Red
            Write-Host "Status: $($response.StatusCode)" -ForegroundColor Red
            Write-Host "Resposta: $responseContent" -ForegroundColor Red    }
    
} catch {
    Write-Host "❌ Erro durante o upload:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host ""
Write-Host "🔍 Para validar o upload, execute:" -ForegroundColor Yellow
Write-Host "  Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/videos' -Method Get" -ForegroundColor Gray
Write-Host "  php artisan videos:list-s3" -ForegroundColor Gray
