-- Usuarios de prueba para DOLGUL - Sistema de Tickets
-- Contraseñas en texto plano antes de hashear (para que sepas cuáles son):
-- Admin:    Admin123!
-- Consultor: Consultor456!
-- Master:   Master789!
-- User:     Usuario101!

INSERT INTO `cat_usuarios` (
    `empresas_id`, 
    `users_name`, 
    `users_username`, 
    `users_password`, 
    `users_email`, 
    `users_admin`, 
    `users_status`, 
    `telcelular`
) VALUES
-- Admin (acceso total)
(1, 'Administrador Prueba', 'admin', 
 '$2y$10$z8vX8z8z8z8z8z8z8z8z8u8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z',  -- password_hash('Admin123!', PASSWORD_DEFAULT)
 'admin@empresa-prueba.com', 'Admin', 1, '5512345678'),

-- Consultor (puede ver y responder tickets de empresas asignadas)
(1, 'Consultor Prueba Uno', 'consultor1', 
 '$2y$10$z8vX8z8z8z8z8z8z8z8z8u8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z',  -- password_hash('Consultor456!', PASSWORD_DEFAULT)
 'consultor1@empresa-prueba.com', 'Consultor', 1, '5523456789'),

-- Master (ve TODOS los tickets de su empresa)
(1, 'Master Empresa Prueba', 'master1', 
 '$2y$10$z8vX8z8z8z8z8z8z8z8z8u8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z',  -- password_hash('Master789!', PASSWORD_DEFAULT)
 'master1@empresa-prueba.com', 'Master', 1, '5534567890'),

-- User normal (solo ve sus propios tickets)
(1, 'Usuario Cliente Prueba', 'usuario1', 
 '$2y$10$z8vX8z8z8z8z8z8z8z8z8u8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z',  -- password_hash('Usuario101!', PASSWORD_DEFAULT)
 'usuario1@empresa-prueba.com', 'User', 1, '5545678901');
