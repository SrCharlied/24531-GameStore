<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GameStore')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700&family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
            --bg: #07071a;
            --surface: #131334;
            --surface-alt: #1c1c47;
            --border: #3a3a78;
            --text: #e8e8ff;
            --muted: #9d9dd1;
            --accent: #a78bfa;
            --accent-strong: #8b5cf6;
            --accent-soft: #2c1e5e;
            --cyan: #22d3ee;
            --danger: #f87171;
            --success: #4ade80;
            --font-display: 'Orbitron', 'Audiowide', sans-serif;
            --font-body: 'Exo 2', system-ui, -apple-system, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--font-body);
            font-weight: 400;
            color: var(--text);
            min-height: 100vh;
            background: linear-gradient(160deg, #0e0e2a 0%, #07071a 60%, #03030d 100%);
        }

        h1, h2, h3 {
            font-family: var(--font-display);
            letter-spacing: 0.04em;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px;
        }

        .topbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .brand h1 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            background: linear-gradient(90deg, var(--accent) 0%, var(--cyan) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(139, 92, 246, 0.4);
        }

        .brand p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .nav a {
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface-alt);
            color: var(--muted);
            font-size: 0.85rem;
            font-family: var(--font-display);
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            transition: border-color 0.15s, color 0.15s;
        }

        .nav a:hover {
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 14px rgba(139, 92, 246, 0.3);
        }

        .nav a.active {
            background: linear-gradient(135deg, var(--accent-strong), var(--accent));
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 0 18px rgba(139, 92, 246, 0.55);
        }

        .content {
            margin-top: 24px;
            display: grid;
            gap: 18px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .alert {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid var(--danger);
            color: #fca5a5;
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 0 16px rgba(248, 113, 113, 0.15);
        }

        .panel h2,
        .panel h3 {
            margin-top: 0;
        }

        .lead {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid.cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .grid.cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .card {
            background: var(--surface-alt);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
        }

        .metric {
            font-family: var(--font-display);
            font-weight: 900;
            font-size: 2.2rem;
            margin: 8px 0 0;
            background: linear-gradient(90deg, var(--accent) 0%, var(--cyan) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 24px rgba(139, 92, 246, 0.35);
        }

        .eyebrow {
            margin: 0;
            color: var(--muted);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-family: var(--font-display);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(58, 58, 120, 0.5);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--cyan);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-family: var(--font-display);
            font-weight: 500;
        }

        tbody tr:hover {
            background: rgba(139, 92, 246, 0.05);
        }

        .tag {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.78rem;
            border: 1px solid rgba(167, 139, 250, 0.3);
            letter-spacing: 0.03em;
        }

        ul.simple-list {
            margin: 12px 0 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.7;
        }

        .form-field {
            margin-bottom: 16px;
        }

        .form-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text);
        }

        .form-field input[type="text"],
        .form-field input[type="number"],
        .form-field input[type="password"],
        .form-field textarea,
        .form-field select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(7, 7, 26, 0.6);
            color: var(--text);
            font-family: var(--font-body);
            font-size: 0.95rem;
            transition: all 0.15s ease;
        }

        .form-field input::placeholder,
        .form-field textarea::placeholder {
            color: rgba(157, 157, 209, 0.5);
        }

        .form-field input:focus,
        .form-field textarea:focus,
        .form-field select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.18);
        }

        .form-error {
            margin-top: 6px;
            color: var(--danger);
            font-size: 0.85rem;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface-alt);
            color: var(--text);
            cursor: pointer;
            font-family: var(--font-display);
            font-weight: 500;
            font-size: 0.82rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-decoration: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .btn:hover {
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(139, 92, 246, 0.35);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-strong), var(--accent));
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 0 16px rgba(139, 92, 246, 0.45);
        }

        .btn-primary:hover {
            box-shadow: 0 0 24px rgba(139, 92, 246, 0.7);
        }

        .btn-danger {
            background: linear-gradient(135deg, #b91c1c, #dc2626);
            border-color: #dc2626;
            color: #fff;
            box-shadow: 0 0 14px rgba(220, 38, 38, 0.35);
        }

        .btn-danger:hover {
            box-shadow: 0 0 22px rgba(220, 38, 38, 0.6);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.72rem;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 16px;
        }

        .checkbox-grid label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            margin: 0;
        }

        .alert-success {
            background: rgba(74, 222, 128, 0.12);
            border-color: var(--success);
            color: #86efac;
            box-shadow: 0 0 16px rgba(74, 222, 128, 0.15);
        }

        .row-actions {
            display: flex;
            gap: 8px;
        }

        .inline-form {
            display: inline;
        }

        .compra-line {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
            margin-bottom: 10px;
        }

        .compra-line .form-field {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .compra-line {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .grid.cols-4,
            .grid.cols-3 {
                grid-template-columns: 1fr;
            }

            .wrapper {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <header class="topbar">
            <div class="brand">
                <h1>GameStore</h1>
                <p>Sistema simple de inventario, compras y reportes en Laravel.</p>
            </div>

            @php $rol = session('user.rol'); @endphp

            <nav class="nav">
                @if ($rol === 'admin')
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">Productos</a>
                @endif
                @if ($rol)
                    <a href="{{ route('compras.index') }}" class="{{ request()->routeIs('compras.*') ? 'active' : '' }}">Compras</a>
                @endif
                @if ($rol === 'admin')
                    <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">Reportes</a>
                @endif

                @if ($rol)
                    <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border: 1px solid var(--cyan); border-radius: 999px; background: rgba(34, 211, 238, 0.10); color: var(--cyan); font-family: var(--font-display); font-size: 0.78rem; letter-spacing: 0.06em; text-transform: uppercase; box-shadow: 0 0 12px rgba(34, 211, 238, 0.25);">
                        {{ session('user.username') }} · {{ $rol }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn btn-sm">Cerrar sesión</button>
                    </form>
                @endif
            </nav>
        </header>

        <main class="content">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
