<?php
// 1. Iniciar sesión para manejar mensajes de error/éxito
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }
        /* ESTILO ESPECÍFICO PARA EL BOTÓN FACIAL */
        .btn-facial {
            background-color: #fff;
            color: #333;
            border: 2px solid #e9ecef;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-facial:hover {
            background-color: #333;
            color: #fff;
            border-color: #333;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Sistema de Prácticas</h3>
            <p class="text-muted small">Ingresa tus credenciales para continuar</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center p-2 mb-3 small border-0 bg-danger-subtle text-danger">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['exito'])): ?>
            <div class="alert alert-success text-center p-2 mb-3 small border-0 bg-success-subtle text-success">
                <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($_GET['exito']); ?>
            </div>
        <?php endif; ?>

        <form action="modules/auth/login.php" method="POST">
            
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">Email o Código</label>
                <input type="text" class="form-control" name="email_o_codigo" required placeholder="ej: usuario@sideral.edu.pe">
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">Contraseña</label>
                <input type="password" class="form-control" name="password" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-semibold">
                Ingresar
            </button>

            <a href="login_facial.php" class="btn btn-facial w-100 py-2 mb-4 text-decoration-none">
                <i class="bi bi-person-bounding-box me-2"></i> Ingreso Facial
            </a>

            <div class="text-center pt-2 border-top">
                <a href="registro.php" class="text-primary text-decoration-none small fw-semibold">
                    ¿No tienes cuenta? Regístrate aquí
                </a>
            </div>

        </form>
    </div>

</body>
</html>