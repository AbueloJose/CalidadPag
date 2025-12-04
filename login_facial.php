<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Biométrico - Sistema de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background-color: #f0f2f5; /* Mismo fondo gris que el login normal */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .video-container {
            position: relative;
            width: 100%;
            height: 280px;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
            border: 2px solid #e9ecef;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Efecto espejo */
        }
        /* Ocultar imagen de referencia */
        #ref-img { display: none; }
        
        .status-indicator {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .status-loading { background: #fff3cd; color: #856404; }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark"><i class="bi bi-scan"></i> Acceso Biométrico</h3>
            <p class="text-muted small">Ingresa sin contraseña usando tu rostro</p>
        </div>

        <div id="step-1">
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Correo Institucional</label>
                <input type="email" id="email-input" class="form-control" placeholder="usuario@sideral.edu.pe">
            </div>
            
            <button onclick="buscarUsuario()" class="btn btn-primary w-100 py-2 mb-3">
                <i class="bi bi-search"></i> Buscar mi Rostro
            </button>
            
            <div class="text-center border-top pt-3">
                <a href="login.php" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left"></i> Volver al login normal
                </a>
            </div>
        </div>

        <div id="step-2" style="display:none;" class="text-center">
            
            <div id="status-badge" class="status-indicator status-loading">
                <span class="spinner-border spinner-border-sm me-1"></span> Iniciando cámara...
            </div>
            
            <div class="video-container shadow-sm">
                <video id="video" autoplay playsinline></video>
            </div>
            
            <img id="ref-img" src="" crossorigin="anonymous">

            <div id="msg-detalles" class="text-muted small mb-3">
                Por favor, quédate quieto y mira a la cámara.
            </div>

            <button onclick="location.reload()" class="btn btn-outline-secondary w-100 btn-sm">
                Cancelar
            </button>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

    <script>
        const video = document.getElementById('video');
        const refImg = document.getElementById('ref-img');
        const statusBadge = document.getElementById('status-badge');
        const msgDetalles = document.getElementById('msg-detalles');
        let userEmail = '';

        // --- PASO 1: Buscar usuario ---
        async function buscarUsuario() {
            const input = document.getElementById('email-input');
            userEmail = input.value.trim();

            if (!userEmail) {
                alert("Por favor escribe tu correo.");
                return;
            }
            
            // Cambiar botón a cargando
            const btn = document.querySelector('#step-1 button');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buscando...';

            try {
                // Consultamos al backend
                const response = await fetch('modules/auth/get_foto_usuario.php?email=' + userEmail);
                const data = await response.json();

                if (data.success) {
                    // Si existe, pasamos al paso 2
                    document.getElementById('step-1').style.display = 'none';
                    document.getElementById('step-2').style.display = 'block';
                    
                    // Cargamos la foto recuperada
                    refImg.src = data.foto_biometria; 
                    
                    // Iniciamos la IA
                    statusBadge.innerText = "Cargando Inteligencia Artificial...";
                    iniciarIA();
                } else {
                    alert("No encontramos una cuenta con registro facial para este correo. Regístrate primero.");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-search"></i> Buscar mi Rostro';
                }
            } catch (error) {
                console.error(error);
                alert("Error de conexión con el servidor.");
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-search"></i> Buscar mi Rostro';
            }
        }

        // --- PASO 2: Cargar Modelos IA ---
        async function iniciarIA() {
            try {
                // Cargar modelos necesarios
                const modelUrl = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                await faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl);
                await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
                await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);
                
                startVideo();
            } catch (e) { 
                statusBadge.className = "status-indicator status-error";
                statusBadge.innerText = "Error cargando modelos IA";
            }
        }

        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                    statusBadge.innerText = "Analizando rostro de referencia...";
                })
                .catch(err => {
                    statusBadge.className = "status-indicator status-error";
                    statusBadge.innerText = "No se detecta cámara";
                });
        }

        // --- PASO 3: Comparación en Bucle ---
        video.addEventListener('play', async () => {
            
            // 1. Procesar la foto de referencia (la que subió al registrarse)
            const refDetection = await faceapi.detectSingleFace(refImg).withFaceLandmarks().withFaceDescriptor();
            
            if (!refDetection) {
                statusBadge.className = "status-indicator status-error";
                statusBadge.innerText = "Error: Foto de registro inválida";
                msgDetalles.innerText = "Tu foto de perfil no tiene una cara clara. Debes registrarte de nuevo.";
                return;
            }
            
            // Crear el comparador
            const faceMatcher = new faceapi.FaceMatcher(refDetection);
            
            statusBadge.className = "status-indicator status-loading";
            statusBadge.innerText = "Escaneando... Mira a la cámara";

            // 2. Escanear video en vivo cada 500ms
            const interval = setInterval(async () => {
                if (video.paused || video.ended) return;

                const liveDetection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

                if (liveDetection) {
                    // Comparar
                    const bestMatch = faceMatcher.findBestMatch(liveDetection.descriptor);
                    const distancia = bestMatch.distance; 

                    // Umbral: Menor a 0.55 es la misma persona
                    if (distancia < 0.55) { 
                        clearInterval(interval); // Dejar de escanear
                        
                        statusBadge.className = "status-indicator status-success";
                        statusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> ¡Identidad Confirmada!';
                        msgDetalles.innerText = "Redirigiendo al sistema...";
                        video.pause();
                        
                        // Redirigir al login exitoso
                        setTimeout(() => {
                            window.location.href = "modules/auth/login_facial_backend.php?email=" + userEmail;
                        }, 1000);
                        
                    } else {
                        // Rostro detectado pero no coincide
                        statusBadge.className = "status-indicator status-error";
                        statusBadge.innerText = "Rostro no coincide";
                        msgDetalles.innerText = "Esa no parece ser tu cara registrada.";
                    }
                }
            }, 500);
        });
    </script>
</body>
</html>