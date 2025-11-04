<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 REST API - DynamicCRUD</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card h2 { margin-bottom: 15px; color: #333; }
        .endpoint { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #667eea; }
        .endpoint .method { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-right: 10px; }
        .method.get { background: #28a745; color: white; }
        .method.post { background: #007bff; color: white; }
        .method.put { background: #ffc107; color: black; }
        .method.delete { background: #dc3545; color: white; }
        .endpoint .path { font-family: monospace; color: #333; }
        .endpoint .desc { color: #666; font-size: 14px; margin-top: 5px; }
        .test-panel { background: #f8f9fa; padding: 20px; border-radius: 8px; }
        .test-panel input, .test-panel textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; }
        .test-panel button { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .test-panel button:hover { background: #5568d3; }
        .response { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 6px; margin-top: 15px; font-family: monospace; font-size: 13px; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        .token-display { background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 4px; margin: 10px 0; font-family: monospace; font-size: 12px; word-break: break-all; }
        .btn-group { display: flex; gap: 10px; margin-top: 10px; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 REST API Generator</h1>
            <p>API REST automática generada desde la estructura de la base de datos</p>
        </div>

        <div class="card">
            <h2>📚 Endpoints Disponibles</h2>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/v1/</span>
                <div class="desc">Información de la API</div>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/v1/docs</span>
                <div class="desc">Especificación OpenAPI/Swagger</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/api/v1/auth/login</span>
                <div class="desc">Autenticación - Obtener JWT token</div>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/v1/{table}</span>
                <div class="desc">Listar registros (paginado: ?page=1&per_page=20)</div>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/v1/{table}/{id}</span>
                <div class="desc">Obtener registro por ID</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/api/v1/{table}</span>
                <div class="desc">Crear nuevo registro</div>
            </div>

            <div class="endpoint">
                <span class="method put">PUT</span>
                <span class="path">/api/v1/{table}/{id}</span>
                <div class="desc">Actualizar registro</div>
            </div>

            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/api/v1/{table}/{id}</span>
                <div class="desc">Eliminar registro</div>
            </div>
        </div>

        <div class="card">
            <h2>🧪 Probar API</h2>
            
            <div class="test-panel">
                <h3>1. Login (Obtener Token)</h3>
                <input type="email" id="loginEmail" placeholder="Email" value="admin@example.com">
                <input type="password" id="loginPassword" placeholder="Password" value="admin123">
                <button onclick="login()">🔐 Login</button>
                
                <div id="tokenDisplay" style="display:none;">
                    <strong>Token JWT:</strong>
                    <div class="token-display" id="tokenValue"></div>
                </div>

                <h3 style="margin-top: 30px;">2. Hacer Request</h3>
                <select id="method" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                </select>
                
                <input type="text" id="endpoint" placeholder="Endpoint (ej: users, users/1, docs)" value="users">
                
                <textarea id="body" rows="5" placeholder='Body JSON (para POST/PUT)&#10;Ejemplo:&#10;{&#10;  "name": "John Doe",&#10;  "email": "john@example.com"&#10;}'></textarea>
                
                <div class="btn-group">
                    <button onclick="makeRequest()">📡 Enviar Request</button>
                    <button onclick="getDocs()" class="btn-secondary">📚 Ver Docs</button>
                </div>

                <div id="response" class="response" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
        let token = null;

        async function login() {
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            showResponse({ status: 'Enviando login...' });

            try {
                const response = await fetch('api.php/v1/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();
                
                if (data.token) {
                    token = data.token;
                    document.getElementById('tokenDisplay').style.display = 'block';
                    document.getElementById('tokenValue').textContent = token;
                }
                
                showResponse(data);
            } catch (error) {
                showResponse({ error: error.message, stack: error.stack });
            }
        }

        async function makeRequest() {
            const method = document.getElementById('method').value;
            const endpoint = document.getElementById('endpoint').value;
            const body = document.getElementById('body').value;

            const options = {
                method: method,
                headers: { 'Content-Type': 'application/json' }
            };

            if (token) {
                options.headers['Authorization'] = `Bearer ${token}`;
            }

            if ((method === 'POST' || method === 'PUT') && body) {
                options.body = body;
            }

            try {
                const response = await fetch(`api.php/v1/${endpoint}`, options);
                const data = await response.json();
                showResponse(data);
            } catch (error) {
                showResponse({ error: error.message });
            }
        }

        async function getDocs() {
            try {
                const response = await fetch('api.php/v1/docs');
                const data = await response.json();
                showResponse(data);
            } catch (error) {
                showResponse({ error: error.message });
            }
        }

        function showResponse(data) {
            const responseDiv = document.getElementById('response');
            responseDiv.style.display = 'block';
            responseDiv.textContent = JSON.stringify(data, null, 2);
        }
    </script>
</body>
</html>
