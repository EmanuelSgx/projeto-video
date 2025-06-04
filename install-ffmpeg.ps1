# Script de Instalacao do FFmpeg para Windows
# Execute este script no PowerShell como Administrador

Write-Host "=== Instalacao do FFmpeg para o Projeto de Upload de Videos ===" -ForegroundColor Green
Write-Host ""

# Verificar se ja esta instalado
$ffmpegInstalled = Get-Command ffmpeg -ErrorAction SilentlyContinue
if ($ffmpegInstalled) {
    Write-Host "FFmpeg ja esta instalado!" -ForegroundColor Green
    Write-Host "Versao: " -NoNewline
    & ffmpeg -version | Select-String "ffmpeg version" | ForEach-Object { $_.Line.Split(' ')[2] }
    exit 0
}

Write-Host "FFmpeg nao encontrado. Iniciando instalacao..." -ForegroundColor Yellow
Write-Host ""

# Verificar se Chocolatey esta instalado
$chocoInstalled = Get-Command choco -ErrorAction SilentlyContinue
if (-not $chocoInstalled) {
    Write-Host "Instalando Chocolatey..." -ForegroundColor Blue
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    
    # Recarregar PATH
    $env:PATH = [System.Environment]::GetEnvironmentVariable("PATH","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("PATH","User")
}

Write-Host "Instalando FFmpeg via Chocolatey..." -ForegroundColor Blue
choco install ffmpeg -y

# Recarregar PATH
$env:PATH = [System.Environment]::GetEnvironmentVariable("PATH","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("PATH","User")

# Verificar instalacao
$ffmpegInstalled = Get-Command ffmpeg -ErrorAction SilentlyContinue
if ($ffmpegInstalled) {
    Write-Host ""
    Write-Host "FFmpeg instalado com sucesso!" -ForegroundColor Green
    Write-Host "Versao: " -NoNewline
    & ffmpeg -version | Select-String "ffmpeg version" | ForEach-Object { $_.Line.Split(' ')[2] }
    Write-Host ""
    Write-Host "Agora voce pode executar: php artisan serve" -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "Erro na instalacao. Tente instalar manualmente." -ForegroundColor Red
    Write-Host "Consulte o arquivo FFMPEG_INSTALL_GUIDE.md para instrucoes detalhadas." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Dica: Reinicie o terminal/IDE apos a instalacao." -ForegroundColor Yellow
