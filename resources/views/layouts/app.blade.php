<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GameStore')</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f1e8;
            --surface: #fffdf8;
            --surface-alt: #efe6d6;
            --border: #d7c7af;
            --text: #2f2418;
            --muted: #6a5847;
            --accent: #8c3d1f;
            --accent-soft: #f1dfcf;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            background: linear-gradient(180deg, #f5f1e8 0%, #ece3d3 100%);
            color: var(--text);
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
            box-shadow: 0 10px 30px rgba(47, 36, 24, 0.08);
        }

        .brand h1 {
            margin: 0;
            font-size: 1.6rem;
        }

        .brand p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nav a {
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface-alt);
            color: var(--muted);
            font-size: 0.95rem;
        }

        .nav a.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff8f2;
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
            padding: 22px;
            box-shadow: 0 10px 30px rgba(47, 36, 24, 0.06);
        }

        .alert {
            background: #fff1e6;
            border: 1px solid #e2b892;
            color: #7a3b1f;
            border-radius: 14px;
            padding: 14px 16px;
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
            font-size: 2rem;
            margin: 8px 0 0;
            color: var(--accent);
        }

        .eyebrow {
            margin: 0;
            color: var(--muted);
            font-size: 0.85rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .tag {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.85rem;
        }

        ul.simple-list {
            margin: 12px 0 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.7;
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

            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">Productos</a>
                <a href="{{ route('compras.index') }}" class="{{ request()->routeIs('compras.*') ? 'active' : '' }}">Compras</a>
                <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">Reportes</a>
            </nav>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
