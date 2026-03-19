-- Modelo: Grok (xAI) - Chunk 1: Crear tablas base
-- Fecha generación: Marzo 2026
-- Motor: MariaDB / InnoDB - utf8mb4 en todas las tablas
-- Reglas: No booleanos, fechas en DATETIME, CONVERT_TZ para NOW(), sin NOW() directo

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. cat_empresas (clientes)
CREATE TABLE `cat_empresas` (
  `empresas_id`      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresas_name`    VARCHAR(100) NOT NULL DEFAULT '',
  `empresas_dbegin`  DATETIME DEFAULT NULL,
  `empresas_dend`    DATETIME DEFAULT NULL,
  `empresas_horasg`  TINYINT UNSIGNED NOT NULL DEFAULT '0',    -- horas anuales contratadas
  `empresas_correoc` VARCHAR(100) DEFAULT NULL,
  `empresas_recibec` VARCHAR(3) DEFAULT 'no',                   -- 'si'/'no'
  `empresas_comment` TEXT,
  PRIMARY KEY (`empresas_id`),
  UNIQUE KEY `uk_empresas_name` (`empresas_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. cat_prioridades
CREATE TABLE `cat_prioridades` (
  `prioridades_id`    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prioridades_name`  VARCHAR(40) NOT NULL DEFAULT '',
  `prioridades_order` TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `prioridades_color` VARCHAR(6) NOT NULL DEFAULT '',           -- hex sin #
  PRIMARY KEY (`prioridades_id`),
  KEY `idx_prioridades_order` (`prioridades_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserts por defecto (colores Bootstrap)
INSERT INTO `cat_prioridades` (`prioridades_id`, `prioridades_name`, `prioridades_order`, `prioridades_color`) VALUES
(1, 'Baja',    4, '28a745'),  -- success / verde
(2, 'Media',   3, 'ffc107'),  -- warning / amarillo
(3, 'Alta',    2, 'dc3545'),  -- danger / rojo
(4, 'Urgente', 1, '343a40');  -- dark / negro

-- 3. cat_usuarios
CREATE TABLE `cat_usuarios` (
  `users_id`        TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresas_id`     TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `users_name`      VARCHAR(50) NOT NULL DEFAULT '',
  `users_username`  VARCHAR(16) NOT NULL DEFAULT '',
  `users_password`  VARCHAR(255) NOT NULL DEFAULT '',
  `users_email`     VARCHAR(100) NOT NULL DEFAULT '',
  `users_lastlogin` DATETIME DEFAULT NULL,
  `users_newlogin`  DATETIME DEFAULT NULL,
  `users_admin`     VARCHAR(10) NOT NULL DEFAULT 'User',       -- Admin / Consultor / Master / User
  `users_status`    TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `users_tips`      VARCHAR(25) NOT NULL DEFAULT 'Ninguno',
  `telcelular`      VARCHAR(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`users_id`),
  UNIQUE KEY `uk_users_username` (`users_username`),
  KEY `idx_users_email` (`users_email`),
  KEY `idx_users_admin` (`users_admin`),
  KEY `idx_users_status` (`users_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. cat_productos (Sistemas - nombre configurable en header)
CREATE TABLE `cat_productos` (
  `productos_id`      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `productos_name`    VARCHAR(40) NOT NULL DEFAULT '',
  `productos_comment` TEXT,
  PRIMARY KEY (`productos_id`),
  UNIQUE KEY `uk_productos_name` (`productos_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. cat_subproductos (Procesos - nombre configurable en header)
CREATE TABLE `cat_subproductos` (
  `subproductos_id`      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subproductos_name`    VARCHAR(40) NOT NULL DEFAULT '',
  `subproductos_comment` TEXT,
  PRIMARY KEY (`subproductos_id`),
  UNIQUE KEY `uk_subproductos_name` (`subproductos_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. cat_categorias
CREATE TABLE `cat_categorias` (
  `tickets_categories_id`    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tickets_categories_name`  VARCHAR(40) NOT NULL DEFAULT '',
  `tickets_categories_order` TINYINT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`tickets_categories_id`),
  UNIQUE KEY `uk_categories_name` (`tickets_categories_name`),
  KEY `idx_categories_order` (`tickets_categories_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. consultoresempresas (liga consultor ↔ empresa)
CREATE TABLE `consultoresempresas` (
  `tickets_consultores_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresas_id`            TINYINT UNSIGNED NOT NULL DEFAULT '0',
  `users_id`               TINYINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`tickets_consultores_id`),
  KEY `idx_ce_empresa` (`empresas_id`),
  KEY `idx_ce_user` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. productosempresas (liga sistema ↔ empresa)
CREATE TABLE `productosempresas` (
  `tickets_empresaprod_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresas_id`            TINYINT UNSIGNED NOT NULL DEFAULT '0',
  `productos_id`           TINYINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`tickets_empresaprod_id`),
  KEY `idx_pe_empresa` (`empresas_id`),
  KEY `idx_pe_producto` (`productos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. productosmodulos (liga sistema ↔ proceso)
CREATE TABLE `productosmodulos` (
  `productomodulo_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `productos_id`      TINYINT UNSIGNED NOT NULL DEFAULT '0',
  `subproductos_id`   TINYINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`productomodulo_id`),
  KEY `idx_pm_producto` (`productos_id`),
  KEY `idx_pm_subproducto` (`subproductos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. tickets_tickets (tabla central)
CREATE TABLE `tickets_tickets` (
  `tickets_id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tickets_idempresa`   SMALLINT UNSIGNED NOT NULL DEFAULT '0',   -- consecutivo POR EMPRESA
  `empresas_id`         TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `productos_id`        TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `subproductos_id`     TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `tickets_username`    VARCHAR(16) NOT NULL DEFAULT '',
  `tickets_subject`     VARCHAR(100) NOT NULL DEFAULT '',
  `tickets_timestamp`   DATETIME DEFAULT NULL,                     -- creación
  `tickets_status`      VARCHAR(15) NOT NULL DEFAULT 'Open',       -- Open / In Progress / Closed
  `tickets_name`        VARCHAR(50) NOT NULL DEFAULT '',
  `tickets_email`       VARCHAR(100) NOT NULL DEFAULT '',
  `prioridades_id`      TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `tickets_categories_id` TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `tickets_escritopor`  VARCHAR(20) NOT NULL DEFAULT 'Client',     -- Client / Consultor
  `tickets_minutos`     INT DEFAULT NULL,                           -- SOLO en respuestas (child > 0)
  `tickets_child`       SMALLINT UNSIGNED NOT NULL DEFAULT '0',     -- 0 = pregunta original, >0 = ID del padre (respuesta)
  `tickets_question`    TEXT NOT NULL,
  `tickets_consempresa` SMALLINT UNSIGNED DEFAULT '0',              -- ¿ID consultor asignado? (pendiente de uso claro)
  `horacierre`          DATETIME DEFAULT NULL,                     -- fecha de cierre
  PRIMARY KEY (`tickets_id`),
  KEY `idx_t_username`    (`tickets_username`),
  KEY `idx_t_prioridad`   (`prioridades_id`),
  KEY `idx_t_categoria`   (`tickets_categories_id`),
  KEY `idx_t_child`       (`tickets_child`),
  KEY `idx_t_status`      (`tickets_status`),
  KEY `idx_t_empresa`     (`empresas_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Notas de lógica de negocio:
-- - tickets_child = 0          → pregunta original del cliente
-- - tickets_child = tickets_id → respuesta del consultor (o cliente si permite)
-- - tickets_minutos            → SOLO se registra en respuestas (child > 0), solo consultores
-- - tickets_idempresa          → consecutivo autoincremental POR EMPRESA (no global)
-- - Usar siempre CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City') para fechas
