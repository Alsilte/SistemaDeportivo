/**
 * AUTH STORE - Gestión de autenticación con Pinia
 * 
 * Archivo: resources/js/stores/auth.js
 * 
 * Store para manejar autenticación, usuarios y permisos
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'

export const useAuthStore = defineStore('auth', () => {
  // Estado
  const user = ref(null)
  const token = ref(null)
  const loading = ref(false)
  
  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.tipo_usuario || null)
  const userName = computed(() => user.value?.nombre || '')
  const userEmail = computed(() => user.value?.email || '')
  
  // Verificar si el usuario tiene un rol específico
  const hasRole = computed(() => (role) => {
    return userRole.value === role
  })
  
  // Verificar si el usuario es administrador
  const isAdmin = computed(() => userRole.value === 'administrador')
  const isJugador = computed(() => userRole.value === 'jugador')
  const isArbitro = computed(() => userRole.value === 'arbitro')
  
  // Actions
  const setUser = (userData) => {
    user.value = userData
    localStorage.setItem('user_data', JSON.stringify(userData))
  }
  
  const setToken = (tokenValue) => {
    token.value = tokenValue
    localStorage.setItem('auth_token', tokenValue)
    axios.defaults.headers.common['Authorization'] = `Bearer ${tokenValue}`
  }
  
  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('user_data')
    localStorage.removeItem('auth_token')
    delete axios.defaults.headers.common['Authorization']
  }
  
  // Login
  const login = async (credentials) => {
    loading.value = true
    
    try {
      const response = await axios.post('/auth/login', {
        email: credentials.email,
        password: credentials.password,
        remember: credentials.remember || false,
        device_name: 'web_browser'
      })
      
      if (response.data.success) {
        const { user: userData, token: tokenValue } = response.data.data
        
        setUser(userData)
        setToken(tokenValue)
        
        notify({
          type: 'success',
          title: 'Bienvenido',
          text: `¡Hola ${userData.nombre}! Has iniciado sesión correctamente.`
        })
        
        return { success: true, user: userData }
      } else {
        throw new Error(response.data.message)
      }
    } catch (error) {
      console.error('Error en login:', error)
      
      let errorMessage = 'Error al iniciar sesión'
      
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message
      } else if (error.message) {
        errorMessage = error.message
      }
      
      notify({
        type: 'error',
        title: 'Error de autenticación',
        text: errorMessage
      })
      
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }
  
  // Register
  const register = async (userData) => {
    loading.value = true
    
    try {
      const response = await axios.post('/auth/register', userData)
      
      if (response.data.success) {
        const { user: newUser, token: tokenValue } = response.data.data
        
        setUser(newUser)
        setToken(tokenValue)
        
        notify({
          type: 'success',
          title: 'Registro exitoso',
          text: `¡Bienvenido ${newUser.nombre}! Tu cuenta ha sido creada.`
        })
        
        return { success: true, user: newUser }
      } else {
        throw new Error(response.data.message)
      }
    } catch (error) {
      console.error('Error en registro:', error)
      
      let errorMessage = 'Error al registrar usuario'
      
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message
      } else if (error.message) {
        errorMessage = error.message
      }
      
      // Los errores de validación ya se manejan en axios interceptor
      if (error.response?.status !== 422) {
        notify({
          type: 'error',
          title: 'Error de registro',
          text: errorMessage
        })
      }
      
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }
  
  // Logout
  const logout = async () => {
    loading.value = true
    
    try {
      await axios.post('/auth/logout')
    } catch (error) {
      console.error('Error en logout:', error)
    } finally {
      clearAuth()
      loading.value = false
      
      notify({
        type: 'info',
        title: 'Sesión cerrada',
        text: 'Has cerrado sesión correctamente.'
      })
    }
  }
  
  // Obtener datos del usuario actual
  const fetchUser = async () => {
    if (!token.value) return
    
    try {
      const response = await axios.get('/auth/me')
      
      if (response.data.success) {
        setUser(response.data.data)
        return response.data.data
      }
    } catch (error) {
      console.error('Error al obtener usuario:', error)
      
      // Si hay error 401, limpiar autenticación
      if (error.response?.status === 401) {
        clearAuth()
      }
    }
  }
  
  // Actualizar perfil
  const updateProfile = async (profileData) => {
    loading.value = true
    
    try {
      const response = await axios.put('/auth/profile', profileData)
      
      if (response.data.success) {
        setUser(response.data.data)
        
        notify({
          type: 'success',
          title: 'Perfil actualizado',
          text: 'Tu perfil ha sido actualizado correctamente.'
        })
        
        return { success: true, user: response.data.data }
      }
    } catch (error) {
      console.error('Error al actualizar perfil:', error)
      return { success: false, error: error.response?.data?.message || 'Error al actualizar perfil' }
    } finally {
      loading.value = false
    }
  }
  
  // Cambiar contraseña
  const changePassword = async (passwordData) => {
    loading.value = true
    
    try {
      const response = await axios.post('/auth/change-password', passwordData)
      
      if (response.data.success) {
        notify({
          type: 'success',
          title: 'Contraseña actualizada',
          text: 'Tu contraseña ha sido cambiada correctamente.'
        })
        
        return { success: true }
      }
    } catch (error) {
      console.error('Error al cambiar contraseña:', error)
      return { success: false, error: error.response?.data?.message || 'Error al cambiar contraseña' }
    } finally {
      loading.value = false
    }
  }
  
  // Inicializar store (cargar datos del localStorage)
  const initializeAuth = () => {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('user_data')
    
    if (savedToken && savedUser) {
      try {
        setToken(savedToken)
        setUser(JSON.parse(savedUser))
        
        // Verificar si el token sigue siendo válido
        fetchUser()
      } catch (error) {
        console.error('Error al inicializar autenticación:', error)
        clearAuth()
      }
    }
  }
  
  // Retornar estado y métodos
  return {
    // Estado
    user,
    token,
    loading,
    
    // Getters
    isAuthenticated,
    userRole,
    userName,
    userEmail,
    hasRole,
    isAdmin,
    isJugador,
    isArbitro,
    
    // Actions
    setUser,
    setToken,
    clearAuth,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    changePassword,
    initializeAuth
  }
})