<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Carteira') — {{ config('app.name', 'Wallet') }}</title>
    <style>
        :root {
            --bg: #0f1419;
            --surface: #1a2332;
            --surface-2: #243044;
            --border: #2d3a4f;
            --text: #e7ecf3;
            --muted: #8b9cb3;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --radius: 12px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(145deg, #0a0e14 0%, #121a26 50%, #0f1419 100%);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.5;
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0 2rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        .logo { font-size: 1.25rem; font-weight: 700; color: var(--text); }
        .logo span { color: var(--accent); }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        .card + .card { margin-top: 1.25rem; }
        .card-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }
        .grid { display: grid; gap: 1.25rem; }
        @media (min-width: 768px) {
            .grid-2 { grid-template-columns: 1fr 1fr; }
            .grid-3 { grid-template-columns: 1fr 1fr 1fr; }
        }
        label { display: block; font-size: 0.875rem; color: var(--muted); margin-bottom: 0.35rem; }
        input, select {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface-2);
            color: var(--text);
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn:active { transform: scale(0.98); }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { background: var(--surface-2); color: var(--text); }
        .btn-danger { background: transparent; color: var(--danger); border: 1px solid var(--danger); font-size: 0.8rem; padding: 0.35rem 0.75rem; }
        .btn-danger:hover { background: rgba(239, 68, 68, 0.15); }
        .btn-block { width: 100%; }
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }
        .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid var(--success); color: #86efac; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; }
        .balance-display {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .balance-display.negative { color: var(--warning); }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); font-weight: 500; font-size: 0.8rem; text-transform: uppercase; }
        .badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-deposit { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .badge-transfer_out { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .badge-transfer_in { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
        .badge-reversal { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .badge-reversed { background: rgba(139, 156, 179, 0.2); color: var(--muted); }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .auth-card { width: 100%; max-width: 420px; }
        .auth-card h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .auth-card p.sub { color: var(--muted); margin-bottom: 1.5rem; font-size: 0.9rem; }
        .form-footer { margin-top: 1rem; text-align: center; font-size: 0.875rem; color: var(--muted); }
        .user-chip { font-size: 0.875rem; color: var(--muted); }
        .empty-state { text-align: center; padding: 2rem; color: var(--muted); }
    </style>
</head>
<body>
    @hasSection('auth')
        <div class="auth-wrapper">
            <div class="auth-card card">
                @include('partials.alerts')
                @yield('auth')
            </div>
        </div>
    @else
        <div class="container">
            <nav class="navbar">
                <a href="{{ route('dashboard') }}" class="logo">Carteira<span>Pay</span></a>
                @auth
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <span class="user-chip">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn btn-ghost">Sair</button>
                        </form>
                    </div>
                @endauth
            </nav>
            @include('partials.alerts')
            @yield('content')
        </div>
    @endif
</body>
</html>
