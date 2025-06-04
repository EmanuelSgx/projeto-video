# Guia Completo para Testar a API de Vídeos no Postman

## 🚀 Pré-requisitos

1. **Postman instalado** (baixe em: https://www.postman.com/downloads/)
2. **Servidor Laravel rodando**:
   ```bash
   php artisan serve
   ```
   O servidor estará disponível em: `http://localhost:8000`

---

## 📋 Configuração Inicial no Postman

### 1. Criar uma Nova Collection
1. Abra o Postman
2. Clique em "New" → "Collection"
3. Nome: "API Upload de Vídeos"
4. Descrição: "Testes para o sistema de upload de vídeos com Laravel"

### 2. Configurar Variáveis da Collection
1. Na collection criada, vá em "Variables"
2. Adicione as variáveis:
   - **Variable**: `base_url` | **Value**: `http://localhost:8000`
   - **Variable**: `api_prefix` | **Value**: `/api`

---

## 🎯 Testes da API

### **1. Upload de Vídeo** ✅

**Configuração:**
- **Method**: `POST`
- **URL**: `{{base_url}}{{api_prefix}}/videos`
- **Headers**:
  ```
  Accept: application/json
  ```

**Body:**
1. Selecione: `form-data`
2. Adicione a key: `video`
3. Mude o tipo para `File`
4. Clique em "Select Files" e escolha um arquivo de vídeo (.mp4, .mov, .avi, .webm, .wmv)

**Exemplo de Resposta (Status 201):**
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

**⚠️ IMPORTANTE**: Salve o `uuid` retornado para usar nos próximos testes!

---

### **2. Listar Todos os Vídeos** 📋

**Configuração:**
- **Method**: `GET`
- **URL**: `{{base_url}}{{api_prefix}}/videos`
- **Headers**:
  ```
  Accept: application/json
  ```

**Exemplo de Resposta (Status 200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
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
            }
        ],
        "per_page": 10,
        "total": 1
    },
    "message": "Videos retrieved successfully"
}
```

---

### **3. Visualizar Vídeo Específico** 👁️

**Configuração:**
- **Method**: `GET`
- **URL**: `{{base_url}}{{api_prefix}}/videos/{{video_uuid}}`
  - Substitua `{{video_uuid}}` pelo UUID do vídeo uploadado
- **Headers**:
  ```
  Accept: application/json
  ```

**Exemplo de URL:**
```
http://localhost:8000/api/videos/550e8400-e29b-41d4-a716-446655440000
```

**Exemplo de Resposta (Status 200):**
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
        "created_at": "2025-06-04T17:00:00.000000Z",
        "updated_at": "2025-06-04T17:00:00.000000Z"
    },
    "message": "Video retrieved successfully"
}
```

---

### **4. Deletar Vídeo** 🗑️

**Configuração:**
- **Method**: `DELETE`
- **URL**: `{{base_url}}{{api_prefix}}/videos/{{video_uuid}}`
- **Headers**:
  ```
  Accept: application/json
  ```

**Exemplo de Resposta (Status 200):**
```json
{
    "success": true,
    "message": "Video deleted successfully"
}
```

---

## 🔍 Testes de Validação (Casos de Erro)

### **5. Upload sem Arquivo** ❌

**Configuração:**
- **Method**: `POST`
- **URL**: `{{base_url}}{{api_prefix}}/videos`
- **Headers**: `Accept: application/json`
- **Body**: `form-data` (vazio, sem o campo `video`)

**Resposta Esperada (Status 422):**
```json
{
    "message": "The video field is required.",
    "errors": {
        "video": [
            "Video file is required."
        ]
    }
}
```

---

### **6. Upload com Arquivo Inválido** ❌

**Configuração:**
- **Method**: `POST`
- **URL**: `{{base_url}}{{api_prefix}}/videos`
- **Body**: `form-data` com um arquivo PDF ou imagem

**Resposta Esperada (Status 422):**
```json
{
    "message": "Video must be of type: mp4, mov, avi, webm, wmv.",
    "errors": {
        "video": [
            "Video must be of type: mp4, mov, avi, webm, wmv."
        ]
    }
}
```

---

### **7. Buscar Vídeo Inexistente** ❌

**Configuração:**
- **Method**: `GET`
- **URL**: `{{base_url}}{{api_prefix}}/videos/uuid-inexistente`

**Resposta Esperada (Status 404):**
```json
{
    "success": false,
    "message": "Video not found"
}
```

---

## 🎬 Fluxo de Teste Recomendado

1. **Primeiro**: Execute "Upload de Vídeo"
2. **Copie o UUID** da resposta
3. **Segundo**: Execute "Listar Vídeos" para ver todos
4. **Terceiro**: Execute "Visualizar Vídeo" usando o UUID copiado
5. **Quarto**: Execute "Deletar Vídeo" usando o mesmo UUID
6. **Verificação**: Execute "Listar Vídeos" novamente (deve estar vazio)

---

## 🛠️ Troubleshooting

### Erro "Connection refused"
- Verifique se o servidor Laravel está rodando: `php artisan serve`

### Erro 500
- Verifique os logs: `storage/logs/laravel.log`
- Verifique se as configurações do .env estão corretas

### Upload não funciona
- Verifique se o arquivo é um vídeo válido
- Verifique se o arquivo não excede 100MB
- Verifique as permissões da pasta `storage/`

---

## 📝 Dicas Importantes

1. **Sempre use** `Accept: application/json` no header
2. **Para upload**, use `form-data` no body
3. **Salve os UUIDs** retornados para reutilizar
4. **Teste os cenários de erro** também
5. **Monitore os logs** do Laravel durante os testes