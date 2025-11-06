-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-11-2025 a las 02:59:09
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdinscripcion`
--
CREATE DATABASE IF NOT EXISTS `bdinscripcion` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bdinscripcion`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aplicaciones`
--

CREATE TABLE `aplicaciones` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_vacante` int(11) NOT NULL,
  `id_docente_revisor` int(11) DEFAULT NULL,
  `fecha_revision` datetime DEFAULT NULL,
  `id_docente_supervisor` int(11) DEFAULT NULL,
  `estado` enum('Pendiente_Estudiante','Pendiente_Docente','Aprobada','Rechazada','En_Curso','Finalizada') NOT NULL DEFAULT 'Pendiente_Estudiante',
  `fecha_aplicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_seguimiento`
--

CREATE TABLE `documentos_seguimiento` (
  `id` int(11) NOT NULL,
  `id_aplicacion` int(11) NOT NULL,
  `tipo_documento` enum('Plan_Trabajo','Informe_Mensual_1','Informe_Mensual_2','Informe_Final','Constancia_Empresa') NOT NULL,
  `ruta_archivo` varchar(512) NOT NULL,
  `estado_revision` enum('Pendiente','Observado','Aprobado') NOT NULL DEFAULT 'Pendiente',
  `comentarios_docente` text DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `contacto_nombre` varchar(150) DEFAULT NULL,
  `contacto_email` varchar(100) DEFAULT NULL,
  `convenio_activo` tinyint(1) NOT NULL DEFAULT 0,
  `convenio_fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instituciones`
--

INSERT INTO `instituciones` (`id`, `nombre_empresa`, `ruc`, `direccion`, `contacto_nombre`, `contacto_email`, `convenio_activo`, `convenio_fecha_fin`) VALUES
(1, 'Antony cra', '00000000001', 'jr damian', 'Cell 28', 'cel_28@gmail.com', 1, NULL),
(2, 'Daarick28', '17171717', 'jr airita', 'Lujan Carrion', 'minishop2024@gmail.com', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `id_usuario_destino` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('estudiante','docente','admin') NOT NULL DEFAULT 'estudiante',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `apellidos`, `codigo`, `email`, `password`, `rol`, `activo`) VALUES
(1, 'Asa', 'Mitaka', '12345', 'loladare.44@gmail.com', '$2y$10$gjbaRLUKTolzFXQLSPKqLOWL5.HEMZj4tJRhs5t/38wA9e.s2hRrq', 'admin', 1),
(2, 'daniel', 'xdxd', '12345678', 'loladare_12@hotmail.com', '$2y$10$Hhrt7GFDaH8bZQl.mX0PLuXmr3emvzChuvNu97dBeu7AVE9nD/wvW', 'estudiante', 1),
(3, 'katty', 'Wiwiwa', '1214', 'nose_10@gmail.com', '$2y$10$x.90TOj1jI3Nph6slkbE9.51TcxUTfdYgS6izjNrYwFJCxrdnDCoC', 'docente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacantes`
--

CREATE TABLE `vacantes` (
  `id` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `titulo_vacante` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `area_carrera` varchar(100) DEFAULT NULL,
  `cupos_disponibles` int(3) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacantes`
--

INSERT INTO `vacantes` (`id`, `id_institucion`, `titulo_vacante`, `descripcion`, `area_carrera`, `cupos_disponibles`, `activa`) VALUES
(1, 1, 'Camaraman', 'Trabajar en audiovisuales', 'Audiovisuales', 1, 1),
(2, 2, 'Niñera', '', 'Ama de casa', 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aplicaciones`
--
ALTER TABLE `aplicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fk_id_estudiante` (`id_estudiante`),
  ADD KEY `idx_fk_id_vacante` (`id_vacante`),
  ADD KEY `idx_fk_id_docente` (`id_docente_supervisor`);

--
-- Indices de la tabla `documentos_seguimiento`
--
ALTER TABLE `documentos_seguimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fk_id_aplicacion` (`id_aplicacion`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_uq_ruc` (`ruc`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fk_id_usuario_destino` (`id_usuario_destino`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_uq_email` (`email`),
  ADD UNIQUE KEY `idx_uq_codigo` (`codigo`);

--
-- Indices de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fk_id_institucion` (`id_institucion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aplicaciones`
--
ALTER TABLE `aplicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `documentos_seguimiento`
--
ALTER TABLE `documentos_seguimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aplicaciones`
--
ALTER TABLE `aplicaciones`
  ADD CONSTRAINT `fk_aplicaciones_docente` FOREIGN KEY (`id_docente_supervisor`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aplicaciones_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aplicaciones_vacante` FOREIGN KEY (`id_vacante`) REFERENCES `vacantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `documentos_seguimiento`
--
ALTER TABLE `documentos_seguimiento`
  ADD CONSTRAINT `fk_documentos_aplicacion` FOREIGN KEY (`id_aplicacion`) REFERENCES `aplicaciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD CONSTRAINT `fk_vacantes_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
