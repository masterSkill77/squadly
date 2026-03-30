<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Squadly</title>
    <link rel="icon" type="image/svg+xml" href="/squadly-icon-square.svg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f9fafb;
            color: #374151;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 28rem;
        }
        .code {
            font-size: 6rem;
            font-weight: 700;
            color: #059669;
            line-height: 1;
        }
        .title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-top: 1rem;
        }
        .description {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.75rem;
            line-height: 1.6;
        }
        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-primary {
            background-color: #059669;
            color: #fff;
        }
        .btn-primary:hover { background-color: #047857; }
        .btn-secondary {
            background-color: #fff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .btn-secondary:hover { background-color: #f9fafb; }
        .logo {
            margin-top: 3rem;
            opacity: 0.3;
        }
        .logo img { height: 1.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">@yield('code')</div>
        <h1 class="title">@yield('title')</h1>
        <p class="description">@yield('message')</p>
        <div class="actions">
            <a href="/" class="btn btn-primary">Retour à l'accueil</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Page précédente</a>
        </div>
        <div class="logo">
            <img src="/squadly-logo-light.svg" alt="Squadly" />
        </div>
    </div>
</body>
</html>
