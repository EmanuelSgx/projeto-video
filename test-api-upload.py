import http.client
import json
import os

# Configurações
host = '127.0.0.1:8000'
boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW'

# Dados do arquivo de teste (um pequeno arquivo de vídeo fake)
video_content = b'\x00\x00\x00\x20ftypmp41\x00\x00\x00\x00mp41isom\x00\x00\x00\x28moov'

# Criar corpo da requisição multipart
body_parts = []
body_parts.append(f'--{boundary}\r\n')
body_parts.append('Content-Disposition: form-data; name="video"; filename="test-video.mp4"\r\n')
body_parts.append('Content-Type: video/mp4\r\n\r\n')

# Converter para bytes
body_bytes = ''.join(body_parts).encode('utf-8')
body_bytes += video_content
body_bytes += f'\r\n--{boundary}--\r\n'.encode('utf-8')

# Headers
headers = {
    'Content-Type': f'multipart/form-data; boundary={boundary}',
    'Content-Length': str(len(body_bytes))
}

print("=== TESTE DE UPLOAD VIA API ===")
print(f"Host: {host}")
print(f"Content-Length: {len(body_bytes)} bytes")
print()

try:
    # Criar conexão
    conn = http.client.HTTPConnection(host)
    
    # Fazer requisição
    conn.request('POST', '/api/videos', body_bytes, headers)
    
    # Obter resposta
    response = conn.getresponse()
    response_data = response.read().decode('utf-8')
    
    print(f"Status: {response.status} {response.reason}")
    print("Headers:")
    for header, value in response.getheaders():
        print(f"  {header}: {value}")
    print()
    print("Body:")
    
    try:
        # Tentar parsear JSON
        json_data = json.loads(response_data)
        print(json.dumps(json_data, indent=2))
    except:
        print(response_data)
    
    conn.close()
    
except Exception as e:
    print(f"❌ Erro: {e}")

print("\n=== FIM DO TESTE ===")
