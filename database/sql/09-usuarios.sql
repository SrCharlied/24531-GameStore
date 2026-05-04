-- Fase final - Autenticacion de aplicacion
-- Archivo: 09-usuarios.sql

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS USUARIO (
    ID_Usuario    SERIAL PRIMARY KEY,
    Username      VARCHAR(50)  NOT NULL UNIQUE,
    Password_Hash VARCHAR(255) NOT NULL,
    Rol           VARCHAR(20)  NOT NULL CHECK (Rol IN ('admin', 'empleado'))
);

-- Seed: admin / admin123 ; empleado / empleado123
-- Las contrasenas se hashean con bcrypt (gen_salt('bf')), compatible con password_verify de PHP
INSERT INTO USUARIO (Username, Password_Hash, Rol) VALUES
    ('admin',    crypt('admin123',    gen_salt('bf')), 'admin'),
    ('empleado', crypt('empleado123', gen_salt('bf')), 'empleado')
ON CONFLICT (Username) DO NOTHING;
