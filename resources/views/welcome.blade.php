{{--
  VISTA PRINCIPAL DE LARAVEL PARA VUE 3
  
  Esta vista serve como punto de entrada para la aplicación Vue SPA
  Debe reemplazar el contenido de resources/views/welcome.blade.php
  
  Ubicación: resources/views/welcome.blade.php
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Meta tags básicos --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Título de la aplicación --}}
    <title>{{ config('app.name', 'Sistema Deportivo') }}</title>
    
    {{-- Información para PWA --}}
    <meta name="description" content="Sistema de gestión deportiva integral">
    <meta name="keywords" content="deportes, torneos, equipos, partidos, gestión">
    <meta name="author" content="Sistema Deportivo">
    
    {{-- Open Graph Meta Tags para redes sociales --}}
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="Sistema de gestión deportiva integral">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('') }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    
    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }}">
    <meta name="twitter:description" content="Sistema de gestión deportiva integral">
    <meta name="twitter:image" content="{{ asset('images/twitter-image.jpg') }}">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    {{-- Preconnect para mejorar performance --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    {{-- Google Fonts (opcional) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- CSS de la aplicación --}}
    @vite(['resources/css/app.css'])
    
    {{-- Variables globales de JavaScript --}}
    <script>
        // Configuración global para Vue
        window.AppConfig = {
            name: @json(config('app.name')),
            url: @json(config('app.url')),
            env: @json(config('app.env')),
            locale: @json(app()->getLocale()),
            currency: @json(config('app.currency', 'EUR')),
            timezone: @json(config('app.timezone')),
            version: @json(config('app.version', '1.0.0')),
        };
        
        // URLs para la API
        window.ApiConfig = {
            baseURL: @json(config('app.url') . '/api'),
            timeout: 30000
        };
        
        // Configuración de autenticación
        window.AuthConfig = {
            guards: {
                sanctum: @json(config('sanctum.stateful')),
            }
        };
        
        // Usuario autenticado (si existe)
        @auth
            window.AuthUser = @json(auth()->user());
        @else
            window.AuthUser = null;
        @endauth
    </script>
    
    {{-- Estilos iniciales para evitar flash --}}
    <style>
        /* Loading inicial mientras Vue se carga */
        #app {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        #app.loaded {
            opacity: 1;
        }
        
        /* Loading spinner inicial */
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
        }
        
        .initial-loading .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Ocultar loading cuando Vue está listo */
        #app.loaded + .initial-loading {
            display: none;
        }
    </style>
</head>

<body class="font-sans antialiased">
    {{-- Contenedor principal de Vue --}}
    <div id="app"></div>
    
    {{-- Loading inicial --}}
    <div class="initial-loading">
        <div class="spinner"></div>
    </div>
    
    {{-- Mensajes flash de Laravel (si los hay) --}}
    @if(session()->has('success') || session()->has('error') || session()->has('warning'))
        <script>
            // Pasar mensajes flash a Vue
            window.LaravelFlashMessages = {
                @if(session()->has('success'))
                    success: @json(session('success')),
                @endif
                @if(session()->has('error'))
                    error: @json(session('error')),
                @endif
                @if(session()->has('warning'))
                    warning: @json(session('warning')),
                @endif
            };
        </script>
    @endif
    
    {{-- JavaScript de la aplicación Vue --}}
    @vite(['resources/js/app.js'])
    
    {{-- Script para marcar que la app está cargada --}}
    <script>
        // Marcar la app como cargada una vez que Vue esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar un poco para que Vue se monte
            setTimeout(() => {
                const app = document.getElementById('app');
                if (app) {
                    app.classList.add('loaded');
                }
            }, 100);
        });
        
        // Manejar errores de JavaScript globalmente
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            // Aquí podrías enviar el error a un servicio de logging
        });
        
        // Manejar errores de promesas rechazadas
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled Promise Rejection:', e.reason);
            // Aquí podrías enviar el error a un servicio de logging
        });
    </script>
    
    {{-- Google Analytics (opcional) --}}
    @if(config('app.env') === 'production' && config('services.google_analytics.id'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.google_analytics.id') }}');
        </script>
    @endif
</body>
</html>

{{--
  ============================================================================
  CONFIGURACIONES ADICIONALES NECESARIAS
  ============================================================================
  
  1. Actualizar routes/web.php:
  
  Route::get('/{any}', function () {
      return view('welcome');
  })->where('any', '.*');
  
  2. Configurar .env para desarrollo:
  
  VITE_API_URL=http://localhost:8000/api
  VITE_APP_NAME="${APP_NAME}"
  VITE_APP_URL="${APP_URL}"
  
  3. Actualizar config/cors.php:
  
  'paths' => ['api/*', 'sanctum/csrf-cookie'],
  'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
  'supports_credentials' => true,
  
  4. Configurar config/session.php:
  
  'domain' => env('SESSION_DOMAIN', null),
  'same_site' => 'lax',
  
  ============================================================================
  COMANDOS PARA DESARROLLO
  ============================================================================
  
  # Terminal 1 - Laravel
  php artisan serve
  
  # Terminal 2 - Vite (Vue)
  npm run dev
  
  # Para build de producción
  npm run build
  
  ============================================================================
--}}