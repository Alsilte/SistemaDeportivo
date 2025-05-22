 
/**
 * CONFIGURACIÓN DEL ROUTER VUE
 * 
 * Define todas las rutas de la aplicación SPA
 * Incluye guards de autenticación y configuración avanzada
 * 
 * Ubicación: resources/js/router/index.js
 */

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// ============================================================================
// IMPORTAR PÁGINAS/COMPONENTES
// ============================================================================

// Páginas de autenticación
const Login = () => import('@/pages/auth/Login.vue');
const Register = () => import('@/pages/auth/Register.vue');
const ForgotPassword = () => import('@/pages/auth/ForgotPassword.vue');
const ResetPassword = () => import('@/pages/auth/ResetPassword.vue');

// Dashboard
const Dashboard = () => import('@/pages/dashboard/Dashboard.vue');

// Gestión de Torneos
const TorneoIndex = () => import('@/pages/torneos/TorneoIndex.vue');
const TorneoShow = () => import('@/pages/torneos/TorneoShow.vue');
const TorneoCreate = () => import('@/pages/torneos/TorneoCreate.vue');
const TorneoEdit = () => import('@/pages/torneos/TorneoEdit.vue');

// Gestión de Equipos
const EquipoIndex = () => import('@/pages/equipos/EquipoIndex.vue');
const EquipoShow = () => import('@/pages/equipos/EquipoShow.vue');
const EquipoCreate = () => import('@/pages/equipos/EquipoCreate.vue');
const EquipoEdit = () => import('@/pages/equipos/EquipoEdit.vue');

// Gestión de Partidos
const PartidoIndex = () => import('@/pages/partidos/PartidoIndex.vue');
const PartidoShow = () => import('@/pages/partidos/PartidoShow.vue');
const PartidoCreate = () => import('@/pages/partidos/PartidoCreate.vue');

// Páginas de Usuario
const UserProfile = () => import('@/pages/user/Profile.vue');
const UserSettings = () => import('@/pages/user/Settings.vue');

// Páginas de Error
const NotFound = () => import('@/pages/errors/NotFound.vue');
const Forbidden = () => import('@/pages/errors/Forbidden.vue');

// ============================================================================
// DEFINICIÓN DE RUTAS
// ============================================================================

const routes = [
  // ========================================
  // RUTAS PÚBLICAS (NO REQUIEREN AUTH)
  // ========================================
  {
    path: '/',
    name: 'home',
    redirect: { name: 'dashboard' }, // Redirigir al dashboard si está autenticado
    meta: { 
      title: 'Inicio',
      requiresAuth: false 
    }
  },
  
  // Rutas de autenticación
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { 
      title: 'Iniciar Sesión',
      requiresAuth: false,
      requiresGuest: true // Solo para usuarios no autenticados
    }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { 
      title: 'Registrarse',
      requiresAuth: false,
      requiresGuest: true 
    }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { 
      title: 'Recuperar Contraseña',
      requiresAuth: false,
      requiresGuest: true 
    }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: ResetPassword,
    props: true,
    meta: { 
      title: 'Restablecer Contraseña',
      requiresAuth: false,
      requiresGuest: true 
    }
  },

  // ========================================
  // RUTAS PRIVADAS (REQUIEREN AUTH)
  // ========================================
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { 
      title: 'Dashboard',
      requiresAuth: true 
    }
  },

  // ========================================
  // RUTAS DE TORNEOS
  // ========================================
  {
    path: '/torneos',
    name: 'torneos.index',
    component: TorneoIndex,
    meta: { 
      title: 'Torneos',
      requiresAuth: true 
    }
  },
  {
    path: '/torneos/crear',
    name: 'torneos.create',
    component: TorneoCreate,
    meta: { 
      title: 'Crear Torneo',
      requiresAuth: true,
      permissions: ['create_torneo'] // Permisos específicos
    }
  },
  {
    path: '/torneos/:id',
    name: 'torneos.show',
    component: TorneoShow,
    props: true,
    meta: { 
      title: 'Ver Torneo',
      requiresAuth: true 
    }
  },
  {
    path: '/torneos/:id/editar',
    name: 'torneos.edit',
    component: TorneoEdit,
    props: true,
    meta: { 
      title: 'Editar Torneo',
      requiresAuth: true,
      permissions: ['edit_torneo']
    }
  },

  // ========================================
  // RUTAS DE EQUIPOS
  // ========================================
  {
    path: '/equipos',
    name: 'equipos.index',
    component: EquipoIndex,
    meta: { 
      title: 'Equipos',
      requiresAuth: true 
    }
  },
  {
    path: '/equipos/crear',
    name: 'equipos.create',
    component: EquipoCreate,
    meta: { 
      title: 'Crear Equipo',
      requiresAuth: true,
      permissions: ['create_equipo']
    }
  },
  {
    path: '/equipos/:id',
    name: 'equipos.show',
    component: EquipoShow,
    props: true,
    meta: { 
      title: 'Ver Equipo',
      requiresAuth: true 
    }
  },
  {
    path: '/equipos/:id/editar',
    name: 'equipos.edit',
    component: EquipoEdit,
    props: true,
    meta: { 
      title: 'Editar Equipo',
      requiresAuth: true,
      permissions: ['edit_equipo']
    }
  },

  // ========================================
  // RUTAS DE PARTIDOS
  // ========================================
  {
    path: '/partidos',
    name: 'partidos.index',
    component: PartidoIndex,
    meta: { 
      title: 'Partidos',
      requiresAuth: true 
    }
  },
  {
    path: '/partidos/crear',
    name: 'partidos.create',
    component: PartidoCreate,
    meta: { 
      title: 'Programar Partido',
      requiresAuth: true,
      permissions: ['create_partido']
    }
  },
  {
    path: '/partidos/:id',
    name: 'partidos.show',
    component: PartidoShow,
    props: true,
    meta: { 
      title: 'Ver Partido',
      requiresAuth: true 
    }
  },

  // ========================================
  // RUTAS DE USUARIO
  // ========================================
  {
    path: '/perfil',
    name: 'user.profile',
    component: UserProfile,
    meta: { 
      title: 'Mi Perfil',
      requiresAuth: true 
    }
  },
  {
    path: '/configuracion',
    name: 'user.settings',
    component: UserSettings,
    meta: { 
      title: 'Configuración',
      requiresAuth: true 
    }
  },

  // ========================================
  // RUTAS DE ERROR
  // ========================================
  {
    path: '/403',
    name: 'forbidden',
    component: Forbidden,
    meta: { 
      title: 'Acceso Denegado',
      requiresAuth: false 
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: { 
      title: 'Página No Encontrada',
      requiresAuth: false 
    }
  }
];

// ============================================================================
// CREAR INSTANCIA DEL ROUTER
// ============================================================================

const router = createRouter({
  history: createWebHistory(),
  routes,
  
  // Configuración adicional del router
  scrollBehavior(to, from, savedPosition) {
    // Comportamiento de scroll automático
    if (savedPosition) {
      return savedPosition;
    } else if (to.hash) {
      return { el: to.hash, behavior: 'smooth' };
    } else {
      return { top: 0 };
    }
  }
});

// ============================================================================
// GUARDS DE NAVEGACIÓN GLOBALES
// ============================================================================

/**
 * Guard de autenticación antes de cada ruta
 */
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Actualizar el título de la página
  document.title = to.meta.title ? `${to.meta.title} - Sistema Deportivo` : 'Sistema Deportivo';
  
  // Verificar si la ruta requiere autenticación
  if (to.meta.requiresAuth) {
    // Si no hay token, verificar con el servidor
    if (!authStore.token) {
      await authStore.checkAuthStatus();
    }
    
    // Si aún no está autenticado, redirigir al login
    if (!authStore.isAuthenticated) {
      next({ 
        name: 'login', 
        query: { redirect: to.fullPath } 
      });
      return;
    }
    
    // Verificar permisos específicos si los hay
    if (to.meta.permissions && to.meta.permissions.length > 0) {
      const hasPermission = to.meta.permissions.some(permission => 
        authStore.hasPermission(permission)
      );
      
      if (!hasPermission) {
        next({ name: 'forbidden' });
        return;
      }
    }
  }
  
  // Verificar si la ruta es solo para invitados (usuarios no autenticados)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' });
    return;
  }
  
  // Permitir la navegación
  next();
});

/**
 * Guard después de cada navegación
 */
router.afterEach((to, from) => {
  // Aquí puedes agregar lógica para analytics, loading states, etc.
  console.log(`Navegación completada: ${from.path} -> ${to.path}`);
});

// ============================================================================
// EXPORTAR ROUTER
// ============================================================================

export default router;

/**
 * FUNCIONES AUXILIARES PARA USAR EN COMPONENTES
 */

/**
 * Función para navegar programáticamente con verificación de permisos
 */
export function navigateToRoute(routeName, params = {}, query = {}) {
  return router.push({ name: routeName, params, query });
}

/**
 * Función para verificar si una ruta está activa
 */
export function isRouteActive(routeName) {
  return router.currentRoute.value.name === routeName;
}

/**
 * INSTRUCCIONES PARA USAR:
 * 
 * 1. Crear todos los componentes de página mencionados en las rutas
 * 2. Configurar el store de auth con los métodos necesarios
 * 3. Ajustar los permisos según tu sistema de roles
 * 4. Personalizar el comportamiento de scroll si es necesario
 */