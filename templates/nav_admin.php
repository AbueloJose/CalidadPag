<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="dashboard_admin.php">
        <i class="bi bi-shield-lock-fill"></i> Panel de Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin" aria-controls="navAdmin" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="admin_gestionar_usuarios.php">Usuarios </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_gestionar_convenios.php">Convenios </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_gestionar_vacantes.php">Vacantes </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="modules/reportes/generar_reporte_pdf.php" target="_blank">Reportes </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($_SESSION['usuario_nombres']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="modules/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>