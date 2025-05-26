/**
 * ROUTER PRINCIPAL
 * 
 * Archivo: resources/js/router/index.js
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// ============================================================================
// LAYOUTS
// ============================================================================
import AuthLayout from '@/layouts/AuthLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ============================================================================
// PÁGINAS DE AUTENTICACIÓN
// ============================================================================
import Login from '@/pages/auth/Login.vue'
import Register from '@/pages/auth/Register.vue'

// ============================================================================
// PÁGINAS PRINCIPALES
// ============================================================================
import Dashboard from '@/pages/dashboard/Dashboard.vue'

// Torneos
import TorneoIndex from '@/pages/torneos/TorneoIndex.vue'
import TorneoShow from '@/pages/torneos/TorneoShow.vue'
import TorneoCreate from '@/pages/torneos/TorneoCreate.vue'

// Equipos
import EquipoIndex from '@/pages/equipos/EquipoIndex.vue'
import EquipoShow from '@/pages/equipos/EquipoShow.vue'
import EquipoCreate from '@/pages/equipos/EquipoCreate.vue'

// Partidos
import PartidoIndex from '@/pages/partidos/PartidoIndex.vue'
import PartidoShow from '@/pages/partidos/PartidoShow.vue'

// Usuarios
import UsuarioIndex from '@/pages/usuarios/UsuarioIndex.vue'
import UsuarioShow from '@/pages/usuarios/UsuarioShow.vue'

// Páginas de error
import NotFound from '@/pages/errors/NotFound.vue'

// ============================================================================
// DEFINICIÓN DE RUTAS
// ============================================================================

const routes = [
  // Redirección inicial
  {
    path: '/',
    redirect: '/dashboard'
  },

  // ========================================
  // RUTAS DE AUTENTICACIÓN
  // ========================================
  {
    path: '/auth',
    component: AuthLayout,
    meta: { requiresGuest: true },
    children: [
      {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { title: 'Iniciar Sesión' }
      },
      {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { title: 'Registrarse' }
      }
    ]
  },

  // ========================================
  // RUTAS PROTEGIDAS
  // ========================================
  {
    path: '/app',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      // Dashboard
      {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' }
      },

      // ========================================
      // RUTAS DE TORNEOS
      // ========================================
      {
        path: '/torneos',
        name: 'torneos.index',
        component: TorneoIndex,
        meta: { title: 'Torneos' }
      },
      {
        path: '/torneos/crear',
        name: 'torneos.create',
        component: TorneoCreate,
        meta: { 
          title: 'Crear Torneo',
          requiresRole: 'administrador'
        }
      },
      {
        path: '/torneos/:id',
        name: 'torneos.show',
        component: TorneoShow,
        props: true,
        meta: { title: 'Ver Torneo' }
      },

      // ========================================
      // RUTAS DE EQUIPOS
      // ========================================
      {
        path: '/equipos',
        name: 'equipos.index',
        component: EquipoIndex,
        meta: { title: 'Equipos' }
      },
      {
        path: '/equipos/crear',
        name: 'equipos.create',
        component: EquipoCreate,
        meta: { 
          title: 'Crear Equipo',
          requiresRole: 'administrador'
        }
      },
      {
        path: '/equipos/:id',
        name: 'equipos.show',
        component: EquipoShow,
        props: true,
        meta: { title: 'Ver Equipo' }
      },

      // ========================================
      // RUTAS DE PARTIDOS
      // ========================================
      {
        path: '/partidos',
        name: 'partidos.index',
        component: PartidoIndex,
        meta: { title: 'Partidos' }
      },
      {
        path: '/partidos/:id',
        name: 'partidos.show',
        component: PartidoShow,
        props: true,
        meta: { title: 'Ver Partido' }
      },

      // ========================================
      // RUTAS DE USUARIOS (Solo administradores)
      // ========================================
      {
        path: '/usuarios',
        name: 'usuarios.index',
        component: UsuarioIndex,
        meta: { 
          title: 'Usuarios',
          requiresRole: 'administrador'
        }
      },
      {
        path: '/usuarios/:id',
        name: 'usuarios.show',
        component: UsuarioShow,
        props: true,
        meta: { 
          title: 'Ver Usuario',
          requiresRole: 'administrador'
        }
      }
    ]
  },

  // ========================================
  // PÁGINA 404
  // ========================================
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: { title: 'Página No Encontrada' }
  }
]

// ============================================================================
// CREAR ROUTER
// ============================================================================

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// ============================================================================
// GUARDS DE NAVEGACIÓN
// ============================================================================

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Actualizar título de la página
  document.title = to.meta.title ? `${to.meta.title} - Sistema Deportivo` : 'Sistema Deportivo'
  
  // Verificar autenticación
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
    
    // Verificar rol específico
    if (to.meta.requiresRole && authStore.userRole !== to.meta.requiresRole) {
      return next({ name: 'dashboard' })
    }
  }
  
  // Verificar si es solo para invitados
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }
  
  next()
})

export default router