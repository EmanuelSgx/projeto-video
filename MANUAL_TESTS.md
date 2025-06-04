# Testes Manuais da API

## Executar o Servidor
```bash
php artisan serve
```

## Teste 1: Upload de Vídeo
```bash
# Usando cURL (substitua o caminho do arquivo)
curl -X POST http://localhost:8000/api/videos \
  -F "video=@C:/path/to/your/video.mp4" \
  -H "Accept: application/json"
```

## Teste 2: Listar Vídeos
```bash
curl -X GET http://localhost:8000/api/videos \
  -H "Accept: application/json"
```

## Teste 3: Visualizar Vídeo Específico
```bash
# Substitua {uuid} pelo UUID retornado no upload
curl -X GET http://localhost:8000/api/videos/{uuid} \
  -H "Accept: application/json"
```

## Teste 4: Deletar Vídeo
```bash
# Substitua {uuid} pelo UUID do vídeo
curl -X DELETE http://localhost:8000/api/videos/{uuid} \
  -H "Accept: application/json"
```

## Teste 5: Upload com Arquivo Inválido
```bash
# Teste com arquivo PDF (deve falhar)
curl -X POST http://localhost:8000/api/videos \
  -F "video=@C:/path/to/document.pdf" \
  -H "Accept: application/json"
```

## Teste 6: Upload sem Arquivo
```bash
# Teste sem enviar arquivo (deve falhar)
curl -X POST http://localhost:8000/api/videos \
  -H "Accept: application/json"
```

## Resposta Esperada do Upload Bem-sucedido
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

## Para Testar com Postman

1. **Upload de Vídeo**
   - Method: POST
   - URL: http://localhost:8000/api/videos
   - Body: form-data
   - Key: video (tipo: File)
   - Value: Selecione um arquivo de vídeo

2. **Listar Vídeos**
   - Method: GET
   - URL: http://localhost:8000/api/videos

3. **Ver Vídeo Específico**
   - Method: GET
   - URL: http://localhost:8000/api/videos/{uuid}

4. **Deletar Vídeo**
   - Method: DELETE
   - URL: http://localhost:8000/api/videos/{uuid}
