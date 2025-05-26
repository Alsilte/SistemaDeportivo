<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Sistema Deportivo') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Variables para JavaScript -->
    <script>
        window.AppConfig = {
            name: @json(config('app.name')),
            url: @json(config('app.url')),
            env: @json(config('app.env')),
            locale: @json(app()->getLocale()),
        };
        
        window.ApiConfig = {
            baseURL: @json(config('app.url') . '/api'),
            timeout: 30000
        };
        
        @auth
            window.AuthUser = @json(auth()->user());
        @else
            window.AuthUser = null;
        @endauth
    </script>
    
    <style>
        /* Loading inicial */
        #app {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        #app.loaded {
            opacity: 1;
        }
        
        .initial-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            font-family: 'Inter', sans-serif;
        }
        
        .loading-content {
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        #app.loaded + .initial-loading {
            display: none;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <!-- Contenedor de Vue -->
    <div id="app"></div>
    
    <!-- Loading inicial -->
    <div class="initial-loading">
        <div class="loading-content">
            <div class="spinner"></div>
            <h2 style="color: #374151; margin: 0;">Cargando Sistema Deportivo...</h2>
            <p style="color: #6b7280; margin: 10px 0 0;">Iniciando aplicación Vue...</p>
        </div>
    </div>
    
    <!-- Script para marcar como cargado -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const app = document.getElementById('app');
                if (app) {
                    app.classList.add('loaded');
                }
            }, 100);
        });
        
        // Manejo de errores globales
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
        });
        
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled Promise Rejection:', e.reason);
        });
    </script>
</body>
</html>