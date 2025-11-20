-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-11-2025 a las 11:32:44
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `minehostx`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backup_exports`
--

CREATE TABLE `backup_exports` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backup_restore_logs`
--

CREATE TABLE `backup_restore_logs` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `restored_by` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backup_schedules`
--

CREATE TABLE `backup_schedules` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `type` enum('daily','weekly','on_start') NOT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `backup_schedules`
--

INSERT INTO `backup_schedules` (`id`, `server_id`, `type`, `enabled`, `created_at`) VALUES
(1, 7, 'weekly', 1, '2025-11-10 08:43:22'),
(2, 8, 'daily', 1, '2025-11-10 09:29:01'),
(3, 8, 'daily', 1, '2025-11-10 09:29:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_knowledge`
--

CREATE TABLE `chatbot_knowledge` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chatbot_knowledge`
--

INSERT INTO `chatbot_knowledge` (`id`, `question`, `answer`, `category`) VALUES
(1, 'precio', 'Puedes consultar todos nuestros planes en la sección Planes del menú.', 'planes'),
(2, 'plan', 'Actualmente ofrecemos planes gratuitos y premium. Más información en la sección Planes.', 'planes'),
(3, 'crear servidor', 'Para crear un servidor, entra en tu panel y pulsa en Crear Servidor.', 'servidores'),
(4, 'error', 'Si tienes un error, por favor describe el mensaje exacto para ayudarte mejor.', 'soporte'),
(5, 'docker', 'Docker se utiliza para crear servidores aislados y seguros para cada usuario.', 'tecnico'),
(6, 'hola', '¡Hola! ¿En qué puedo ayudarte hoy?', 'saludo'),
(7, 'buenas', '¡Buenas! ¿Qué necesitas?', 'saludo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `currency` varchar(6) DEFAULT 'EUR',
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `card_mask` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `plan_id`, `amount`, `currency`, `status`, `card_mask`, `created_at`) VALUES
(1, 1, 2, 4.99, '0', 'success', '**** **** **** 7688', '2025-11-04 13:38:41'),
(2, 1, 3, 9.99, '0', 'success', '**** **** **** 3453', '2025-11-04 13:42:56'),
(3, 1, 3, 9.99, '0', 'success', '**** **** **** 3453', '2025-11-04 13:45:40'),
(4, 3, 3, 9.99, '0', 'success', '**** **** **** 4242', '2025-11-10 08:01:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `max_servers` int(11) DEFAULT 1,
  `max_ram` int(11) DEFAULT 2,
  `price` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `allow_mods` tinyint(1) DEFAULT 0,
  `allow_plugins` tinyint(1) DEFAULT 1,
  `allow_backups` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `plans`
--

INSERT INTO `plans` (`id`, `name`, `max_servers`, `max_ram`, `price`, `description`, `allow_mods`, `allow_plugins`, `allow_backups`) VALUES
(1, 'Free', 1, 2, 0.00, 'Plan gratuito básico', 0, 1, 0),
(2, 'Premium', 3, 6, 4.99, 'Plan Premium con soporte de mods, plugins y backups', 1, 1, 1),
(3, 'Pro', 5, 12, 9.99, 'Plan Enterprise con recursos dedicados', 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `software` enum('Paper','Forge','Vanilla') NOT NULL,
  `version` varchar(20) DEFAULT '1.20.1',
  `ram_gb` int(11) NOT NULL DEFAULT 1,
  `status` enum('running','stopped') DEFAULT 'stopped',
  `port` int(11) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servers`
--

INSERT INTO `servers` (`id`, `user_id`, `name`, `software`, `version`, `ram_gb`, `status`, `port`, `ip`, `created_at`) VALUES
(1, 2, 'epep', 'Paper', '1.20.1', 2, 'stopped', 25802, NULL, '2025-11-04 11:41:29'),
(7, 1, 'A170', 'Paper', '1.20.1', 2, 'running', 25039, '5gaj1gfv.minehostx.es', '2025-11-06 08:14:40'),
(8, 1, 'A170', 'Paper', '1.20.1', 2, 'stopped', 25138, 'op6c2hv.minehostx.es', '2025-11-06 08:15:20'),
(9, 1, 'A170', 'Paper', '1.20.1', 2, 'stopped', 25031, 'd8fzqeo1.minehostx.es', '2025-11-06 08:15:39'),
(10, 1, 'HW310', 'Forge', '1.20.1', 2, 'stopped', 25341, 'u1wtzuax.minehostx.es', '2025-11-07 09:46:01'),
(11, 3, '120', 'Paper', '1.20.1', 7, 'stopped', 25672, 'l55a422xo.minehostx.es', '2025-11-10 08:01:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `server_console`
--

CREATE TABLE `server_console` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `command` text NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `server_console`
--

INSERT INTO `server_console` (`id`, `server_id`, `user_id`, `command`, `response`, `created_at`) VALUES
(1, 7, 1, 'hola', 'Comando ejecutado: hola\n(Respuesta simulada del servidor)', '2025-11-06 09:34:11'),
(2, 7, 1, 'hola', 'Comando ejecutado: hola\n(Respuesta simulada del servidor)', '2025-11-06 09:34:15'),
(3, 7, 1, 'jhgfj', 'Comando ejecutado (simulado).', '2025-11-06 13:42:55'),
(4, 7, 1, '/time set day', 'Comando ejecutado (simulado).', '2025-11-06 13:43:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `server_logs`
--

CREATE TABLE `server_logs` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `categoria` varchar(50) DEFAULT 'INFO',
  `mensaje` text NOT NULL,
  `usuario` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `server_logs`
--

INSERT INTO `server_logs` (`id`, `server_id`, `categoria`, `mensaje`, `usuario`, `fecha`) VALUES
(4, 7, 'INFO', 'hola', 1, '2025-11-10 09:08:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `server_mods`
--

CREATE TABLE `server_mods` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `server_plugins`
--

CREATE TABLE `server_plugins` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `server_stats`
--

CREATE TABLE `server_stats` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `ram_used` float DEFAULT 0,
  `cpu_used` float DEFAULT 0,
  `players` int(11) DEFAULT 0,
  `tps` float DEFAULT 20,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `server_stats`
--

INSERT INTO `server_stats` (`id`, `server_id`, `ram_used`, `cpu_used`, `players`, `tps`, `created_at`) VALUES
(1, 7, 1553, 15, 0, 20.8, '2025-11-06 09:51:21'),
(2, 7, 534, 31, 2, 18.1, '2025-11-06 13:45:49'),
(3, 7, 1217, 17, 1, 18.1, '2025-11-07 07:57:16'),
(4, 7, 1394, 63, 3, 17.7, '2025-11-07 07:57:21'),
(5, 7, 1092, 24, 9, 19.4, '2025-11-07 07:57:24'),
(6, 7, 1900, 81, 8, 20, '2025-11-07 08:21:39'),
(7, 7, 1049, 43, 7, 17.9, '2025-11-07 10:14:39'),
(8, 11, 1176, 17, 1, 20.5, '2025-11-10 08:01:59'),
(9, 11, 4921, 19, 6, 19, '2025-11-10 08:02:04'),
(10, 7, 1654, 46, 8, 19.8, '2025-11-10 08:21:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `server_tasks`
--

CREATE TABLE `server_tasks` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `type` enum('restart','backup','command') NOT NULL,
  `command_text` varchar(255) DEFAULT NULL,
  `frequency` enum('1h','6h','12h','24h','weekly') NOT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `plan_id` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `plan_id`, `created_at`) VALUES
(1, 'admin', 'admin@minehostx.local', '$2y$10$MugSzrY.V0d9DXCP01dqouKwOx8E1HCGNZY/InurP09XANy8LUgUW', 'admin', 3, '2025-11-04 11:34:29'),
(2, 'nicolas', 'nico@gmail.com', '$2y$10$0egG5cL95gZ3FuHVRlkwkOUC2SiBOdbAaDnYkHmshr4xOViYwgu1a', 'user', 1, '2025-11-04 11:41:01'),
(3, 'hugo', 'hugo@gmail.com', '$2y$10$kuwobB0t3NXcIVQFtL4VOet7pE7mNu3T6IniRbWZD8zjlnuVm8BuK', 'user', 3, '2025-11-10 07:59:57');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`);

--
-- Indices de la tabla `backup_exports`
--
ALTER TABLE `backup_exports`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `backup_restore_logs`
--
ALTER TABLE `backup_restore_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `backup_schedules`
--
ALTER TABLE `backup_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `server_console`
--
ALTER TABLE `server_console`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `server_logs`
--
ALTER TABLE `server_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`),
  ADD KEY `usuario` (`usuario`);

--
-- Indices de la tabla `server_mods`
--
ALTER TABLE `server_mods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`);

--
-- Indices de la tabla `server_plugins`
--
ALTER TABLE `server_plugins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`);

--
-- Indices de la tabla `server_stats`
--
ALTER TABLE `server_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`);

--
-- Indices de la tabla `server_tasks`
--
ALTER TABLE `server_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `plan_id` (`plan_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `backup_exports`
--
ALTER TABLE `backup_exports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `backup_restore_logs`
--
ALTER TABLE `backup_restore_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `backup_schedules`
--
ALTER TABLE `backup_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `server_console`
--
ALTER TABLE `server_console`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `server_logs`
--
ALTER TABLE `server_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `server_mods`
--
ALTER TABLE `server_mods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `server_plugins`
--
ALTER TABLE `server_plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `server_stats`
--
ALTER TABLE `server_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `server_tasks`
--
ALTER TABLE `server_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `servers`
--
ALTER TABLE `servers`
  ADD CONSTRAINT `servers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `server_console`
--
ALTER TABLE `server_console`
  ADD CONSTRAINT `server_console_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `server_console_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `server_logs`
--
ALTER TABLE `server_logs`
  ADD CONSTRAINT `server_logs_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `server_logs_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `server_mods`
--
ALTER TABLE `server_mods`
  ADD CONSTRAINT `server_mods_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `server_plugins`
--
ALTER TABLE `server_plugins`
  ADD CONSTRAINT `server_plugins_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `server_stats`
--
ALTER TABLE `server_stats`
  ADD CONSTRAINT `server_stats_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `server_tasks`
--
ALTER TABLE `server_tasks`
  ADD CONSTRAINT `server_tasks_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
