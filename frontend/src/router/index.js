/**
 * Vue Router Configuration
 * Defines application routes with authentication guards
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Lazy load components for better performance
const Home = () => import('@/views/Home.vue')
const Login = () => import('@/views/auth/Login.vue')
const Register = () => import('@/views/auth/Register.vue')
const Dashboard = () => import('@/views/Dashboard.vue')
const Profile = () => import('@/views/Profile.vue')

// Tournament views
const Tournaments = () => import('@/views/tournaments/Tournaments.vue')
const TournamentDetail = () => import('@/views/tournaments/TournamentDetail.vue')
const CreateTournament = () => import('@/views/tournaments/CreateTournament.vue')

// Team views
const Teams = () => import('@/views/teams/Teams.vue')
const TeamDetail = () => import('@/views/teams/TeamDetail.vue')
const CreateTeam = () => import('@/views/teams/CreateTeam.vue')
const TeamRoster = () => import('@/views/teams/TeamRoster.vue')

// Player views
const Players = () => import('@/views/players/Players.vue')
const PlayerDetail = () => import('@/views/players/PlayerDetail.vue')

// Match views
const Matches = () => import('@/views/matches/Matches.vue')
const MatchDetail = () => import('@/views/matches/MatchDetail.vue')
const CreateMatch = () => import('@/views/matches/CreateMatch.vue')

// Admin views
const AdminPanel = () => import('@/views/admin/AdminPanel.vue')
const UserManagement = () => import('@/views/admin/UserManagement.vue')

const routes = [
  // Public routes
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { 
      title: 'Sports Tournament Manager',
      public: true 
    }
  },
  {
    path: '/tournaments',
    name: 'tournaments',
    component: Tournaments,
    meta: { 
      title: 'Tournaments',
      public: true 
    }
  },
  {
    path: '/tournaments/:id',
    name: 'tournament-detail',
    component: TournamentDetail,
    meta: { 
      title: 'Tournament Details',
      public: true 
    }
  },
  {
    path: '/teams',
    name: 'teams',
    component: Teams,
    meta: { 
      title: 'Teams',
      public: true 
    }
  },
  {
    path: '/teams/:id',
    name: 'team-detail',
    component: TeamDetail,
    meta: { 
      title: 'Team Details',
      public: true 
    }
  },
  {
    path: '/players',
    name: 'players',
    component: Players,
    meta: { 
      title: 'Players',
      public: true 
    }
  },
  {
    path: '/players/:id',
    name: 'player-detail',
    component: PlayerDetail,
    meta: { 
      title: 'Player Details',
      public: true 
    }
  },
  {
    path: '/matches',
    name: 'matches',
    component: Matches,
    meta: { 
      title: 'Matches',
      public: true 
    }
  },
  {
    path: '/matches/:id',
    name: 'match-detail',
    component: MatchDetail,
    meta: { 
      title: 'Match Details',
      public: true 
    }
  },

  // Authentication routes
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { 
      title: 'Login',
      public: true,
      hideForAuth: true 
    }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { 
      title: 'Register',
      public: true,
      hideForAuth: true 
    }
  },

  // Protected routes
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { 
      title: 'Dashboard',
      requiresAuth: true 
    }
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { 
      title: 'Profile',
      requiresAuth: true 
    }
  },

  // Team management (Team Manager + Admin)
  {
    path: '/teams/create',
    name: 'create-team',
    component: CreateTeam,
    meta: { 
      title: 'Create Team',
      requiresAuth: true,
      roles: ['admin', 'team_manager']
    }
  },
  {
    path: '/teams/:id/roster',
    name: 'team-roster',
    component: TeamRoster,
    meta: { 
      title: 'Team Roster',
      requiresAuth: true,
      roles: ['admin', 'team_manager']
    }
  },

  // Tournament management (Admin only)
  {
    path: '/tournaments/create',
    name: 'create-tournament',
    component: CreateTournament,
    meta: { 
      title: 'Create Tournament',
      requiresAuth: true,
      roles: ['admin']
    }
  },

  // Match management (Admin + Referee)
  {
    path: '/matches/create',
    name: 'create-match',
    component: CreateMatch,
    meta: { 
      title: 'Create Match',
      requiresAuth: true,
      roles: ['admin']
    }
  },

  // Admin routes
  {
    path: '/admin',
    name: 'admin',
    component: AdminPanel,
    meta: { 
      title: 'Admin Panel',
      requiresAuth: true,
      roles: ['admin']
    }
  },
  {
    path: '/admin/users',
    name: 'user-management',
    component: UserManagement,
    meta: { 
      title: 'User Management',
      requiresAuth: true,
      roles: ['admin']
    }
  },

  // 404 catch-all
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFound.vue'),
    meta: { 
      title: 'Page Not Found',
      public: true 
    }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

/**
 * Navigation guards
 */
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Set document title
  document.title = to.meta.title ? `${to.meta.title} - Sports Tournament Manager` : 'Sports Tournament Manager'
  
  // Skip auth check for public routes
  if (to.meta.public && !to.meta.requiresAuth) {
    // Hide login/register pages for authenticated users
    if (to.meta.hideForAuth && authStore.isAuthenticated) {
      next({ name: 'dashboard' })
      return
    }
    next()
    return
  }
  
  // Check authentication for protected routes
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next({ 
        name: 'login', 
        query: { redirect: to.fullPath } 
      })
      return
    }
    
    // Check role-based access
    if (to.meta.roles && !authStore.hasAnyRole(to.meta.roles)) {
      // Redirect to dashboard if user doesn't have required role
      next({ name: 'dashboard' })
      return
    }
  }
  
  next()
})

/**
 * Error handler for navigation failures
 */
router.onError((error) => {
  console.error('Router error:', error)
})

export default router