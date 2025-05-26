<template>
    <div id="app">
      <!-- Indicador de carga global -->
      <div v-if="appStore.isLoading" class="global-loader">
        <div class="loader-content">
          <div class="spinner"></div>
          <p>Cargando...</p>
        </div>
      </div>
      
      <!-- Contenido principal de la aplicación -->
      <router-view />
      
      <!-- Sistema de notificaciones -->
      <NotificationSystem />
      
      <!-- Indicador de conectividad -->
      <div v-if="!appStore.isOnline" class="offline-banner">
        <span>⚠️ Sin conexión a internet</span>
      </div>
    </div>
  </template>
  
  <script>
  import { useAuthStore } from './stores/auth'
//   import { useAppStore } from './stores/app'
  import { onMounted } from 'vue'
//   import NotificationSystem from './components/NotificationSystem.vue'
  
  export default {
    name: 'App',
    components: {
      NotificationSystem
    },
    setup() {
      const authStore = useAuthStore()
      const appStore = useAppStore()
      
      // Inicializar la aplicación
      onMounted(() => {
        // Inicializar el store de app
        appStore.initialize()
        
        // Verificar si hay token guardado al iniciar la app
        const token = localStorage.getItem('auth_token')
        const userData = localStorage.getItem('user_data')
        
        if (token && userData) {
          try {
            authStore.setToken(token)
            authStore.setUser(JSON.parse(userData))
            // Sincronizar con app store
            appStore.setCurrentUser(JSON.parse(userData))
          } catch (error) {
            console.error('Error cargando datos de usuario:', error)
            // Limpiar datos corruptos
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user_data')
          }
        }
      })
      
      return {
        authStore,
        appStore
      }
    }
  }
  </script>
  
  <style lang="scss">
  // Estilos globales de la aplicación
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  
  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #374151;
    background-color: #f9fafb;
    overflow-x: hidden;
  }
  
  #app {
    min-height: 100vh;
    position: relative;
  }
  
  // Loader global
  .global-loader {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
  }
  
  .loader-content {
    text-align: center;
    
    .spinner {
      width: 50px;
      height: 50px;
      border: 4px solid #e5e7eb;
      border-top: 4px solid #3b82f6;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }
    
    p {
      color: #6b7280;
      font-weight: 500;
    }
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  
  // Banner de offline
  .offline-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: #f59e0b;
    color: white;
    text-align: center;
    padding: 0.5rem;
    font-weight: 600;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  // Variables CSS personalizadas
  :root {
    --primary-color: #3b82f6;
    --primary-dark: #1d4ed8;
    --secondary-color: #10b981;
    --error-color: #ef4444;
    --warning-color: #f59e0b;
    --success-color: #10b981;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
  }
  
  // Utilidades globales
  .text-center { text-align: center; }
  .text-left { text-align: left; }
  .text-right { text-align: right; }
  
  .font-bold { font-weight: 700; }
  .font-semibold { font-weight: 600; }
  .font-medium { font-weight: 500; }
  
  .text-primary { color: var(--primary-color); }
  .text-secondary { color: var(--secondary-color); }
  .text-error { color: var(--error-color); }
  .text-success { color: var(--success-color); }
  .text-warning { color: var(--warning-color); }
  
  .bg-primary { background-color: var(--primary-color); }
  .bg-secondary { background-color: var(--secondary-color); }
  .bg-white { background-color: white; }
  
  .rounded { border-radius: 0.375rem; }
  .rounded-lg { border-radius: 0.5rem; }
  .rounded-full { border-radius: 9999px; }
  
  .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
  .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
  
  // Transiciones suaves
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }
  
  // Responsive
  @media (max-width: 768px) {
    .offline-banner {
      font-size: 0.875rem;
      padding: 0.375rem;
    }
    
    .global-loader .loader-content {
      .spinner {
        width: 40px;
        height: 40px;
      }
      
      p {
        font-size: 0.875rem;
      }
    }
  }
  </style>