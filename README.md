# 🎬 Sistema de Upload de Vídeos

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![AWS S3](https://img.shields.io/badge/AWS-S3-FF9900?logo=amazon-aws&logoColor=white)](https://aws.amazon.com/s3/)
[![Tests](https://img.shields.io/badge/Tests-7%20passing-green)](./tests)

API RESTful robusta para upload, processamento e gerenciamento de vídeos com **arquitetura SOLID** completa e integração com AWS S3.

## 📋 Índice

- [🚀 Funcionalidades](#-funcionalidades)
- [🛠️ Tecnologias](#️-tecnologias)
- [⚡ Instalação](#-instalação)
- [⚙️ Configuração](#️-configuração)
- [🎯 API Endpoints](#-api-endpoints)
- [📊 Exemplos de Uso](#-exemplos-de-uso)
- [🧪 Testes](#-testes)
- [🏗️ Arquitetura](#️-arquitetura)
- [📁 Estrutura do Projeto](#-estrutura-do-projeto)
- [🔧 Comandos Artisan](#-comandos-artisan)
- [📚 Documentação](#-documentação)

## 🚀 Funcionalidades

### Core Features
- ✅ **Upload de vídeos** para AWS S3 com validação rigorosa
- ✅ **Extração de metadados** automática (FFmpeg/Mock)
- ✅ **API RESTful completa** (CRUD operations)
- ✅ **Sistema de filas** para processamento assíncrono
- ✅ **Validação de arquivos** (tipo, tamanho, integridade)
- ✅ **UUIDs únicos** para identificação segura

### Arquitetura & Qualidade
- ✅ **Arquitetura SOLID** implementada com interfaces
- ✅ **Injeção de dependências** configurada
- ✅ **Testes automatizados** (PHPUnit)
- ✅ **Tratamento de erros** robusto
- ✅ **Logs estruturados** para debugging
- ✅ **Rollback automático** em caso de falhas

## 🛠️ Tecnologias

| Categoria | Tecnologia | Versão | Descrição |
|-----------|------------|--------|-----------|
| **Backend** | PHP | 8.2+ | Linguagem principal |
| **Framework** | Laravel | 12.x | Framework web moderno |
| **Storage** | AWS S3 | Latest | Armazenamento de vídeos |
| **Database** | MySQL | 8.0+ | Banco de dados relacional |
| **Media** | FFmpeg | Latest | Processamento de vídeos |
| **Queue** | Laravel Queue | Built-in | Sistema de filas |
| **Testing** | PHPUnit | 11.x | Testes automatizados |

## ⚡ Instalação

### Pré-requisitos
- **PHP 8.2+** com extensões: `gd`, `curl`, `openssl`, `mbstring`
- **Composer** para gerenciamento de dependências
- **MySQL** 8.0+ ou compatível
- **FFmpeg** instalado (opcional, usa Mock se não disponível)
- **Conta AWS** com bucket S3 configurado

### Passo a Passo

```bash
# 1. Clonar o repositório
git clone https://github.com/seu-usuario/projeto-video.git
cd projeto-video

# 2. Instalar dependências PHP
composer install

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Configurar banco de dados (veja seção Configuração)
# Edite o arquivo .env com suas credenciais

# 5. Executar migrations
php artisan migrate

# 6. (Opcional) Executar seeders
php artisan db:seed

# 7. Iniciar servidor de desenvolvimento
php artisan serve
```

O servidor estará disponível em: `http://localhost:8000`

## ⚙️ Configuração

### Arquivo .env

Copie `.env.example` para `.env` e configure:

```env
# Aplicação
APP_NAME="Sistema Upload Vídeos"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=video_upload_db
DB_USERNAME=root
DB_PASSWORD=sua_senha

# AWS S3 (OBRIGATÓRIO)
AWS_ACCESS_KEY_ID=sua_access_key
AWS_SECRET_ACCESS_KEY=sua_secret_key
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=seu-bucket-videos

# FFmpeg (Opcional)
FFMPEG_PATH=ffmpeg
FFPROBE_PATH=ffprobe

# Queue System
QUEUE_CONNECTION=database
```

### Configuração AWS S3

1. **Criar bucket S3** na AWS Console
2. **Configurar CORS** no bucket:
```json
[
  {
    "AllowedHeaders": ["*"],
    "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
    "AllowedOrigins": ["*"],
    "ExposeHeaders": []
  }
]
```

3. **Criar usuário IAM** com política:
```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:GetObject",
        "s3:PutObject",
        "s3:DeleteObject",
        "s3:ListBucket"
      ],
      "Resource": [
        "arn:aws:s3:::seu-bucket-videos",
        "arn:aws:s3:::seu-bucket-videos/*"
      ]
    }
  ]
}
```

## 🎯 API Endpoints

### Base URL: `http://localhost:8000/api`

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| `POST` | `/videos` | Upload de vídeo | Não |
| `GET` | `/videos` | Listar todos os vídeos | Não |
| `GET` | `/videos/{uuid}` | Buscar vídeo específico | Não |
| `DELETE` | `/videos/{uuid}` | Deletar vídeo | Não |
| `GET` | `/videos/validate/s3` | Validar conexão S3 | Não |

### Formatos Aceitos
- **Tipos:** MP4, MOV, AVI, WEBM, WMV
- **Tamanho máximo:** 100MB
- **Content-Type:** `multipart/form-data`

### Headers Recomendados
```
Accept: application/json
Content-Type: multipart/form-data
```

## 📊 Exemplos de Uso

### 1. Upload de Vídeo

**Request:**
```bash
curl -X POST http://localhost:8000/api/videos \
  -F "video=@meu-video.mp4" \
  -H "Accept: application/json"
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "original_name": "meu-video.mp4",
    "resolution": "1920x1080",
    "duration": 120,
    "formatted_duration": "00:02:00",
    "file_size": 15728640,
    "formatted_file_size": "15.00 MB",
    "mime_type": "video/mp4",
    "status": "uploaded",
    "s3_path": "https://bucket.s3.region.amazonaws.com/videos/uuid/file.mp4",
    "created_at": "2025-06-04T17:00:00.000000Z"
  },
  "message": "Video uploaded successfully"
}
```

### 2. Listar Vídeos

**Request:**
```bash
curl -X GET http://localhost:8000/api/videos \
  -H "Accept: application/json"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "original_name": "video1.mp4",
        "resolution": "1920x1080",
        "formatted_duration": "00:02:00",
        "formatted_file_size": "15.00 MB",
        "status": "uploaded",
        "created_at": "2025-06-04T17:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "total": 1
  }
}
```

### 3. Buscar Vídeo Específico

**Request:**
```bash
curl -X GET http://localhost:8000/api/videos/550e8400-e29b-41d4-a716-446655440000 \
  -H "Accept: application/json"
```

### 4. Validar Conexão S3

**Request:**
```bash
curl -X GET http://localhost:8000/api/videos/validate/s3 \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "S3 connection is working properly",
  "data": {
    "bucket": "seu-bucket-videos",
    "region": "sa-east-1",
    "status": "connected"
  }
}
```

### Tratamento de Erros

**Arquivo muito grande (422):**
```json
{
  "message": "File size exceeds maximum allowed size of 100MB",
  "errors": {
    "video": ["File size exceeds maximum allowed size of 100MB"]
  }
}
```

**Tipo inválido (422):**
```json
{
  "message": "Video must be of type: mp4, mov, avi, webm, wmv.",
  "errors": {
    "video": ["Video must be of type: mp4, mov, avi, webm, wmv."]
  }
}
```

## 🧪 Testes

### Testes Automatizados

```bash
# Executar todos os testes
./vendor/bin/phpunit

# Testes com coverage
./vendor/bin/phpunit --coverage-html coverage/

# Testes específicos
./vendor/bin/phpunit tests/Feature/VideoUploadTest.php
```

### Testes Manuais

```bash
# Teste completo do sistema
php tests/scripts/simple-system-test.php

# Teste específico S3
php tests/scripts/test-s3-connection.php

# Validação completa
php tests/scripts/validate-s3.php
```

### Status dos Testes

```
✅ PHPUnit: 7 tests, 18 assertions (100% passing)
✅ Upload functionality: Working
✅ S3 integration: Connected
✅ Metadata extraction: Functional
✅ API endpoints: All active
✅ Database operations: Stable
```

## 🏗️ Arquitetura

### Princípios SOLID Implementados

#### 1. **Single Responsibility Principle (SRP)**
- `VideoUploadService`: Apenas upload de vídeos
- `S3FileStorageService`: Apenas operações S3
- `FFmpegVideoMetadataExtractor`: Apenas extração de metadados

#### 2. **Open/Closed Principle (OCP)**
- Sistema extensível via interfaces
- Novos storages podem ser adicionados facilmente

#### 3. **Liskov Substitution Principle (LSP)**
- Implementações são intercambiáveis
- Mock e FFmpeg são substituíveis

#### 4. **Interface Segregation Principle (ISP)**
- Interfaces específicas e focadas
- Sem dependências desnecessárias

#### 5. **Dependency Inversion Principle (DIP)**
- Dependência de abstrações, não implementações
- Injeção de dependências configurada

### Fluxo de Upload

```mermaid
graph TD
    A[Client] -->|POST /api/videos| B[VideoController]
    B --> C[VideoUploadService]
    C --> D[Validate File]
    D --> E[Extract Metadata]
    E --> F[Upload to S3]
    F --> G[Save to Database]
    G --> H[Send to Queue]
    H --> I[Return Response]
```

## 📁 Estrutura do Projeto

```
📦 projeto-video/
├── 🎯 app/
│   ├── Console/Commands/        # Comandos Artisan personalizados
│   ├── Contracts/              # Interfaces (SOLID)
│   │   ├── FileStorageInterface.php
│   │   ├── QueueServiceInterface.php
│   │   └── VideoMetadataExtractorInterface.php
│   ├── Http/
│   │   ├── Controllers/        # Controllers da API
│   │   └── Requests/          # Form Requests para validação
│   ├── Models/                # Modelos Eloquent
│   │   ├── User.php
│   │   └── Video.php
│   ├── Providers/             # Service Providers
│   │   └── AppServiceProvider.php
│   └── Services/              # Lógica de negócio
│       ├── FFmpegVideoMetadataExtractor.php
│       ├── LaravelQueueService.php
│       ├── MockVideoMetadataExtractor.php
│       ├── S3FileStorageService.php
│       └── VideoUploadService.php
├── 🗄️ database/
│   ├── factories/             # Factories para testes
│   ├── migrations/            # Migrações do banco
│   └── seeders/              # Seeders
├── 🧪 tests/
│   ├── Feature/              # Testes de feature
│   ├── Unit/                 # Testes unitários
│   └── scripts/              # Scripts de teste manual
├── 📝 config/                # Configurações do Laravel
├── 🛤️ routes/                 # Definição de rotas
├── 📚 Documentação/
│   ├── README.md
│   ├── DOCUMENTATION.md
│   └── postman-collection.json
└── ⚙️ Arquivos de configuração
    ├── .env.example
    ├── composer.json
    └── phpunit.xml
```

## 🔧 Comandos Artisan

### Comandos Disponíveis

```bash
# Listar arquivos S3 e sincronizar com banco
php artisan videos:list-s3-files

# Limpar cache da aplicação
php artisan cache:clear

# Executar migrations
php artisan migrate

# Executar seeders
php artisan db:seed

# Gerar chave da aplicação
php artisan key:generate
```

### Comandos de Desenvolvimento

```bash
# Modo de desenvolvimento com reload automático
php artisan serve --host=0.0.0.0 --port=8000

# Executar workers da fila
php artisan queue:work

# Monitorar logs em tempo real
tail -f storage/logs/laravel.log
```

## 📚 Documentação

### Arquivos de Documentação

- **`README.md`** - Este guia completo
- **`DOCUMENTATION.md`** - Documentação técnica detalhada
- **`tests/scripts/README.md`** - Guia dos scripts de teste
- **`postman-collection.json`** - Coleção Postman para testes

### Postman Collection

Importe o arquivo `postman-collection.json` no Postman para testar todos os endpoints da API com exemplos prontos.

### Logs e Debugging

```bash
# Logs da aplicação
tail -f storage/logs/laravel.log

# Logs do queue worker
php artisan queue:work --verbose

# Debug mode no .env
APP_DEBUG=true
LOG_LEVEL=debug
```

## 🚀 Deploy em Produção

### Checklist de Deploy

- [ ] Configurar variáveis de ambiente de produção
- [ ] Configurar HTTPS
- [ ] Configurar supervisor para queue workers
- [ ] Configurar backup do banco de dados
- [ ] Configurar monitoramento de logs
- [ ] Configurar cache Redis (opcional)
- [ ] Testar conectividade S3
- [ ] Configurar firewall

### Variáveis de Produção

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Cache de produção
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 📞 Suporte e Contribuição

### Reportar Bugs

1. Verifique se o bug já foi reportado
2. Crie uma issue detalhada
3. Inclua logs relevantes
4. Descreva passos para reproduzir

### Contribuir

1. Fork o projeto
2. Crie uma branch para sua feature
3. Implemente com testes
4. Submeta um Pull Request

---

## 📄 Licença

Este projeto está licenciado sob a [MIT License](https://opensource.org/licenses/MIT).

---

## 🎉 Status do Projeto

**✅ Sistema 100% funcional e pronto para produção**

- ✅ Arquitetura SOLID implementada
- ✅ Testes automatizados passando
- ✅ Integração S3 operacional
- ✅ API RESTful completa
- ✅ Documentação abrangente
- ✅ Código limpo e organizadopip
