<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #005A9C;">
  <div class="container">
    <a class="navbar-brand" href="dashboard_estudiante.php">
      <i class="bi bi-mortarboard-fill"></i> Portal del Estudiante
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navEstudiante" aria-controls="navEstudiante" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navEstudiante">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="estudiante_buscar_vacantes.php">Buscar Vacantes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="estudiante_mi_practica.php">Mi Práctica</a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($_SESSION['usuario_nombres']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            
            <li><a class="dropdown-item" href="mi_perfil.php"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="modules/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>