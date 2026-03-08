-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-03-2026 a las 00:51:26
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
-- Base de datos: `sistema_parksys`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(120) NOT NULL,
  `detalle` text DEFAULT NULL,
  `ip` varchar(60) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `detalle`, `ip`, `creado_en`) VALUES
(1, 1, 'LOGO_UPLOAD', 'Logo actualizado', '::1', '2026-03-07 23:04:45'),
(2, 1, 'LOGOUT', 'Cierre de sesion', '::1', '2026-03-07 23:05:02'),
(3, 1, 'LOGIN_OK', 'Inicio de sesion', '::1', '2026-03-07 23:05:12'),
(4, 1, 'LOGO_UPLOAD', 'Logo actualizado', '::1', '2026-03-07 23:15:09'),
(5, 1, 'LOGOUT', 'Cierre de sesion', '::1', '2026-03-07 23:15:17'),
(6, 1, 'LOGIN_OK', 'Inicio de sesion', '::1', '2026-03-07 23:17:46'),
(7, 1, 'REPORTE_DELETE', 'Registro ID: 4', '::1', '2026-03-07 23:18:13'),
(8, 1, 'ENTRADA_REGISTRADA', 'Placa: JQM283, Ticket: TKWS3BBJ24', '::1', '2026-03-07 23:21:26'),
(9, 1, 'SALIDA_REGISTRADA', 'Placa: JQM283, Factura: FCP574B6PB', '::1', '2026-03-07 23:22:36'),
(10, 1, 'SIMULAR_COBRO', 'Categoria: BICICLETA, minutos: 60', '::1', '2026-03-07 23:23:11'),
(11, 1, 'LOGOUT', 'Cierre de sesion', '::1', '2026-03-07 23:25:04'),
(12, 1, 'LOGIN_OK', 'Inicio de sesion', '::1', '2026-03-07 23:25:11'),
(13, 1, 'ENTRADA_REGISTRADA', 'Placa: ANM777, Ticket: TKZ3GN2WTL', '::1', '2026-03-07 23:26:28'),
(14, 1, 'LOGOUT', 'Cierre de sesion', '::1', '2026-03-07 23:31:41'),
(15, 1, 'LOGIN_OK', 'Inicio de sesion', '::1', '2026-03-07 23:32:28'),
(16, 1, 'LOGOUT', 'Cierre de sesion', '::1', '2026-03-07 23:41:02'),
(17, 1, 'LOGIN_OK', 'Inicio de sesion', '::1', '2026-03-07 23:41:19'),
(18, 1, 'LOGO_UPLOAD', 'Logo actualizado', '::1', '2026-03-07 23:41:29'),
(19, 1, 'LOGOUT', 'Cierre de sesion', '::1', '2026-03-07 23:42:21'),
(20, 1, 'LOGIN_OK', 'Inicio de sesion', '::1', '2026-03-07 23:43:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_vehiculo`
--

CREATE TABLE `categorias_vehiculo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `valor_hora` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_vehiculo`
--

INSERT INTO `categorias_vehiculo` (`id`, `nombre`, `valor_hora`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 'CARRO', 3500.00, 1, '2026-03-06 23:29:24', '2026-03-06 23:29:24'),
(2, 'MOTO', 2000.00, 1, '2026-03-06 23:29:24', '2026-03-06 23:29:24'),
(3, 'BICICLETA', 1000.00, 1, '2026-03-06 23:29:24', '2026-03-06 23:29:24'),
(4, 'CAMIONETA', 4500.00, 1, '2026-03-06 23:29:24', '2026-03-06 23:29:24'),
(5, 'CAMION', 6000.00, 1, '2026-03-06 23:29:24', '2026-03-06 23:29:24'),
(6, 'BUS', 7000.00, 1, '2026-03-06 23:29:24', '2026-03-06 23:29:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(80) NOT NULL,
  `valor` text DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `actualizado_en`) VALUES
(1, 'logo_url', 'assets/img/logo-custom.png?v=1772926889', '2026-03-07 23:41:29'),
(5, 'modo_cobro', 'POR_MINUTO', '2026-03-07 22:57:49'),
(6, 'minutos_gracia', '5', '2026-03-07 22:57:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas_vehiculo`
--

CREATE TABLE `marcas_vehiculo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marcas_vehiculo`
--

INSERT INTO `marcas_vehiculo` (`id`, `nombre`, `activo`, `creado_en`) VALUES
(1, 'CHEVROLET', 1, '2026-03-06 23:29:24'),
(2, 'RENAULT', 1, '2026-03-06 23:29:24'),
(3, 'KIA', 1, '2026-03-06 23:29:24'),
(4, 'YAMAHA', 1, '2026-03-06 23:29:24'),
(5, 'SUZUKI', 1, '2026-03-06 23:29:24'),
(6, 'MAZDA', 1, '2026-03-06 23:29:24'),
(7, 'TOYOTA', 1, '2026-03-07 23:26:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos_vehiculo`
--

CREATE TABLE `modelos_vehiculo` (
  `id` int(11) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modelos_vehiculo`
--

INSERT INTO `modelos_vehiculo` (`id`, `marca_id`, `nombre`, `categoria_id`, `activo`, `creado_en`) VALUES
(1, 1, 'SPARK GT', 1, 1, '2026-03-06 23:29:25'),
(2, 2, 'LOGAN', 1, 1, '2026-03-06 23:29:25'),
(3, 4, 'FZ', 2, 1, '2026-03-06 23:29:25'),
(4, 5, 'GRAND VITARA', 4, 1, '2026-03-07 21:48:04'),
(5, 4, 'FZ150', 2, 1, '2026-03-07 23:21:26'),
(6, 7, 'HILUX', 4, 1, '2026-03-07 23:26:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  `valor_pagado` decimal(10,2) NOT NULL,
  `metodo` enum('EFECTIVO','TRANSFERENCIA','TARJETA','QR') NOT NULL DEFAULT 'EFECTIVO',
  `estado` enum('PAGADO','PENDIENTE') NOT NULL DEFAULT 'PAGADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `registro_id`, `usuario_id`, `fecha_pago`, `valor_pagado`, `metodo`, `estado`) VALUES
(3, 5, 1, '2026-03-07 18:22:36', 0.00, 'EFECTIVO', 'PAGADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(80) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_parqueo`
--

CREATE TABLE `registros_parqueo` (
  `id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `ubicacion_id` int(11) NOT NULL,
  `usuario_entrada_id` int(11) NOT NULL,
  `usuario_salida_id` int(11) DEFAULT NULL,
  `hora_entrada` datetime NOT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `total_minutos` int(11) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `ticket_codigo` varchar(20) NOT NULL,
  `factura_codigo` varchar(20) DEFAULT NULL,
  `estado` enum('ACTIVO','FINALIZADO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `registros_parqueo`
--

INSERT INTO `registros_parqueo` (`id`, `vehiculo_id`, `ubicacion_id`, `usuario_entrada_id`, `usuario_salida_id`, `hora_entrada`, `hora_salida`, `total_minutos`, `valor_total`, `ticket_codigo`, `factura_codigo`, `estado`, `creado_en`) VALUES
(5, 5, 1, 1, 1, '2026-03-07 18:21:26', '2026-03-07 18:22:36', 2, 0.00, 'TKWS3BBJ24', 'FCP574B6PB', 'FINALIZADO', '2026-03-07 23:21:26'),
(6, 6, 3, 1, NULL, '2026-03-07 18:26:28', NULL, NULL, NULL, 'TKZ3GN2WTL', NULL, 'ACTIVO', '2026-03-07 23:26:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id` int(11) NOT NULL,
  `tipo_vehiculo` enum('CARRO','MOTO','BICICLETA') NOT NULL,
  `valor_hora` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id`, `tipo_vehiculo`, `valor_hora`, `activo`, `actualizado_en`) VALUES
(1, 'CARRO', 3500.00, 1, '2026-03-06 22:27:24'),
(2, 'MOTO', 2000.00, 1, '2026-03-06 22:27:24'),
(3, 'BICICLETA', 1000.00, 1, '2026-03-06 22:27:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `zona` varchar(50) NOT NULL,
  `estado` enum('LIBRE','OCUPADO','MANTENIMIENTO') NOT NULL DEFAULT 'LIBRE',
  `observacion` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ubicaciones`
--

INSERT INTO `ubicaciones` (`id`, `codigo`, `zona`, `estado`, `observacion`, `creado_en`) VALUES
(1, 'A01', 'A', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(2, 'A02', 'A', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(3, 'A03', 'A', 'OCUPADO', NULL, '2026-03-06 23:29:25'),
(4, 'A04', 'A', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(5, 'A05', 'A', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(6, 'B01', 'B', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(7, 'B02', 'B', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(8, 'B03', 'B', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(9, 'B04', 'B', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(10, 'B05', 'B', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(11, 'C01', 'C', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(12, 'C02', 'C', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(13, 'C03', 'C', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(14, 'C04', 'C', 'LIBRE', NULL, '2026-03-06 23:29:25'),
(15, 'C05', 'C', 'LIBRE', NULL, '2026-03-06 23:29:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('SUPERADMIN','ADMIN','OPERADOR') NOT NULL DEFAULT 'OPERADOR',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `activo`, `creado_en`) VALUES
(1, 'Super Usuario', 'jamesq771@gmail.com', '$2y$10$pJI8xVvic721Uj9aUf.GAeQhnCDel7jEIrNqzxRd/eVvhpMM4AGaO', 'SUPERADMIN', 1, '2026-03-06 23:29:25'),
(2, 'Administrador', 'administrador@gmail.com', '$2y$10$yiTUDgYHjtfil1e6POcRg.nL7xpDcsaC00IXqLEUd2HQkrRjgfy7O', 'ADMIN', 1, '2026-03-06 23:29:25'),
(3, 'Operador', 'operador@gmail.com', '$2y$10$aMg/6tTjkpYhJmzSpbEA0.EI/InE8pZ.9iH6Dr7Ymp9BRp/en.rj6', 'OPERADOR', 1, '2026-03-06 23:29:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `modelo_id` int(11) DEFAULT NULL,
  `color` varchar(40) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `placa`, `categoria_id`, `marca_id`, `modelo_id`, `color`, `creado_en`, `actualizado_en`) VALUES
(1, 'BLL25M', 2, 4, 3, 'blanco', '2026-03-07 19:52:44', '2026-03-07 19:52:44'),
(2, 'YXZ47F', 1, 1, 1, 'Negro', '2026-03-07 20:44:42', '2026-03-07 20:44:42'),
(3, 'ASK1042', 4, 5, 4, 'Gris', '2026-03-07 21:48:04', '2026-03-07 21:48:04'),
(4, 'AMM123', 4, 1, 1, 'blanco', '2026-03-07 22:32:28', '2026-03-07 22:32:28'),
(5, 'JQM283', 2, 4, 5, 'Azul', '2026-03-07 23:21:26', '2026-03-07 23:21:26'),
(6, 'ANM777', 4, 7, 6, 'BLANCO', '2026-03-07 23:26:28', '2026-03-07 23:26:28');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_fecha` (`usuario_id`,`creado_en`);

--
-- Indices de la tabla `categorias_vehiculo`
--
ALTER TABLE `categorias_vehiculo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `marcas_vehiculo`
--
ALTER TABLE `marcas_vehiculo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `modelos_vehiculo`
--
ALTER TABLE `modelos_vehiculo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_modelo_marca` (`marca_id`,`nombre`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registro_id` (`registro_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_password_resets_usuario` (`usuario_id`);

--
-- Indices de la tabla `registros_parqueo`
--
ALTER TABLE `registros_parqueo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_codigo` (`ticket_codigo`),
  ADD UNIQUE KEY `factura_codigo` (`factura_codigo`),
  ADD KEY `vehiculo_id` (`vehiculo_id`),
  ADD KEY `ubicacion_id` (`ubicacion_id`),
  ADD KEY `usuario_entrada_id` (`usuario_entrada_id`),
  ADD KEY `usuario_salida_id` (`usuario_salida_id`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_vehiculo` (`tipo_vehiculo`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `marca_id` (`marca_id`),
  ADD KEY `modelo_id` (`modelo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `categorias_vehiculo`
--
ALTER TABLE `categorias_vehiculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `marcas_vehiculo`
--
ALTER TABLE `marcas_vehiculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `modelos_vehiculo`
--
ALTER TABLE `modelos_vehiculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registros_parqueo`
--
ALTER TABLE `registros_parqueo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `modelos_vehiculo`
--
ALTER TABLE `modelos_vehiculo`
  ADD CONSTRAINT `modelos_vehiculo_ibfk_1` FOREIGN KEY (`marca_id`) REFERENCES `marcas_vehiculo` (`id`),
  ADD CONSTRAINT `modelos_vehiculo_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_vehiculo` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`registro_id`) REFERENCES `registros_parqueo` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `registros_parqueo`
--
ALTER TABLE `registros_parqueo`
  ADD CONSTRAINT `registros_parqueo_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`),
  ADD CONSTRAINT `registros_parqueo_ibfk_2` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`),
  ADD CONSTRAINT `registros_parqueo_ibfk_3` FOREIGN KEY (`usuario_entrada_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `registros_parqueo_ibfk_4` FOREIGN KEY (`usuario_salida_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_vehiculo` (`id`),
  ADD CONSTRAINT `vehiculos_ibfk_2` FOREIGN KEY (`marca_id`) REFERENCES `marcas_vehiculo` (`id`),
  ADD CONSTRAINT `vehiculos_ibfk_3` FOREIGN KEY (`modelo_id`) REFERENCES `modelos_vehiculo` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
