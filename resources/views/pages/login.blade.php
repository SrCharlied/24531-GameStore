@extends('layouts.app')

@section('title', 'Iniciar sesión | GameStore')

@section('content')
    <section class="panel" style="max-width: 420px; margin: 40px auto;">
        <h2>Iniciar sesión</h2>
        <p class="lead">Ingresa con tu usuario para acceder al sistema.</p>

        <form method="POST" action="{{ route('login') }}" style="margin-top: 16px;">
            @csrf

            <div class="form-field">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" autofocus required>
                @error('username')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Entrar</button>
            </div>
        </form>

        <p class="lead" style="margin-top: 16px; font-size: 0.85rem;">
            <strong>Usuarios de prueba:</strong><br>
            <code>admin / admin123</code> &mdash; acceso completo<br>
            <code>empleado / empleado123</code> &mdash; solo módulo de compras
        </p>
    </section>
@endsection
