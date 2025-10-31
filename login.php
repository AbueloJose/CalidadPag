<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin: 5rem auto 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm border-0 login-container p-4">
            <div class="card-body">
                <h3 class="text-center mb-4">Sistema de Prácticas</h3>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['exito'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['exito']); ?></div>
                <?php endif; ?>

                <form action="modules/auth/login.php" method="POST">
                    <div class="mb-3">
                        <label for="email_o_codigo" class="form-label">Email o Código</label>
                        <input type="text" class="form-control" id="email_o_codigo" name="email_o_codigo" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Ingresar</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>