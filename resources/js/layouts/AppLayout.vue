<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo">
          <span class="logo-icon">🏆</span>
          <span class="logo-text" v-if="!sidebarCollapsed">SportManager</span>
        </div>
        <button 
          class="sidebar-toggle"
          @click="toggleSidebar"
          :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
        >
          ☰
        </button>
      </div>
      
      <nav class="sidebar-nav">
        <ul class="nav-list">
          <li>
            <router-link to="/dashboard" class="nav-item">
              <span class="nav-icon">📊</span>
              <span class="nav-text" v-if="!sidebarCollapsed">Dashboard</span>
            </router-link>
          </li>
          
          <li>
            <router-link to="/torneos" class="nav-item">
              <span class="nav-icon">🏆</span>
              <span class="nav-text" v-if="!sidebarCollapsed">Torneos</span>
            </router-link>
          </li>
          
          <li>
            <router-link to="/equipos" class="nav-item">
              <span class="nav-icon">👥</span>
              <span class="nav-text" v-if="!sidebarCollapsed">Equipos</span>
            </router-link>
          </li>
          
          <li>
            <router-link to="/partidos" class="nav-item">
              <span class="nav-icon">⚽</span>
              <span class="nav-text" v-if="!sidebarCollapsed">Partidos</span>
            </router-link>
          </li>
          
          <!-- Solo para administradores -->
          <li v-if="authStore.isAdmin">
            <router-link to="/usuarios" class="nav-item">
              <span class="nav-icon">👤</span>
              <span class="nav-text" v-if="!sidebarCollapsed">Usuarios</span>
            </router-link>
          </li>
        </ul>
        
        <!-- Sección de usuario -->
        <div class="user-section">
          <div class="user-info" v-if="!sidebarCollapsed">
            <div class="user-avatar">
              {{ userInitials }}
            </div>
            <div class="user-details">
              <div class="user-name">{{ authStore.userName }}</div>
              <div class="user-role">{{ userRoleLabel }}</div>
            </div>
          </div>
          
          <button 
            class="logout-btn"
            @click="handleLogout"
            :title="sidebarCollapsed ? 'Cerrar sesión' : ''"
          >
            <span class="logout-icon">🚪</span>
            <span class="logout-text" v-if="!sidebarCollapsed">Cerrar Sesión</span>
          </button>
        </div>
      </nav>
    </aside>
    
    <!-- Contenido principal -->
    <main class="main-content">
      <!-- Header -->
      <header class="main-header">
        <div class="header-left">
          <h1 class="page-title">{{ pageTitle }}</h1>
        </div>
        
        <div class="header-right">
          <div class="user-menu">
            <div class="user-avatar-small">{{ userInitials }}</div>
            <span class="user-name-header">{{ authStore.userName }}</span>
          </div>
        </div>
      </header>
      
      <!-- Contenido de la página -->
      <div class="page-content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'AppLayout',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()
    
    const sidebarCollapsed = ref(false)
    
    // Computadas
    const pageTitle = computed(() => {
      const routeNames = {
        'dashboard': 'Dashboard',
        'torneos.index': 'Torneos',
        'torneos.create': 'Crear Torneo',
        'torneos.show': 'Detalle del Torneo',
        'equipos.index': 'Equipos',
        'equipos.create': 'Crear Equipo',
        'equipos.show': 'Detalle del Equipo',
        'partidos.index': 'Partidos',
        'partidos.show': 'Detalle del Partido',
        'usuarios.index': 'Usuarios',
        'usuarios.show': 'Detalle del Usuario'
      }
      
      return routeNames[route.name] || 'SportManager'
    })
    
    const userInitials = computed(() => {
      if (!authStore.userName) return 'U'
      
      const names = authStore.userName.split(' ')
      if (names.length >= 2) {
        return names[0][0] + names[1][0]
      }
      return names[0][0] || 'U'
    })
    
    const userRoleLabel = computed(() => {
      const roles = {
        'administrador': 'Administrador',
        'jugador': 'Jugador',
        'arbitro': 'Árbitro'
      }
      
      return roles[authStore.userRole] || 'Usuario'
    })
    
    // Métodos
    const toggleSidebar = () => {
      sidebarCollapsed.value = !sidebarCollapsed.value
    }
    
    const handleLogout = async () => {
      if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        await authStore.logout()
        router.push('/login')
      }
    }
    
    return {
      authStore,
      sidebarCollapsed,
      pageTitle,
      userInitials,
      userRoleLabel,
      toggleSidebar,
      handleLogout
    }
  }
}
</script>

<style lang="scss" scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: var(--gray-50);
}

.sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid var(--gray-200);
  display: flex;
  flex-direction: column;
  transition: width var(--transition-normal);
  
  &.sidebar-collapsed {
    width: 80px;
  }
}

.sidebar-header {
  padding: var(--spacing-lg);
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-weight: 700;
  color: var(--primary-color);
  
  .logo-icon {
    font-size: 1.5rem;
  }
  
  .logo-text {
    font-size: 1.25rem;
  }
}

.sidebar-toggle {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--border-radius-sm);
  color: var(--gray-600);
  
  &:hover {
    background: var(--gray-100);
  }
}

.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: var(--spacing-md) 0;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  flex: 1;
  
  li {
    margin-bottom: var(--spacing-xs);
  }
}

.nav-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md) var(--spacing-lg);
  color: var(--gray-700);
  text-decoration: none;
  transition: all var(--transition-fast);
  
  &:hover {
    background: var(--gray-50);
    color: var(--primary-color);
  }
  
  &.router-link-active {
    background: var(--primary-color);
    color: white;
    
    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: var(--primary-dark);
    }
  }
  
  .nav-icon {
    font-size: 1.25rem;
    width: 20px;
    text-align: center;
  }
  
  .nav-text {
    font-weight: 500;
  }
}

.user-section {
  border-top: 1px solid var(--gray-200);
  padding: var(--spacing-md) var(--spacing-lg);
}

.user-info {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-md);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-details {
  flex: 1;
  min-width: 0;
  
  .user-name {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.875rem;
    margin-bottom: 2px;
  }
  
  .user-role {
    font-size: 0.75rem;
    color: var(--gray-500);
  }
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  width: 100%;
  padding: var(--spacing-sm) var(--spacing-md);
  background: none;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  color: var(--gray-700);
  cursor: pointer;
  transition: all var(--transition-fast);
  
  &:hover {
    background: var(--error-color);
    color: white;
    border-color: var(--error-color);
  }
  
  .logout-icon {
    font-size: 1rem;
  }
  
  .logout-text {
    font-size: 0.875rem;
    font-weight: 500;
  }
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.main-header {
  background: white;
  border-bottom: 1px solid var(--gray-200);
  padding: var(--spacing-lg) var(--spacing-xl);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.user-avatar-small {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.75rem;
}

.user-name-header {
  font-weight: 500;
  color: var(--gray-700);
  font-size: 0.875rem;
}

.page-content {
  flex: 1;
  padding: var(--spacing-xl);
  overflow-y: auto;
}

// Responsive
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 1000;
    transform: translateX(-100%);
    
    &:not(.sidebar-collapsed) {
      transform: translateX(0);
    }
  }
  
  .main-content {
    width: 100%;
  }
  
  .page-content {
    padding: var(--spacing-md);
  }
}
</style>