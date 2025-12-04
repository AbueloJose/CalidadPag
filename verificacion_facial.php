<?php
include 'templates/header.php';

// Seguridad: Si no hay sesión, fuera.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<style>
    .video-container {
        position: relative;
        width: 400px;
        height: 300px;
        margin: 0 auto;
        background: #000;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #333;
    }
    video {
        position: absolute;
        top: 0;
        left: 0;
        transform: scaleX(-1); /* Efecto espejo */
    }
    /* Canvas para dibujar el recuadro de la IA */
    canvas.overlay {
        position: absolute;
        top: 0;
        left: 0;
    }
</style>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white text-center">
                <h4><i class="bi bi-cpu-fill"></i> Verificación Biométrica Manual</h4>
            </div>
            <div class="card-body text-center">
                <p class="text-muted mb-2">
                    Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario_nombres']); ?></strong>
                </p>
                <div id="status-badge" class="badge bg-secondary mb-3">Cargando IA...</div>

                <div class="video-container">
                    <video id="videoElement" autoplay playsinline width="400" height="300"></video>
                    <canvas id="canvasOverlay" class="overlay" width="400" height="300"></canvas>
                </div>

                <canvas id="canvasCapture" width="400" height="300" style="display:none;"></canvas>

                <div class="mt-4">
                    <button type="button" class="btn btn-primary w-100" id="btnVerificar" onclick="intentarVerificacion()" disabled>
                        <i class="bi bi-person-bounding-box"></i> Verificar Identidad
                    </button>
                    
                    <div id="mensajeError" class="text-danger mt-2 fw-bold" style="display:none;"></div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

<script>
    const video = document.getElementById('videoElement');
    const canvasOverlay = document.getElementById('canvasOverlay');
    const canvasCapture = document.getElementById('canvasCapture');
    const btnVerificar = document.getElementById('btnVerificar');
    const statusBadge = document.getElementById('status-badge');
    const errorDiv = document.getElementById('mensajeError');

    // Variable global para saber si hay una cara AHORA MISMO
    let rostrosDetectados = [];

    // 1. Cargar Modelos
    async function loadModels() {
        statusBadge.className = 'badge bg-warning text-dark';
        statusBadge.innerText = 'Cargando Modelos...';
        
        try {
            const modelUrl = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
            await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
            startVideo();
        } catch (error) {
            console.error(error);
            statusBadge.className = 'badge bg-danger';
            statusBadge.innerText = 'Error de conexión IA';
        }
    }

    // 2. Iniciar Video
    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                statusBadge.className = 'badge bg-info text-dark';
                statusBadge.innerText = 'Esperando rostro...';
                // Habilitamos el botón ahora que hay video
                btnVerificar.disabled = false;
            })
            .catch(err => console.error(err));
    }

    // 3. Bucle de Detección (Solo visualización)
    video.addEventListener('play', () => {
        const displaySize = { width: video.width, height: video.height };
        faceapi.matchDimensions(canvasOverlay, displaySize);

        setInterval(async () => {
            // Detectar caras
            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
            
            // Guardamos el resultado en la variable global para usarla cuando hagas clic
            rostrosDetectados = detections;

            // Dibujar el cuadro azul
            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            const context = canvasOverlay.getContext('2d');
            context.clearRect(0, 0, canvasOverlay.width, canvasOverlay.height);
            faceapi.draw.drawDetections(canvasOverlay, resizedDetections);

            // Actualizar etiqueta de estado visualmente
            if (detections.length > 0 && detections[0].score > 0.5) {
                statusBadge.className = 'badge bg-success';
                statusBadge.innerText = 'Rostro Detectado - Listo para Verificar';
            } else {
                statusBadge.className = 'badge bg-secondary';
                statusBadge.innerText = 'No se detecta rostro';
            }

        }, 100); // 10 veces por segundo
    });

    // 4. Función del BOTÓN MANUAL
    function intentarVerificacion() {
        errorDiv.style.display = 'none';

        // Validar si la IA está viendo algo en este momento
        if (rostrosDetectados.length > 0 && rostrosDetectados[0].score > 0.5) {
            
            // ¡SÍ HAY CARA! PROCEDEMOS A GUARDAR
            btnVerificar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verificando...';
            btnVerificar.disabled = true;
            
            tomarFotoYEnviar();

        } else {
            // NO HAY CARA
            errorDiv.style.display = 'block';
            errorDiv.innerText = '⚠️ No puedo verificarte. Asegúrate de que el cuadro azul marque tu rostro.';
        }
    }

    // 5. Enviar al servidor
    function tomarFotoYEnviar() {
        const contextCap = canvasCapture.getContext('2d');
        contextCap.drawImage(video, 0, 0, 400, 300);
        const dataURL = canvasCapture.toDataURL('image/png');

        fetch('modules/auth/guardar_rostro_acceso.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ imagen: dataURL })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                statusBadge.className = 'badge bg-primary';
                statusBadge.innerText = '¡Acceso Concedido!';
                
                // Redirigir
                window.location.href = "dashboard_<?php echo $_SESSION['usuario_rol']; ?>.php";
            } else {
                btnVerificar.disabled = false;
                btnVerificar.innerText = 'Reintentar Verificación';
                errorDiv.innerText = 'Error al guardar la imagen.';
                errorDiv.style.display = 'block';
            }
        });
    }

    // Iniciar sistema
    loadModels();

</script>

<?php include 'templates/footer.php'; ?>