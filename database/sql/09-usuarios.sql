-- Fase 2 - Roles de aplicacion y usuarios de prueba
-- Archivo: 09-usuarios.sql

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS USUARIO (
    ID_Usuario    SERIAL PRIMARY KEY,
    Username      VARCHAR(50)  NOT NULL UNIQUE,
    Password_Hash VARCHAR(255) NOT NULL,
    Rol           VARCHAR(20)  NOT NULL
);

-- Si se aplica este script sobre una base previa de Proyecto 2, quitar el usuario/rol anterior
-- antes de activar el nuevo CHECK de roles.
DELETE FROM USUARIO WHERE Username = 'empleado';

-- Asegurar los 5 roles incluso si el script se aplica sobre una base previa de Proyecto 2.
ALTER TABLE USUARIO DROP CONSTRAINT IF EXISTS usuario_rol_check;
ALTER TABLE USUARIO ADD CONSTRAINT usuario_rol_check
    CHECK (Rol IN ('admin', 'gerente', 'vendedor', 'bodega', 'auditor'));

-- Seed de usuarios por rol de negocio
-- Las contrasenas se hashean con bcrypt (gen_salt('bf')), compatible con password_verify de PHP
INSERT INTO USUARIO (Username, Password_Hash, Rol) VALUES
    ('admin',    crypt('admin123',    gen_salt('bf')), 'admin'),
    ('gerente',  crypt('gerente123',  gen_salt('bf')), 'gerente'),
    ('vendedor', crypt('vendedor123', gen_salt('bf')), 'vendedor'),
    ('bodega',   crypt('bodega123',   gen_salt('bf')), 'bodega'),
    ('auditor',  crypt('auditor123',  gen_salt('bf')), 'auditor')
ON CONFLICT (Username) DO UPDATE SET
    Password_Hash = EXCLUDED.Password_Hash,
    Rol = EXCLUDED.Rol;
