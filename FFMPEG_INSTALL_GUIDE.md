# Guia de Instalação do FFmpeg no Windows

## Problema
Erro: `FFMpeg\Exception\ExecutableNotFoundException`

## Causa
O FFmpeg não está instalado ou não está no PATH do sistema.

## Soluções

### Opção 1: Instalação via Chocolatey (Recomendado)

1. **Instale o Chocolatey** (se não tiver):
   - Abra PowerShell como Administrador
   - Execute:
   ```powershell
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   ```

2. **Instale o FFmpeg**:
   ```powershell
   choco install ffmpeg
   ```

3. **Reinicie o terminal/IDE**

### Opção 2: Download Manual

1. **Baixe o FFmpeg**:
   - Vá para: https://ffmpeg.org/download.html
   - Clique em "Windows" → "Windows builds by BtbN"
   - Baixe a versão "ffmpeg-master-latest-win64-gpl.zip"

2. **Extraia e Configure**:
   - Extraia para: `C:\ffmpeg`
   - Adicione ao PATH: `C:\ffmpeg\bin`

3. **Configurar PATH**:
   - Pressione Win + R, digite "sysdm.cpl"
   - Aba "Avançado" → "Variáveis de Ambiente"
   - Em "Path" adicione: `C:\ffmpeg\bin`

### Opção 3: Usando Scoop

1. **Instale o Scoop**:
   ```powershell
   iwr -useb get.scoop.sh | iex
   ```

2. **Instale o FFmpeg**:
   ```powershell
   scoop install ffmpeg
   ```

## Verificação

Após instalar, teste no terminal:
```powershell
ffmpeg -version
ffprobe -version
```

Ambos devem retornar informações de versão.

## Configuração no Laravel

Se o FFmpeg estiver em local personalizado, configure no .env:
```env
FFMPEG_PATH=C:\caminho\para\ffmpeg.exe
FFPROBE_PATH=C:\caminho\para\ffprobe.exe
```
