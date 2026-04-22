# 24531-GameStore

Base simple del proyecto GameStore usando Laravel, Blade y PostgreSQL.

## Estructura inicial

- `/` dashboard
- `/productos` listado de productos
- `/compras` listado de compras
- `/reportes` pagina de reportes

## Enfoque

- Sin frontend separado
- Sin Livewire
- Sin API routes
- Sin autenticacion
- Sin JavaScript de frontend
- Vistas Blade con datos placeholder

## Configuracion esperada

- PHP 8.3+
- Composer
- PostgreSQL

## Flujo previsto

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan serve
```

Despues conectaremos la base de datos PostgreSQL real y sustituiremos los arrays dummy por consultas SQL explicitas.
