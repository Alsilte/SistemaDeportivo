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
          />
          <span class="checkmark"></span>
          Recordar mi sesión
        </label>
      </div>
      
      <button 
        type="submit" 
        class="btn btn-primary btn-lg"
        :disabled="authStore.loading"
      >
        <span v-if="authStore.loading" class="loading"></span>
        {{ authStore.loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
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
    >
      {{ showDemoUsers ? 'Ocultar' : 'Mostrar' }} usuarios de prueba
    </button>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const showPassword = ref(false)
    const showDemoUsers = ref(false)
    
    const form = reactive({
      email: '',
      password: '',
      remember: false
    })
    
    const errors = reactive({
      email: '',
      password: ''
    })
    
    // Validar formulario
    const validateForm = () => {
      let isValid = true
      
      // Reset errores
      errors.email = ''
      errors.password = ''
      
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
      } else if (form.password.length < 6) {
        errors.password = 'La contraseña debe tener al menos 6 caracteres'
        isValid = false
      }
      
      return isValid
    }
    
    // Manejar login
    const handleLogin = async () => {
      if (!validateForm()) return
      
      const result = await authStore.login({
        email: form.email,
        password: form.password,
        remember: form.remember
      })
      
      if (result.success) {
        // Redirigir según el rol
        const userRole = result.user.tipo_usuario
        
        switch (userRole) {
          case 'administrador':
            router.push('/dashboard')
            break
          case 'jugador':
            router.push('/dashboard')
            break
          case 'arbitro':
            router.push('/partidos')
            break
          default:
            router.push('/dashboard')
        }
      }
    }
    
    // Login rápido con usuarios demo
    const loginAsDemo = (email, role) => {
      form.email = email
      form.password = 'password'
      form.remember = false
      
      // Auto-enviar el formulario
      setTimeout(() => {
        handleLogin()
      }, 100)
    }
    
    return {
      form,
      errors,
      showPassword,
      showDemoUsers,
      authStore,
      handleLogin,
      loginAsDemo
    }
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  max-width: 400px;
  width: 100%;
}

.login-header {
  text-align: center;
  margin-bottom: var(--spacing-xl);
  
  h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: var(--spacing-sm);
  }
  
  p {
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.5;
  }
}

.login-form {
  .form-group {
    margin-bottom: var(--spacing-lg);
  }
  
  .password-input {
    position: relative;
    
    input {
      padding-right: 50px;
    }
    
    .password-toggle {
      position: absolute;
      right: var(--spacing-md);
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      
      &:hover {
        opacity: 0.7;
      }
    }
  }
  
  .checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--gray-700);
    
    input[type="checkbox"] {
      width: auto;
      margin: 0;
    }
  }
  
  .btn {
    width: 100%;
    margin-bottom: var(--spacing-lg);
  }
}

.form-footer {
  text-align: center;
  
  p {
    color: var(--gray-600);
    font-size: 0.875rem;
  }
  
  .link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    
    &:hover {
      text-decoration: underline;
    }
  }
}

.demo-users {
  margin-top: var(--spacing-xl);
  padding: var(--spacing-lg);
  background: var(--gray-50);
  border-radius: var(--border-radius);
  border: 1px solid var(--gray-200);
  
  h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: var(--spacing-md);
    text-align: center;
  }
}

.demo-user-grid {
  display: grid;
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-md);
}

.demo-user {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-md);
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all var(--transition-fast);
  
  &:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }
  
  .demo-icon {
    font-size: 1.25rem;
  }
  
  .demo-role {
    font-weight: 600;
    font-size: 0.875rem;
  }
  
  .demo-email {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-left: auto;
  }
}

.demo-note {
  text-align: center;
  color: var(--gray-500);
  margin: 0;
}

.toggle-demo {
  width: 100%;
  padding: var(--spacing-sm);
  margin-top: var(--spacing-md);
  background: none;
  border: 1px dashed var(--gray-300);
  border-radius: var(--border-radius);
  color: var(--gray-600);
  cursor: pointer;
  font-size: 0.75rem;
  transition: all var(--transition-fast);
  
  &:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
  }
}
</style>