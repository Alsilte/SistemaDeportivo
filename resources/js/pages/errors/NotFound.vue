<template>
    <div class="not-found-page">
      <!-- Contenedor principal con diseño centrado -->
      <div class="not-found-container">
        <!-- Icono de error grande -->
        <div class="error-icon">
          <div class="icon-circle">
            <span class="icon-text">404</span>
          </div>
          <div class="error-animation">
            <div class="ball"></div>
            <div class="goal-post">
              <div class="post left"></div>
              <div class="post right"></div>
              <div class="crossbar"></div>
            </div>
          </div>
        </div>
  
        <!-- Contenido del error -->
        <div class="error-content">
          <h1 class="error-title">¡Página no encontrada!</h1>
          <p class="error-subtitle">
            Parece que esta página se fue al banquillo. 
            La URL que buscas no existe o ha sido movida.
          </p>
          
          <!-- Información adicional según el contexto -->
          <div class="error-details">
            <div class="detail-item">
              <span class="detail-icon">🏆</span>
              <span class="detail-text">
                Si buscabas un <strong>torneo</strong>, ve a la sección de torneos
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-icon">👥</span>
              <span class="detail-text">
                Si buscabas un <strong>equipo</strong>, revisa la lista de equipos
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-icon">⚽</span>
              <span class="detail-text">
                Si buscabas un <strong>partido</strong>, consulta el calendario
              </span>
            </div>
          </div>
  
          <!-- Sugerencias de navegación -->
          <div class="suggestions">
            <h3>¿Qué puedes hacer?</h3>
            <ul class="suggestions-list">
              <li>Verificar que la URL esté escrita correctamente</li>
              <li>Usar el menú de navegación para encontrar lo que buscas</li>
              <li>Regresar a la página anterior con el botón "Atrás"</li>
              <li>Ir al dashboard principal para empezar de nuevo</li>
            </ul>
          </div>
        </div>
  
        <!-- Botones de acción -->
        <div class="error-actions">
          <button 
            @click="goBack" 
            class="btn btn-outline"
            :disabled="!canGoBack"
          >
            <span class="btn-icon">←</span>
            Volver Atrás
          </button>
          
          <router-link to="/dashboard" class="btn btn-primary">
            <span class="btn-icon">🏠</span>
            Ir al Dashboard
          </router-link>
          
          <button @click="goToLastSection" class="btn btn-secondary">
            <span class="btn-icon">📋</span>
            {{ lastSectionLabel }}
          </button>
        </div>
  
        <!-- Enlaces rápidos -->
        <div class="quick-links">
          <h4>Enlaces rápidos</h4>
          <div class="links-grid">
            <router-link to="/torneos" class="quick-link">
              <span class="link-icon">🏆</span>
              <span class="link-text">Torneos</span>
            </router-link>
            
            <router-link to="/equipos" class="quick-link">
              <span class="link-icon">👥</span>
              <span class="link-text">Equipos</span>
            </router-link>
            
            <router-link to="/partidos" class="quick-link">
              <span class="link-icon">⚽</span>
              <span class="link-text">Partidos</span>
            </router-link>
            
            <router-link 
              v-if="authStore.isAdmin" 
              to="/usuarios" 
              class="quick-link"
            >
              <span class="link-icon">👤</span>
              <span class="link-text">Usuarios</span>
            </router-link>
          </div>
        </div>
  
        <!-- Información de contacto/ayuda -->
        <div class="help-section">
          <p class="help-text">
            ¿Sigues teniendo problemas? 
            <button @click="reportProblem" class="help-link">
              Reportar problema
            </button>
          </p>
        </div>
  
        <!-- Debug info (solo en desarrollo) -->
        <div v-if="showDebugInfo" class="debug-info">
          <h5>Información de debug:</h5>
          <div class="debug-details">
            <p><strong>URL solicitada:</strong> {{ $route.fullPath }}</p>
            <p><strong>Usuario:</strong> {{ authStore.userName || 'No autenticado' }}</p>
            <p><strong>Rol:</strong> {{ authStore.userRole || 'N/A' }}</p>
            <p><strong>Última página:</strong> {{ lastVisitedPage || 'N/A' }}</p>
            <p><strong>Timestamp:</strong> {{ new Date().toISOString() }}</p>
          </div>
        </div>
      </div>
  
      <!-- Modal de reporte de problemas -->
      <div v-if="showReportModal" class="modal-overlay" @click="closeReportModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Reportar Problema</h3>
            <button @click="closeReportModal" class="modal-close">×</button>
          </div>
          
          <div class="modal-body">
            <p>¿Qué estabas intentando hacer cuando encontraste este error?</p>
            
            <textarea
              v-model="problemDescription"
              placeholder="Describe brevemente qué estabas buscando o haciendo..."
              class="problem-textarea"
              rows="4"
            ></textarea>
            
            <div class="problem-details">
              <p><strong>URL problemática:</strong> {{ $route.fullPath }}</p>
              <p><strong>Navegador:</strong> {{ browserInfo }}</p>
            </div>
          </div>
          
          <div class="modal-footer">
            <button @click="closeReportModal" class="btn btn-outline">
              Cancelar
            </button>
            <button @click="submitReport" class="btn btn-primary" :disabled="!problemDescription.trim()">
              Enviar Reporte
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, onMounted } from 'vue'
  import { useRouter, useRoute } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'NotFound',
    setup() {
      const router = useRouter()
      const route = useRoute()
      const authStore = useAuthStore()
      
      // Estado reactivo
      const showReportModal = ref(false)
      const problemDescription = ref('')
      const lastVisitedPage = ref(null)
      const showDebugInfo = ref(false)
      
      // Computadas
      const canGoBack = computed(() => {
        return window.history.length > 1
      })
      
      const lastSectionLabel = computed(() => {
        const path = lastVisitedPage.value || '/dashboard'
        
        if (path.includes('/torneos')) return 'Ir a Torneos'
        if (path.includes('/equipos')) return 'Ir a Equipos'
        if (path.includes('/partidos')) return 'Ir a Partidos'
        if (path.includes('/usuarios')) return 'Ir a Usuarios'
        
        return 'Ir a Inicio'
      })
      
      const browserInfo = computed(() => {
        return `${navigator.userAgent.split(' ')[0]} ${navigator.platform}`
      })
      
      // Métodos
      const goBack = () => {
        if (canGoBack.value) {
          router.go(-1)
        } else {
          router.push('/dashboard')
        }
      }
      
      const goToLastSection = () => {
        const targetPath = getLastSectionPath()
        router.push(targetPath)
      }
      
      const getLastSectionPath = () => {
        const path = lastVisitedPage.value || '/dashboard'
        
        if (path.includes('/torneos')) return '/torneos'
        if (path.includes('/equipos')) return '/equipos'
        if (path.includes('/partidos')) return '/partidos'
        if (path.includes('/usuarios') && authStore.isAdmin) return '/usuarios'
        
        return '/dashboard'
      }
      
      const reportProblem = () => {
        showReportModal.value = true
      }
      
      const closeReportModal = () => {
        showReportModal.value = false
        problemDescription.value = ''
      }
      
      const submitReport = async () => {
        try {
          // Aquí normalmente enviarías el reporte a tu API
          console.log('Reporte de problema:', {
            url: route.fullPath,
            description: problemDescription.value,
            user: authStore.userName,
            timestamp: new Date().toISOString(),
            browser: browserInfo.value
          })
          
          // Simular envío exitoso
          alert('¡Gracias! Tu reporte ha sido enviado. Revisaremos el problema pronto.')
          closeReportModal()
        } catch (error) {
          console.error('Error enviando reporte:', error)
          alert('Hubo un error al enviar el reporte. Por favor, intenta más tarde.')
        }
      }
      
      const initializeDebugMode = () => {
        // Mostrar debug info solo en desarrollo o si se presiona una combinación de teclas
        const isDev = process.env.NODE_ENV === 'development'
        showDebugInfo.value = isDev
        
        // Combinación Ctrl+Alt+D para mostrar debug info
        const handleKeypress = (event) => {
          if (event.ctrlKey && event.altKey && event.key === 'd') {
            showDebugInfo.value = !showDebugInfo.value
          }
        }
        
        document.addEventListener('keydown', handleKeypress)
        
        // Cleanup
        return () => {
          document.removeEventListener('keydown', handleKeypress)
        }
      }
      
      const trackLastVisitedPage = () => {
        // Intentar obtener la última página visitada del localStorage
        const storedPage = localStorage.getItem('lastVisitedPage')
        if (storedPage && storedPage !== route.fullPath) {
          lastVisitedPage.value = storedPage
        }
      }
      
      const logNotFoundEvent = () => {
        // Log para analytics o debugging
        console.warn('404 Error:', {
          requestedUrl: route.fullPath,
          userAgent: navigator.userAgent,
          timestamp: new Date().toISOString(),
          user: authStore.userName || 'Anonymous',
          referrer: document.referrer
        })
        
        // Aquí podrías enviar esta información a un servicio de analytics
      }
      
      // Ciclo de vida
      onMounted(() => {
        const cleanup = initializeDebugMode()
        trackLastVisitedPage()
        logNotFoundEvent()
        
        // Cleanup cuando el componente se desmonte
        return cleanup
      })
      
      return {
        // Estado
        showReportModal,
        problemDescription,
        lastVisitedPage,
        showDebugInfo,
        
        // Stores
        authStore,
        
        // Computadas
        canGoBack,
        lastSectionLabel,
        browserInfo,
        
        // Métodos
        goBack,
        goToLastSection,
        reportProblem,
        closeReportModal,
        submitReport
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .not-found-page {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-lg);
  }
  
  .not-found-container {
    max-width: 800px;
    width: 100%;
    text-align: center;
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    padding: var(--spacing-2xl);
    margin: var(--spacing-lg);
  }
  
  .error-icon {
    position: relative;
    margin-bottom: var(--spacing-xl);
    
    .icon-circle {
      width: 150px;
      height: 150px;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto var(--spacing-lg);
      position: relative;
      overflow: hidden;
      
      &::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        bottom: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
      }
      
      .icon-text {
        font-size: 3rem;
        font-weight: 700;
        color: white;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      }
    }
    
    .error-animation {
      position: relative;
      height: 60px;
      margin: 0 auto;
      
      .goal-post {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        
        .post {
          width: 4px;
          height: 40px;
          background: var(--gray-400);
          position: absolute;
          bottom: 0;
          
          &.left { left: -30px; }
          &.right { right: -30px; }
        }
        
        .crossbar {
          width: 60px;
          height: 4px;
          background: var(--gray-400);
          position: absolute;
          top: 0;
          left: -30px;
        }
      }
      
      .ball {
        width: 20px;
        height: 20px;
        background: var(--primary-color);
        border-radius: 50%;
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        animation: bounce 2s infinite ease-in-out;
        
        &::after {
          content: '⚽';
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 16px;
        }
      }
    }
  }
  
  @keyframes bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(-20px); }
  }
  
  .error-content {
    margin-bottom: var(--spacing-xl);
    
    .error-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--gray-800);
      margin-bottom: var(--spacing-md);
    }
    
    .error-subtitle {
      font-size: 1.25rem;
      color: var(--gray-600);
      margin-bottom: var(--spacing-xl);
      line-height: 1.6;
    }
  }
  
  .error-details {
    display: grid;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
    
    .detail-item {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--spacing-sm);
      padding: var(--spacing-md);
      background: var(--gray-50);
      border-radius: var(--border-radius);
      
      .detail-icon {
        font-size: 1.5rem;
      }
      
      .detail-text {
        color: var(--gray-700);
        font-size: 0.875rem;
      }
    }
  }
  
  .suggestions {
    text-align: left;
    background: var(--info-color);
    background: rgba(14, 165, 233, 0.1);
    padding: var(--spacing-lg);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-xl);
    
    h3 {
      color: var(--info-color);
      margin-bottom: var(--spacing-md);
      text-align: center;
    }
    
    .suggestions-list {
      list-style: none;
      padding: 0;
      margin: 0;
      
      li {
        padding: var(--spacing-sm) 0;
        position: relative;
        padding-left: var(--spacing-lg);
        
        &::before {
          content: '💡';
          position: absolute;
          left: 0;
          top: var(--spacing-sm);
        }
      }
    }
  }
  
  .error-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
    
    .btn {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      padding: var(--spacing-md) var(--spacing-lg);
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 500;
      transition: all var(--transition-fast);
      border: none;
      cursor: pointer;
      
      &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
      }
      
      .btn-icon {
        font-size: 1.125rem;
      }
      
      &.btn-primary {
        background: var(--primary-color);
        color: white;
        
        &:hover:not(:disabled) {
          background: var(--primary-dark);
          transform: translateY(-2px);
        }
      }
      
      &.btn-secondary {
        background: var(--secondary-color);
        color: white;
        
        &:hover:not(:disabled) {
          background: var(--secondary-dark);
          transform: translateY(-2px);
        }
      }
      
      &.btn-outline {
        background: transparent;
        color: var(--gray-700);
        border: 2px solid var(--gray-300);
        
        &:hover:not(:disabled) {
          border-color: var(--primary-color);
          color: var(--primary-color);
          transform: translateY(-2px);
        }
      }
    }
  }
  
  .quick-links {
    margin-bottom: var(--spacing-xl);
    
    h4 {
      color: var(--gray-700);
      margin-bottom: var(--spacing-md);
    }
    
    .links-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: var(--spacing-md);
      max-width: 600px;
      margin: 0 auto;
    }
    
    .quick-link {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--spacing-sm);
      padding: var(--spacing-lg);
      background: white;
      border: 2px solid var(--gray-200);
      border-radius: var(--border-radius-lg);
      text-decoration: none;
      color: var(--gray-700);
      transition: all var(--transition-fast);
      
      &:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
      }
      
      .link-icon {
        font-size: 2rem;
      }
      
      .link-text {
        font-weight: 500;
        font-size: 0.875rem;
      }
    }
  }
  
  .help-section {
    border-top: 1px solid var(--gray-200);
    padding-top: var(--spacing-lg);
    
    .help-text {
      color: var(--gray-600);
      font-size: 0.875rem;
    }
    
    .help-link {
      color: var(--primary-color);
      background: none;
      border: none;
      text-decoration: underline;
      cursor: pointer;
      font-size: inherit;
      
      &:hover {
        color: var(--primary-dark);
      }
    }
  }
  
  .debug-info {
    margin-top: var(--spacing-lg);
    padding: var(--spacing-lg);
    background: var(--gray-800);
    color: var(--gray-100);
    border-radius: var(--border-radius);
    text-align: left;
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    
    h5 {
      color: var(--warning-color);
      margin-bottom: var(--spacing-md);
    }
    
    .debug-details p {
      margin: var(--spacing-xs) 0;
    }
  }
  
  // Modal
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: var(--spacing-lg);
  }
  
  .modal-content {
    background: white;
    border-radius: var(--border-radius-lg);
    max-width: 500px;
    width: 100%;
    max-height: 80vh;
    overflow-y: auto;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
    
    h3 {
      margin: 0;
      color: var(--gray-800);
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--gray-500);
      
      &:hover {
        color: var(--gray-700);
      }
    }
  }
  
  .modal-body {
    padding: var(--spacing-lg);
    
    .problem-textarea {
      width: 100%;
      padding: var(--spacing-md);
      border: 1px solid var(--gray-300);
      border-radius: var(--border-radius);
      font-family: inherit;
      resize: vertical;
      margin: var(--spacing-md) 0;
      
      &:focus {
        outline: none;
        border-color: var(--primary-color);
      }
    }
    
    .problem-details {
      background: var(--gray-50);
      padding: var(--spacing-md);
      border-radius: var(--border-radius);
      font-size: 0.75rem;
      color: var(--gray-600);
      
      p {
        margin: var(--spacing-xs) 0;
      }
    }
  }
  
  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    border-top: 1px solid var(--gray-200);
  }
  
  // Responsive
  @media (max-width: 768px) {
    .not-found-container {
      padding: var(--spacing-lg);
      margin: var(--spacing-sm);
    }
    
    .error-content .error-title {
      font-size: 2rem;
    }
    
    .error-actions {
      flex-direction: column;
      align-items: stretch;
      
      .btn {
        justify-content: center;
      }
    }
    
    .links-grid {
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .modal-overlay {
      padding: var(--spacing-sm);
    }
  }
  
  @media (max-width: 480px) {
    .error-icon .icon-circle {
      width: 120px;
      height: 120px;
      
      .icon-text {
        font-size: 2rem;
      }
    }
    
    .error-content .error-title {
      font-size: 1.75rem;
    }
    
    .error-content .error-subtitle {
      font-size: 1rem;
    }
  }
  </style>