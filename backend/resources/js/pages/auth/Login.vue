<template>
    <div class="login-page">
      <div class="login-header">
        <h2>Iniciar Sesión</h2>
        <p>Accede a tu cuenta para gestionar tu sistema deportivo</p>
      </div>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            required
            :class="{ error: errors.email }"
            placeholder="tu@email.com"
            :disabled="loading"
          />
          <div v-if="errors.email" class="error-message">{{ errors.email }}</div>
        </div>
        
        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="password-input">
            <input
              id="password"
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              required
              :class="{ error: errors.password }"
              placeholder="Tu contraseña"
              :disabled="loading"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>
          <div v-if="errors.password" class="error-message">{{ errors.password }}</div>
        </div>
        
        <div class="form-group">
          <label class="checkbox-label">
            <input
              type="checkbox"
              v-model="form.remember"
              :disabled="loading"
            />
            <span class="checkmark"></span>
            Recordar mi sesión
          </label>
        </div>
        
        <button 
          type="submit" 
          class="btn btn-primary btn-lg"
          :disabled="loading"
        >
          <span v-if="loading" class="loading-spinner"></span>
          {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>
        
        <div class="form-footer">
          <p>
            ¿No tienes cuenta? 
            <router-link to="/register" class="link">Regístrate aquí</router-link>
          </p>
        </div>
      </form>
      
      <!-- Usuarios de ejemplo para testing -->
      <div class="demo-users" v-if="showDemoUsers">
        <h3>Usuarios de Prueba</h3>
        <div class="demo-user-grid">
          <div class="demo-user" @click="loginAsDemo('admin@sistema.com', 'Administrador')">
            <span class="demo-icon">👨‍💼</span>
            <span class="demo-role">Administrador</span>
            <span class="demo-email">admin@sistema.com</span>
          </div>
          <div class="demo-user" @click="loginAsDemo('messi@sistema.com', 'Jugador')">
            <span class="demo-icon">⚽</span>
            <span class="demo-role">Jugador</span>
            <span class="demo-email">messi@sistema.com</span>
          </div>
          <div class="demo-user" @click="loginAsDemo('pedro.arbitro@sistema.com', 'Árbitro')">
            <span class="demo-icon">🟨</span>
            <span class="demo-role">Árbitro</span>
            <span class="demo-email">pedro.arbitro@sistema.com</span>
          </div>
        </div>
        <p class="demo-note">
          <small>Contraseña para todos: <strong>password</strong></small>
        </p>
      </div>
      
      <button 
        class="toggle-demo"
        @click="showDemoUsers = !showDemoUsers"
        :disabled="loading"
      >
        {{ showDemoUsers ? 'Ocultar' : 'Mostrar' }} usuarios de prueba
      </button>
    </div>
  </template>
  
  <script>
  import { ref, reactive } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { useToast } from 'vue-toastification'
  
  export default {
    name: 'Login',
    setup() {
      const router = useRouter()
      const authStore = useAuthStore()
      const toast = useToast()
      
      const loading = ref(false)
      const showPassword = ref(false)
      const showDemoUsers = ref(true)
      
      const form = reactive({
        email: '',
        password: '',
        remember: false
      })
      
      const errors = reactive({
        email: '',
        password: ''
      })
      
      // Limpiar errores cuando el usuario escribe
      const clearErrors = () => {
        errors.email = ''
        errors.password = ''
      }
      
      // Validar formulario
      const validateForm = () => {
        let isValid = true
        clearErrors()
        
        // Validar email
        if (!form.email) {
          errors.email = 'El email es obligatorio'
          isValid = false
        } else if (!/\S+@\S+\.\S+/.test(form.email)) {
          errors.email = 'El email no tiene un formato válido'
          isValid = false
        }
        
        // Validar password
        if (!form.password) {
          errors.password = 'La contraseña es obligatoria'
          isValid = false
        }
        
        return isValid
      }
      
      // Manejar login
      const handleLogin = async () => {
        if (!validateForm()) return
        
        loading.value = true
        clearErrors()
        
        try {
          const response = await authStore.login({
            email: form.email,
            password: form.password,
            remember: form.remember
          })
          
          if (response.success) {
            toast.success(`¡Bienvenido ${response.user.nombre}!`)
            
            // Redirigir según el rol o a la página solicitada
            const redirect = router.currentRoute.value.query.redirect || '/dashboard'
            router.push(redirect)
          }
        } catch (error) {
          console.error('Error en login:', error)
          
          if (error.response?.status === 422) {
            // Errores de validación
            const serverErrors = error.response.data.errors
            if (serverErrors.email) errors.email = serverErrors.email[0]
            if (serverErrors.password) errors.password = serverErrors.password[0]
          } else if (error.response?.status === 401) {
            // Credenciales incorrectas
            errors.password = 'Credenciales incorrectas'
            toast.error('Email o contraseña incorrectos')
          } else {
            // Error general
            toast.error('Error al iniciar sesión. Inténtalo de nuevo.')
          }
        } finally {
          loading.value = false
        }
      }
      
      // Login rápido con usuarios demo
      const loginAsDemo = (email, role) => {
        if (loading.value) return
        
        form.email = email
        form.password = 'password'
        form.remember = false
        
        toast.info(`Iniciando sesión como ${role}...`)
        
        // Auto-enviar el formulario
        setTimeout(() => {
          handleLogin()
        }, 500)
      }
      
      return {
        form,
        errors,
        loading,
        showPassword,
        showDemoUsers,
        handleLogin,
        loginAsDemo,
        clearErrors
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .login-page {
    max-width: 400px;
    width: 100%;
    margin: 0 auto;
  }
  
  .login-header {
    text-align: center;
    margin-bottom: 2rem;
    
    h2 {
      font-size: 1.75rem;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 0.5rem;
    }
    
    p {
      color: #6b7280;
      font-size: 0.875rem;
      line-height: 1.5;
    }
  }
  
  .login-form {
    .form-group {
      margin-bottom: 1.5rem;
      
      label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
      }
      
      input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        
        &:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        &.error {
          border-color: #ef4444;
          box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        &:disabled {
          background-color: #f9fafb;
          cursor: not-allowed;
          opacity: 0.6;
        }
      }
    }
    
    .password-input {
      position: relative;
      
      input {
        padding-right: 50px;
      }
      
      .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        padding: 0.25rem;
        border-radius: 0.25rem;
        
        &:hover {
          background-color: #f3f4f6;
        }
      }
    }
    
    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      font-size: 0.875rem;
      color: #374151;
      
      input[type="checkbox"] {
        width: auto;
        margin: 0;
      }
    }
    
    .btn {
      width: 100%;
      padding: 0.75rem 1rem;
      background-color: #3b82f6;
      color: white;
      border: none;
      border-radius: 0.5rem;
      font-weight: 600;
      font-size: 0.875rem;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      
      &:hover:not(:disabled) {
        background-color: #2563eb;
      }
      
      &:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
      }
    }
  }
  
  .loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  
  .form-footer {
    text-align: center;
    
    p {
      color: #6b7280;
      font-size: 0.875rem;
    }
    
    .link {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 500;
      
      &:hover {
        text-decoration: underline;
      }
    }
  }
  
  .demo-users {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    
    h3 {
      font-size: 1rem;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 1rem;
      text-align: center;
    }
  }
  
  .demo-user-grid {
    display: grid;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }
  
  .demo-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
      transform: translateY(-1px);
    }
    
    .demo-icon {
      font-size: 1.25rem;
    }
    
    .demo-role {
      font-weight: 600;
      font-size: 0.875rem;
      flex: 1;
    }
    
    .demo-email {
      font-size: 0.75rem;
      opacity: 0.7;
    }
  }
  
  .demo-note {
    text-align: center;
    color: #6b7280;
    margin: 0;
    font-size: 0.75rem;
  }
  
  .toggle-demo {
    width: 100%;
    padding: 0.5rem;
    margin-top: 1rem;
    background: none;
    border: 1px dashed #d1d5db;
    border-radius: 0.5rem;
    color: #6b7280;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
    
    &:hover:not(:disabled) {
      border-color: #3b82f6;
      color: #3b82f6;
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
  
  .error-message {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
  }
  </style>