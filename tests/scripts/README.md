# 📋 Scripts de Teste - Sistema de Upload de Vídeos

Esta pasta contém scripts para validação completa do sistema de upload de vídeos.

## 🎯 Scripts Disponíveis

### 🚀 **Scripts PHP (Recomendados)**

#### `simple-system-test.php` ✅ **PRINCIPAL**
**Descrição:** Teste completo da arquitetura SOLID e funcionalidades core
**Execução:**
```powershell
# Navegue até a pasta raiz do projeto primeiro
cd c:\projetos\projeto-video

# Execute o teste
C:\Users\Emanuel\.config\herd\bin\php.bat tests\scripts\simple-system-test.php
```

#### `final-system-test.php`
**Descrição:** Teste integrado com upload/download real no S3
**Execução:**
```powershell
cd c:\projetos\projeto-video
C:\Users\Emanuel\.config\herd\bin\php.bat tests\scripts\final-system-test.php
```

#### `simple-final-test.php`
**Descrição:** Validação básica de arquivos e estrutura
**Execução:**
```powershell
cd c:\projetos\projeto-video
C:\Users\Emanuel\.config\herd\bin\php.bat tests\scripts\simple-final-test.php
```

#### `test-s3-connection.php`
**Descrição:** Teste específico de conectividade com S3
**Execução:**
```powershell
cd c:\projetos\projeto-video
C:\Users\Emanuel\.config\herd\bin\php.bat tests\scripts\test-s3-connection.php
```

### 🔗 **Scripts PowerShell**

#### `test-api-upload.ps1`
**Descrição:** Teste de upload via API REST
**Execução:**
```powershell
cd c:\projetos\projeto-video
.\tests\scripts\test-api-upload.ps1
```

#### `test-upload-working.ps1`
**Descrição:** Teste funcional completo de upload
**Execução:**
```powershell
cd c:\projetos\projeto-video
.\tests\scripts\test-upload-working.ps1
```

#### `upload-test.ps1`
**Descrição:** Script de teste básico
**Execução:**
```powershell
cd c:\projetos\projeto-video
.\tests\scripts\upload-test.ps1
```

#### `validate-s3.php`
**Descrição:** Validação avançada do S3
**Execução:**
```powershell
cd c:\projetos\projeto-video
C:\Users\Emanuel\.config\herd\bin\php.bat tests\scripts\validate-s3.php
```

## 📊 Status dos Testes

### ✅ **Funcionando Perfeitamente:**
- `simple-system-test.php` - **VALIDAÇÃO COMPLETA** ⭐
- Arquitetura SOLID - **100% IMPLEMENTADA**
- Conectividade S3 - **OPERACIONAL**
- Database - **3 vídeos confirmados**
- API Routes - **5 endpoints ativos**

### 🔧 **Configuração Necessária:**

Antes de executar os testes, certifique-se de que:

1. **Variáveis de ambiente** estão configuradas (`.env`)
2. **Banco de dados** está migrado
3. **Credenciais S3** estão válidas
4. **PHP** está disponível via Herd

## 🏆 **Resultado Esperado:**

Todos os testes devem mostrar:
```
🎉 SYSTEM VALIDATION COMPLETE
============================
✅ All core services instantiated successfully
✅ SOLID architecture maintained
✅ Database connectivity confirmed
✅ API routes registered
✅ Queue system ready
✅ Metadata extraction working

🚀 System is ready for production use!
```

## 📝 **Observações:**

- Os scripts foram movidos para `tests/scripts/` para melhor organização
- Scripts Python foram removidos (não necessários)
- Caminhos foram ajustados para funcionarem da pasta `tests/scripts/`
- Execute sempre a partir da **pasta raiz do projeto** (`c:\projetos\projeto-video`)
