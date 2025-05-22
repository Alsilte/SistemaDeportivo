/**
 * ROUTES.JS - Configuración de rutas Vue Router
 * 
 * Archivo: resources/js/router/routes.js
 * 
 * Definición de todas las rutas de la aplicación frontend
 */

// Importar componentes
import Home from '../pages/Home.vue'
import Login from '../pages/auth/Login.vue'
import Register from '../pages/auth/Register.vue'
import Dashboard from '../pages/Dashboard.vue'

// Layouts
import AuthLayout from '../layouts/AuthLayout.vue'
import AppLayout from '../layouts/AppLayout.vue'

// Páginas de gestión
import Torneos from '../pages/torneos/Index.vue'
import TorneoDetalle from '../pages/torneos/Show.vue'
import TorneoCrear from '../pages/torneos/Create.vue'

import Equipos from '../pages/equipos/Index.vue'
import EquipoDetalle from '../pages/equipos/Show.vue'
import EquipoCrear from '../pages/equipos/Create.vue'

import Partidos from '../pages/partidos/Index.vue'
import PartidoDetalle from '../pages/partidos/Show.vue'

import Usuarios from '../pages/usuarios/Index.vue'
import UsuarioDetalle from '../pages/usuarios/Show.vue'

// Middleware de autenticación
const requireAuth = (to, from, next) => {
  const token = localStorage.getItem('auth_token')
  if (!token) {
    next('/login')
  } else {
    next()
  }
}

// Middleware para invitados (no autenticados)
const requireGuest = (to, from, next) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    next('/dashboard')
  } else {
    next()
  }
}

// Middleware para roles específicos
const requireRole = (roles) => {
  return (to, from, next) => {
    const userData = JSON.parse(localStorage.getItem('user_data') || '{}')
    const userRole = userData.tipo_usuario
    
    if (roles.includes(userRole)) {
      next()
    } else {
      next('/dashboard')
    }
  }
}

// Definición de rutas
const routes = [
  {
    path: '/',
    component: AppLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: Home
      }
    ]
  },
  
  // Rutas de autenticación
  {
    path: '/auth',
    component: AuthLayout,
    beforeEnter: requireGuest,
    children: [
      {
        path: '/login',
        name: 'login',
        component: Login
      },
      {
        path: '/register',
        name: 'register',
        component: Register
      }
    ]
  },
  
  // Rutas protegidas
  {
    path: '/app',
    component: AppLayout,
    beforeEnter: requireAuth,
    children: [
      {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard
      },
      
      // Gestión de torneos
      {
        path: '/torneos',
        name: 'torneos.index',
        component: Torneos
      },
      {
        path: '/torneos/crear',
        name: 'torneos.create',
        component: TorneoCrear,
        beforeEnter: requireRole(['administrador'])
      },
      {
        path: '/torneos/:id',
        name: 'torneos.show',
        component: TorneoDetalle,
        props: true
      },
      
      // Gestión de equipos
      {
        path: '/equipos',
        name: 'equipos.index',
        component: Equipos
      },
      {
        path: '/equipos/crear',
        name: 'equipos.create',
        component: EquipoCrear,
        beforeEnter: requireRole(['administrador'])
      },
      {
        path: '/equipos/:id',
        name: 'equipos.show',
        component: EquipoDetalle,
        props: true
      },
      
      // Gestión de partidos
      {
        path: '/partidos',
        name: 'partidos.index',
        component: Partidos
      },
      {
        path: '/partidos/:id',
        name: 'partidos.show',
        component: PartidoDetalle,
        props: true
      },
      
      // Gestión de usuarios (solo administradores)
      {
        path: '/usuarios',
        name: 'usuarios.index',
        component: Usuarios,
        beforeEnter: requireRole(['administrador'])
      },
      {
        path: '/usuarios/:id',
        name: 'usuarios.show',
        component: UsuarioDetalle,
        props: true,
        beforeEnter: requireRole(['administrador'])
      }
    ]
  },
  
  // Ruta 404 - debe ir al final
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('../pages/NotFound.vue')
  }
]

export default routes