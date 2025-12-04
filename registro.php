<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #e9ecef; }
        .register-card { max-width: 850px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        
        /* --- CORRECCIÓN DE LA CÁMARA --- */
        .video-box { 
            position: relative; 
            width: 100%; 
            height: 320px; 
            background: #000; 
            border-radius: 8px; 
            overflow: hidden; 
            margin-bottom: 1rem;
            display: flex;             /* Centrar contenido */
            align-items: center;       /* Centrar verticalmente */
            justify-content: center;   /* Centrar horizontalmente */
        }

        video, #canvas-preview { 
            /* Esto asegura que el video ocupe el espacio pero MANTENGA su proporción */
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* CLAVE: 'contain' muestra toda la imagen sin estirar (puede dejar bordes negros). 'cover' llena todo pero recorta bordes. */
            transform: scaleX(-1); /* Efecto espejo */
        }
        
        #canvas-preview { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%;
            height: 100%;
            display: none; 
        }
        /* ------------------------------- */

        .form-section { padding: 2.5rem; }
        .camera-section { padding: 2.5rem; background-color: #f8f9fa; border-left: 1px solid #eee; }
        .page-title { font-weight: 700; color: #333; margin-bottom: 1.5rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="register-card row g-0">
        
        <div class="col-md-6 form-section">
            <h3 class="page-title">Crear Cuenta</h3>
            
            <form id="formRegistro" action="modules/auth/registro.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Nombres</label>
                    <input type="text" name="nombres" class="form-control" placeholder="Ej: Juan Carlos" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez López" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Email Institucional</label>
                    <input type="email" name="email" class="form-control" placeholder="u2021...@sideral.edu.pe" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <input type="hidden" name="rol" value="estudiante">

                <input type="hidden" name="biometria_base64" id="biometria_base64" required>

                <div class="alert alert-warning small py-2 d-flex align-items-center" id="alerta-foto">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Captura tu rostro para continuar.
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="btn-submit" disabled>
                    Registrarse
                </button>
                
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none small">Ya tengo cuenta</a>
                </div>
            </form>
        </div>

        <div class="col-md-6 camera-section d-flex flex-column justify-content-center text-center">
            
            <h5 class="mb-2 fw-bold"><i class="bi bi-person-bounding-box"></i> Registro Facial</h5>
            <p class="text-muted small mb-3">Asegúrate de que tu rostro esté centrado y bien iluminado.</p>
            
            <div class="video-box shadow-sm">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas-preview"></canvas>
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-dark" id="btn-capturar" onclick="capturarFoto()">
                    <i class="bi bi-camera-fill me-1"></i> Capturar Rostro
                </button>
                
                <button type="button" class="btn btn-outline-danger" id="btn-retake" onclick="repetirFoto()" style="display:none;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Repetir Foto
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas-preview');
    const inputBase64 = document.getElementById('biometria_base64');
    const btnSubmit = document.getElementById('btn-submit');
    const alerta = document.getElementById('alerta-foto');
    const btnCapturar = document.getElementById('btn-capturar');
    const btnRetake = document.getElementById('btn-retake');

    // 1. Encender Cámara
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => { video.srcObject = stream; })
        .catch(err => {
            alert("No se detecta cámara. Por favor verifica tus permisos.");
        });

    // 2. Tomar Foto (CORREGIDO PARA EVITAR DISTORSIÓN)
    function capturarFoto() {
        const context = canvas.getContext('2d');
        
        // IMPORTANTE: Ajustamos el tamaño interno del canvas al tamaño REAL del video
        // Esto evita que se estire la imagen al guardarla
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Dibujamos el video tal cual viene de la cámara
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const dataURL = canvas.toDataURL('image/png');
        inputBase64.value = dataURL;

        // Mostrar elementos
        canvas.style.display = 'block';
        video.style.display = 'none';

        btnCapturar.style.display = 'none';
        btnRetake.style.display = 'block';
        
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('btn-secondary');
        btnSubmit.classList.add('btn-primary');
        
        alerta.className = "alert alert-success small py-2 d-flex align-items-center";
        alerta.innerHTML = "<i class='bi bi-check-circle-fill me-2'></i> Rostro capturado correctamente.";
    }

    function repetirFoto() {
        inputBase64.value = "";
        canvas.style.display = 'none';
        video.style.display = 'block';
        btnCapturar.style.display = 'block';
        btnRetake.style.display = 'none';
        btnSubmit.disabled = true;
        alerta.className = "alert alert-warning small py-2 d-flex align-items-center";
        alerta.innerHTML = "<i class='bi bi-exclamation-triangle-fill me-2'></i> Captura tu rostro para continuar.";
    }
</script>

</body>
</html>