/**
 * SERVICIO API - Axios Configuration
 * 
 * Configuración central para todas las llamadas HTTP de la aplicación
 * Incluye interceptores para autenticación, manejo de errores y logging
 * 
 * Ubicación: resources/js/services/api.js
 */

import axios from 'axios';

// ============================================================================
// CONFIGURACIÓN BASE DE AXIOS
// ============================================================================

/**
 * Crear instancia de axios con configuración personalizada
 */
const axiosInstance = axios.create({
  // URL base para todas las peticiones API
  baseURL: import.meta.env.VITE_API_URL || '/api',
  
  // Timeout para las peticiones
  timeout: 30000, // 30 segundos
  
  // Headers por defecto
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  
  // Incluir cookies en las peticiones (para CSRF)
  withCredentials: true
});

// ============================================================================
// INTERCEPTORES DE REQUEST
// ============================================================================

/**
 * Interceptor para peticiones salientes
 * Añade token de autenticación y otros headers necesarios
 */
axiosInstance.interceptors.request.use(
  (config) => {
    // Obtener token del localStorage si existe
    const token = localStorage.getItem('auth_token');
    
    // Añadir token de autorización si existe
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Añadir CSRF token si está disponible (útil para Laravel)
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    // Logging en desarrollo
    if (import.meta.env.DEV) {
      console.log('🚀 API Request:', config.method?.toUpperCase(), config.url, config.data);
    }
    
    return config;
  },
  (error) => {
    console.error('❌ Request Error:', error);
    return Promise.reject(error);
  }
);

// ============================================================================
// INTERCEPTORES DE RESPONSE
// ============================================================================

/**
 * Interceptor para respuestas entrantes
 * Maneja errores globales y tokens expirados
 */
axiosInstance.interceptors.response.use(
  (response) => {
    // Logging en desarrollo
    if (import.meta.env.DEV) {
      console.log('✅ API Response:', response.config.method?.toUpperCase(), 
                  response.config.url, response.status, response.data);
    }
    
    return response;
  },
  async (error) => {
    // Extraer información del error
    const { response, config } = error;
    const status = response?.status;
    const data = response?.data;
    
    // Logging del error
    console.error('❌ API Error:', {
      url: config?.url,
      method: config?.method,
      status,
      message: data?.message,
      errors: data?.errors
    });
    
    // Manejo específico por código de estado
    switch (status) {
      case 401:
        // Token expirado o inválido
        await handleUnauthorized();
        break;
        
      case 403:
        // Sin permisos
        console.warn('🚫 Acceso denegado:', data?.message || 'No tienes permisos para realizar esta acción');
        break;
        
      case 404:
        // Recurso no encontrado
        if (!config.url.includes('/auth/me')) { // No mostrar para verificaciones de auth
          console.warn('🔍 No encontrado:', data?.message || 'El recurso solicitado no fue encontrado');
        }
        break;
        
      case 422:
        // Errores de validación
        handleValidationErrors(data?.errors);
        break;
        
      case 429:
        // Rate limiting
        console.warn('⏱️ Muchas peticiones:', 'Por favor, espera un momento antes de intentar de nuevo');
        break;
        
      case 500:
        // Error interno del servidor
        console.error('🔥 Error del servidor:', 'Ocurrió un error interno. Por favor, intenta más tarde');
        break;
        
      default:
        // Error de red u otros
        if (!response) {
          console.error('🌐 Error de conexión:', 'No se pudo conectar con el servidor. Verifica tu conexión a internet');
        }
    }
    
    return Promise.reject(error);
  }
);

// ============================================================================
// FUNCIONES AUXILIARES
// ============================================================================

/**
 * Manejar errores de autenticación (401)
 */
async function handleUnauthorized() {
  // Limpiar token local
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_data');
  
  // Si estamos en una ruta protegida, redirigir al login
  const currentRoute = window.location.pathname;
  const publicRoutes = ['/login', '/register', '/forgot-password'];
  
  if (!publicRoutes.some(route => currentRoute.startsWith(route))) {
    // Usar router si está disponible, sino usar window.location
    if (window.router) {
      window.router.push({ 
        name: 'login', 
        query: { redirect: currentRoute } 
      });
    } else {
      window.location.href = '/login';
    }
  }
}

/**
 * Manejar errores de validación (422)
 */
function handleValidationErrors(errors) {
  if (errors && typeof errors === 'object') {
    // Log de errores de validación
    Object.keys(errors).forEach(field => {
      const fieldErrors = errors[field];
      if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
        console.warn(`❌ Validación ${field}:`, fieldErrors[0]);
      }
    });
  }
}

// ============================================================================
// FUNCIONES DE CONVENIENCIA PARA HTTP
// ============================================================================

/**
 * Clase API con métodos de conveniencia
 */
class ApiService {
  constructor(axiosInstance) {
    this.axios = axiosInstance;
  }
  
  /**
   * GET request
   */
  async get(url, config = {}) {
    return this.axios.get(url, config);
  }
  
  /**
   * POST request
   */
  async post(url, data = {}, config = {}) {
    return this.axios.post(url, data, config);
  }
  
  /**
   * PUT request
   */
  async put(url, data = {}, config = {}) {
    return this.axios.put(url, data, config);
  }
  
  /**
   * PATCH request
   */
  async patch(url, data = {}, config = {}) {
    return this.axios.patch(url, data, config);
  }
  
  /**
   * DELETE request
   */
  async delete(url, config = {}) {
    return this.axios.delete(url, config);
  }
  
  /**
   * Upload de archivos
   */
  async upload(url, formData, onProgress = null) {
    const config = {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: onProgress || null
    };
    
    return this.axios.post(url, formData, config);
  }
  
  /**
   * Descargar archivos
   */
  async download(url, filename = null) {
    const response = await this.axios.get(url, {
      responseType: 'blob'
    });
    
    // Crear URL temporal para descarga
    const blob = new Blob([response.data]);
    const downloadUrl = window.URL.createObjectURL(blob);
    
    // Crear y hacer clic en enlace temporal
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filename || 'download';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Limpiar URL temporal
    window.URL.revokeObjectURL(downloadUrl);
    
    return response;
  }
  
  /**
   * Configurar token de autenticación
   */
  setAuthToken(token) {
    if (token) {
      this.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
      delete this.axios.defaults.headers.common['Authorization'];
    }
  }
  
  /**
   * Limpiar token de autenticación
   */
  clearAuthToken() {
    delete this.axios.defaults.headers.common['Authorization'];
  }
  
  /**
   * Obtener información del usuario autenticado
   */
  async getCurrentUser() {
    return this.get('/auth/me');
  }
  
  /**
   * Cancelar peticiones pendientes
   */
  cancelPendingRequests() {
    // Implementar si necesitas cancelar peticiones
    // Requiere configurar cancel tokens
  }
}

// ============================================================================
// SERVICIOS ESPECÍFICOS POR MÓDULO
// ============================================================================

/**
 * Servicio para autenticación
 */
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
  refresh: () => api.post('/auth/refresh'),
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (data) => api.post('/auth/reset-password', data),
};

/**
 * Servicio para torneos
 */
export const torneosAPI = {
  getAll: (params = {}) => api.get('/torneos', { params }),
  getById: (id) => api.get(`/torneos/${id}`),
  create: (data) => api.post('/torneos', data),
  update: (id, data) => api.put(`/torneos/${id}`, data),
  delete: (id) => api.delete(`/torneos/${id}`),
  getClasificacion: (id) => api.get(`/torneos/${id}/clasificacion`),
  getPartidos: (id) => api.get(`/torneos/${id}/partidos`),
};

/**
 * Servicio para equipos
 */
export const equiposAPI = {
  getAll: (params = {}) => api.get('/equipos', { params }),
  getById: (id) => api.get(`/equipos/${id}`),
  create: (data) => api.post('/equipos', data),
  update: (id, data) => api.put(`/equipos/${id}`, data),
  delete: (id) => api.delete(`/equipos/${id}`),
  getJugadores: (id) => api.get(`/equipos/${id}/jugadores`),
  addJugador: (id, jugadorData) => api.post(`/equipos/${id}/jugadores`, jugadorData),
  removeJugador: (id, jugadorId) => api.delete(`/equipos/${id}/jugadores/${jugadorId}`),
};

/**
 * Servicio para partidos
 */
export const partidosAPI = {
  getAll: (params = {}) => api.get('/partidos', { params }),
  getById: (id) => api.get(`/partidos/${id}`),
  create: (data) => api.post('/partidos', data),
  update: (id, data) => api.put(`/partidos/${id}`, data),
  delete: (id) => api.delete(`/partidos/${id}`),
  addEvento: (id, eventoData) => api.post(`/partidos/${id}/eventos`, eventoData),
  updateResultado: (id, resultado) => api.patch(`/partidos/${id}/resultado`, resultado),
};

/**
 * Servicio para usuarios
 */
export const usuariosAPI = {
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data) => api.put('/user/profile', data),
  changePassword: (data) => api.put('/user/password', data),
  uploadAvatar: (formData) => api.upload('/user/avatar', formData),
};

// ============================================================================
// EXPORTACIONES
// ============================================================================

// Instancia principal del servicio API
export const api = new ApiService(axiosInstance);

// Exportar también la instancia de axios directamente si se necesita
export default axiosInstance;

/**
 * INSTRUCCIONES DE USO:
 * 
 * 1. En un componente Vue:
 *    import { api, torneosAPI } from '@/services/api';
 * 
 * 2. Uso básico:
 *    const response = await api.get('/endpoint');
 *    const torneos = await torneosAPI.getAll();
 * 
 * 3. Con manejo de errores:
 *    try {
 *      const data = await api.post('/endpoint', payload);
 *    } catch (error) {
 *      // Error ya fue manejado por el interceptor
 *      console.error('Error específico:', error);
 *    }
 * 
 * 4. Upload de archivos:
 *    const formData = new FormData();
 *    formData.append('file', file);
 *    await api.upload('/upload', formData, (progress) => {
 *      console.log('Progress:', progress);
 *    });
 */