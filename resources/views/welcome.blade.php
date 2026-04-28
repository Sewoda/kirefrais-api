<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | API Gateway</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #10b981; /* Fresh Green */
            --primary-dark: #059669;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #34d399;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.05) 0%, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 2rem;
            z-index: 10;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-container {
            margin-bottom: 2rem;
            display: inline-block;
            position: relative;
        }

        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            object-fit: cover;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            border: 2px solid var(--primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--accent);
            border-radius: 50%;
            margin-right: 8px;
            position: relative;
        }

        .status-dot::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--accent);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(3); opacity: 0; }
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: var(--text-muted);
            font-size: 1.125rem;
            line-height: 1.75;
            margin-bottom: 2.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 3rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .footer span {
            color: var(--primary);
            font-weight: 600;
        }

        @media (max-width: 640px) {
            .card {
                padding: 2rem 1.5rem;
            }
            h1 {
                font-size: 1.75rem;
            }
            .actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo-container">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Kirefrais Logo">
            </div>
            
            <div>
                <div class="status-badge">
                    <span class="status-dot"></span>
                    API Gateway Operational
                </div>
            </div>

            <h1>{{ config('app.name') }} API</h1>
            <p>Le moteur logistique derrière Kirefrais, assurant la fraîcheur de vos kits repas directement à votre porte.</p>

            <div class="actions">
                <a href="/api/kits" class="btn btn-primary">
                    Explorer les Produits
                </a>
                <a href="https://github.com/Sewoda/kirefrais" target="_blank" class="btn btn-outline">
                    Documentation
                </a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <span>Kirefrais</span>. Tous droits réservés.
        </div>
    </div>
</body>
</html>
