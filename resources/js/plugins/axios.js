/**
 * AXIOS PLUGIN
 * 
 * Archivo: resources/js/plugins/axios.js
 * 
 * Configuración de Axios para comunicación con el backend Laravel
 */

import axios from 'axios'
import { useToast } from 'vue-toastification'
import router from '../router/routes'

// Configuración base
axios.defaults.baseURL = 'http://localhost:8000/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Interceptor para requests - agregar token de autenticación
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para responses - manejar errores globalmente
axios.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    const toast = useToast()
    
    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 401:
          // Token expirado o no válido
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user_data')
          router.push('/login')
          toast.error('Sesión expirada. Por favor, inicia sesión nuevamente.')
          break
          
        case 403:
          toast.error('No tienes permisos para realizar esta acción.')
          break
          
        case 404:
          toast.error('Recurso no encontrado.')
          break
          
        case 422:
          // Errores de validación
          if (data.errors) {
            const errors = Object.values(data.errors).flat()
            errors.forEach(error => toast.error(error))
          } else {
            toast.error(data.message || 'Error de validación.')
          }
          break
          
        case 500:
          toast.error('Error interno del servidor. Inténtalo más tarde.')
          break
          
        default:
          toast.error(data.message || 'Ha ocurrido un error inesperado.')
      }
    } else if (error.request) {
      // Error de conexión
      toast.error('Error de conexión. Verifica tu conexión a internet.')
    } else {
      // Otro tipo de error
      toast.error('Ha ocurrido un error inesperado.')
    }
    
    return Promise.reject(error)
  }
)

export default axios