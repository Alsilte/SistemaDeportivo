/**
 * API Service Configuration
 * Handles HTTP requests to Laravel backend with authentication
 */

import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

// Create axios instance with base configuration
const api = axios.create({
  baseURL: '/api', // Proxied to http://localhost:8000/api through Vite
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

/**
 * Request interceptor
 * Adds authentication token to requests
 */
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    const token = authStore.token
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

/**
 * Response interceptor
 * Handles authentication errors and token refresh
 */
api.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    const authStore = useAuthStore()
    
    if (error.response?.status === 401) {
      // Unauthorized - clear auth and redirect to login
      authStore.logout()
      if (router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login' })
      }
    } else if (error.response?.status === 403) {
      // Forbidden - user doesn't have permission
      console.error('Access forbidden:', error.response.data.message)
    } else if (error.response?.status >= 500) {
      // Server error
      console.error('Server error:', error.response.data)
    }
    
    return Promise.reject(error)
  }
)

/**
 * API methods for different resources
 */

// Authentication endpoints
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  refresh: () => api.post('/auth/refresh'),
  profile: () => api.get('/auth/profile'),
  updateProfile: (data) => api.put('/auth/profile', data)
}

// Tournament endpoints
export const tournamentAPI = {
  getAll: (params = {}) => api.get('/tournaments', { params }),
  getById: (id) => api.get(`/tournaments/${id}`),
  create: (data) => api.post('/tournaments', data),
  update: (id, data) => api.put(`/tournaments/${id}`, data),
  delete: (id) => api.delete(`/tournaments/${id}`),
  registerTeam: (id, teamData) => api.post(`/tournaments/${id}/register`, teamData),
  getStandings: (id) => api.get(`/tournaments/${id}/standings`),
  getMatches: (id) => api.get(`/tournaments/${id}/matches`)
}

// Team endpoints
export const teamAPI = {
  getAll: (params = {}) => api.get('/teams', { params }),
  getById: (id) => api.get(`/teams/${id}`),
  create: (data) => api.post('/teams', data),
  update: (id, data) => api.put(`/teams/${id}`, data),
  delete: (id) => api.delete(`/teams/${id}`),
  getRoster: (id) => api.get(`/teams/${id}/roster`),
  addPlayer: (id, playerData) => api.post(`/teams/${id}/players`, playerData),
  removePlayer: (teamId, playerId) => api.delete(`/teams/${teamId}/players/${playerId}`),
  getStatistics: (id) => api.get(`/teams/${id}/statistics`)
}

// Player endpoints
export const playerAPI = {
  getAll: (params = {}) => api.get('/players', { params }),
  getById: (id) => api.get(`/players/${id}`),
  update: (id, data) => api.put(`/players/${id}`, data),
  getStatistics: (id) => api.get(`/players/${id}/statistics`),
  getTeamHistory: (id) => api.get(`/players/${id}/team-history`),
  getAvailable: (params = {}) => api.get('/players/available', { params })
}

// Match endpoints
export const matchAPI = {
  getAll: (params = {}) => api.get('/matches', { params }),
  getById: (id) => api.get(`/matches/${id}`),
  create: (data) => api.post('/matches', data),
  update: (id, data) => api.put(`/matches/${id}`, data),
  addEvent: (id, eventData) => api.post(`/matches/${id}/events`, eventData),
  getEvents: (id) => api.get(`/matches/${id}/events`)
}

// Standing endpoints
export const standingAPI = {
  getByTournament: (tournamentId, params = {}) => api.get(`/standings/${tournamentId}`, { params }),
  recalculate: (tournamentId) => api.post(`/standings/${tournamentId}/recalculate`),
  getTeamStats: (tournamentId, teamId) => api.get(`/standings/${tournamentId}/teams/${teamId}`),
  getTopScorers: (tournamentId, params = {}) => api.get(`/standings/${tournamentId}/top-scorers`, { params })
}

/**
 * Generic API helper functions
 */
export const apiHelpers = {
  // Handle API errors with user-friendly messages
  handleError: (error) => {
    if (error.response?.data?.message) {
      return error.response.data.message
    } else if (error.response?.data?.errors) {
      // Laravel validation errors
      const errors = error.response.data.errors
      return Object.values(errors).flat().join(', ')
    } else if (error.message) {
      return error.message
    } else {
      return 'An unexpected error occurred'
    }
  },
  
  // Check if request was successful
  isSuccess: (response) => {
    return response.data?.success === true
  },
  
  // Extract data from successful response
  getData: (response) => {
    return response.data?.data || response.data
  }
}

export default api