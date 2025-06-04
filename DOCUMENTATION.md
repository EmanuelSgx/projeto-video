# Sistema de Upload de Vídeos - Documentação

## Resumo do Projeto

Este projeto foi desenvolvido seguindo os **princípios SOLID** e implementa um sistema completo de upload de vídeos com as seguintes funcionalidades:

### Funcionalidades Implementadas

1. **Upload de Vídeos** - Endpoint POST /api/videos
2. **Listagem de Vídeos** - Endpoint GET /api/videos
3. **Visualização de Vídeo** - Endpoint GET /api/videos/{uuid}
4. **Exclusão de Vídeo** - Endpoint DELETE /api/videos/{uuid}
5. **Armazenamento no AWS S3**
6. **Extração de Metadados com FFmpeg**
7. **Sistema de Filas para Notificações**

## Arquitetura SOLID Implementada

### 1. Single Responsibility Principle (SRP)
Cada classe tem uma única responsabilidade:

- **VideoUploadService**: Responsável apenas pelo processo de upload
- **S3FileStorageService**: Responsável apenas pelo armazenamento no S3
- **FFmpegVideoMetadataExtractor**: Responsável apenas pela extração de metadados
- **LaravelQueueService**: Responsável apenas pelo gerenciamento de filas

### 2. Open/Closed Principle (OCP)
O sistema é aberto para extensão mas fechado para modificação:

- Novas implementações de storage podem ser adicionadas implementando `FileStorageInterface`
- Novos extratores de metadados podem ser criados implementando `VideoMetadataExtractorInterface`
- Novos sistemas de fila podem ser integrados implementando `QueueServiceInterface`

### 3. Liskov Substitution Principle (LSP)
As implementações podem ser substituídas por suas interfaces:

- `S3FileStorageService` pode ser substituído por qualquer implementação de `FileStorageInterface`
- `FFmpegVideoMetadataExtractor` pode ser substituído por qualquer implementação de `VideoMetadataExtractorInterface`

### 4. Interface Segregation Principle (ISP)
Interfaces específicas e focadas:

- `FileStorageInterface`: Apenas métodos relacionados ao armazenamento
- `VideoMetadataExtractorInterface`: Apenas métodos de extração de metadados
- `QueueServiceInterface`: Apenas métodos de gerenciamento de filas

### 5. Dependency Inversion Principle (DIP)
Classes dependem de abstrações, não de implementações concretas:

- `VideoUploadService` depende das interfaces, não das classes concretas
- Injeção de dependência configurada no `AppServiceProvider`

## Estrutura do Banco de Dados

```sql
CREATE TABLE videos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    s3_path VARCHAR(500) NOT NULL,
    s3_key VARCHAR(500) NOT NULL,
    resolution VARCHAR(20) NULL,
    duration INT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    status ENUM('uploaded', 'processing', 'processed', 'failed') DEFAULT 'uploaded',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Endpoints da API

### 1. Upload de Vídeo
```
POST /api/videos
Content-Type: multipart/form-data

Body:
- video: arquivo de vídeo (mp4, mov, avi, webm, wmv)
- Tamanho máximo: 100MB
```

### 2. Listar Vídeos
```
GET /api/videos
```

### 3. Visualizar Vídeo
```
GET /api/videos/{uuid}
```

### 4. Excluir Vídeo
```
DELETE /api/videos/{uuid}
```

## Configuração

### Variáveis de Ambiente Necessárias

```env
# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=your-bucket-name

# FFmpeg Configuration
FFMPEG_PATH=ffmpeg
FFPROBE_PATH=ffprobe

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=banco
DB_USERNAME=root
DB_PASSWORD=1234
```

## Como Executar

1. **Instalar dependências:**
```bash
composer install
```

2. **Configurar ambiente:**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Executar migrations:**
```bash
php artisan migrate
```

4. **Iniciar servidor:**
```bash
php artisan serve
```

## Exemplo de Uso com cURL

### Upload de Vídeo
```bash
curl -X POST http://localhost:8000/api/videos \
  -F "video=@/path/to/your/video.mp4" \
  -H "Accept: application/json"
```

### Listar Vídeos
```bash
curl -X GET http://localhost:8000/api/videos \
  -H "Accept: application/json"
```

## Resposta de Sucesso do Upload

```json
{
    "success": true,
    "data": {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "original_name": "video.mp4",
        "resolution": "1920x1080",
        "duration": 120,
        "formatted_duration": "00:02:00",
        "file_size": 15728640,
        "formatted_file_size": "15.00 MB",
        "mime_type": "video/mp4",
        "status": "uploaded",
        "created_at": "2025-06-04T17:00:00.000000Z"
    },
    "message": "Video uploaded successfully"
}
```

## Sistema de Filas

Após cada upload bem-sucedido, uma mensagem é enviada para a fila com o seguinte formato:

```json
{
    "video_id": 1,
    "video_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "s3_path": "https://bucket.s3.region.amazonaws.com/videos/uuid/filename.mp4",
    "s3_key": "videos/uuid/filename.mp4",
    "event": "video_uploaded",
    "timestamp": "2025-06-04T17:00:00.000000Z"
}
```

## Extensibilidade

O sistema foi projetado para ser facilmente extensível:

1. **Novos Storages**: Implemente `FileStorageInterface` para Google Cloud, Azure, etc.
2. **Novos Processadores**: Implemente `VideoMetadataExtractorInterface` para usar outras bibliotecas
3. **Novas Filas**: Implemente `QueueServiceInterface` para RabbitMQ, Redis, SQS, etc.
4. **Novos Formatos**: Adicione validações e processamento para novos tipos de arquivo

## Tratamento de Erros

O sistema inclui tratamento robusto de erros:

- Validação de arquivos no upload
- Rollback de transações em caso de falha
- Limpeza de arquivos temporários
- Remoção de arquivos do S3 em caso de erro
- Logs detalhados de erros

## Segurança

- Validação rigorosa de tipos de arquivo
- Limite de tamanho de arquivo
- URLs assinadas para acesso aos vídeos no S3
- Sanitização de nomes de arquivo
- UUIDs únicos para evitar colisões
