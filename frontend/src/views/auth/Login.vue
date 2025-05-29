<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
          <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
            <TrophyIcon class="w-8 h-8 text-white" />
          </div>
        </div>
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <RouterLink 
            to="/register" 
            class="font-medium text-primary-600 hover:text-primary-500 transition-colors"
          >
            create a new account
          </RouterLink>
        </p>
      </div>
  
      <!-- Login Form -->
      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="card px-4 py-8 sm:px-10">
          <form @submit.prevent="handleLogin" class="space-y-6">
            <!-- Email Field -->
            <div>
              <label for="email" class="form-label">
                Email address
              </label>
              <div class="relative">
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  required
                  :class="[
                    'form-input',
                    errors.email ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                  ]"
                  placeholder="Enter your email"
                  :disabled="isLoading"
                />
                <EnvelopeIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              </div>
              <p v-if="errors.email" class="form-error">
                {{ errors.email }}
              </p>
            </div>
  
            <!-- Password Field -->
            <div>
              <label for="password" class="form-label">
                Password
              </label>
              <div class="relative">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  required
                  :class="[
                    'form-input pr-10',
                    errors.password ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                  ]"
                  placeholder="Enter your password"
                  :disabled="isLoading"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                  :disabled="isLoading"
                >
                  <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                  <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
              </div>
              <p v-if="errors.password" class="form-error">
                {{ errors.password }}
              </p>
            </div>
  
            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="remember"
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  :disabled="isLoading"
                />
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                  Remember me
                </label>
              </div>
              <div class="text-sm">
                <a href="#" class="font-medium text-primary-600 hover:text-primary-500 transition-colors">
                  Forgot your password?
                </a>
              </div>
            </div>
  
            <!-- General Error -->
            <div v-if="generalError" class="rounded-lg bg-danger-50 border border-danger-200 p-4">
              <div class="flex items-center">
                <ExclamationTriangleIcon class="w-5 h-5 text-danger-600 mr-2" />
                <p class="text-sm text-danger-700">{{ generalError }}</p>
              </div>
            </div>
  
            <!-- Submit Button -->
            <div>
              <button
                type="submit"
                :disabled="isLoading || !isFormValid"
                class="w-full btn-primary py-3 text-base"
              >
                <div v-if="isLoading" class="flex items-center justify-center">
                  <div class="spinner w-5 h-5 mr-2"></div>
                  Signing in...
                </div>
                <span v-else>Sign in</span>
              </button>
            </div>
          </form>
  
          <!-- Divider -->
          <div class="mt-6">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300" />
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with</span>
              </div>
            </div>
  
            <!-- Social Login (Future Enhancement) -->
            <div class="mt-6 text-center">
              <p class="text-sm text-gray-500">
                Social login coming soon
              </p>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Demo Accounts -->
      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="card p-6">
          <h3 class="text-sm font-medium text-gray-900 mb-4">Demo Accounts</h3>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Admin:</span>
              <button 
                @click="fillDemoCredentials('admin')"
                class="text-primary-600 hover:text-primary-700 font-medium"
                :disabled="isLoading"
              >
                admin@example.com
              </button>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Team Manager:</span>
              <button 
                @click="fillDemoCredentials('manager')"
                class="text-primary-600 hover:text-primary-700 font-medium"
                :disabled="isLoading"
              >
                manager@example.com
              </button>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Player:</span>
              <button 
                @click="fillDemoCredentials('player')"
                class="text-primary-600 hover:text-primary-700 font-medium"
                :disabled="isLoading"
              >
                player@example.com
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  /**
   * Login Page Component
   * User authentication form with validation and demo accounts
   */
  
  import { ref, computed, onMounted } from 'vue'
  import { 
    TrophyIcon,
    EnvelopeIcon,
    EyeIcon,
    EyeSlashIcon,
    ExclamationTriangleIcon
  } from '@heroicons/vue/24/outline'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'Login',
    components: {
      TrophyIcon,
      EnvelopeIcon,
      EyeIcon,
      EyeSlashIcon,
      ExclamationTriangleIcon
    },
    setup() {
      const authStore = useAuthStore()
      
      // Form state
      const form = ref({
        email: '',
        password: '',
        remember: false
      })
  
      // UI state
      const showPassword = ref(false)
      const isLoading = ref(false)
      const errors = ref({})
      const generalError = ref('')
  
      // Computed properties
      const isFormValid = computed(() => {
        return form.value.email && form.value.password && !isLoading.value
      })
  
      /**
       * Handle form submission
       */
      const handleLogin = async () => {
        // Clear previous errors
        errors.value = {}
        generalError.value = ''
        
        // Basic validation
        if (!form.value.email) {
          errors.value.email = 'Email is required'
          return
        }
        
        if (!form.value.password) {
          errors.value.password = 'Password is required'
          return
        }
  
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(form.value.email)) {
          errors.value.email = 'Please enter a valid email address'
          return
        }
  
        isLoading.value = true
  
        try {
          const result = await authStore.login({
            email: form.value.email,
            password: form.value.password
          })
  
          if (!result.success) {
            generalError.value = result.error || 'Login failed'
          }
          // Success case is handled by the store (redirect)
        } catch (error) {
          console.error('Login error:', error)
          generalError.value = 'An unexpected error occurred'
        } finally {
          isLoading.value = false
        }
      }
  
      /**
       * Fill form with demo credentials
       */
      const fillDemoCredentials = (type) => {
        const credentials = {
          admin: { email: 'admin@example.com', password: 'password123' },
          manager: { email: 'manager@example.com', password: 'password123' },
          player: { email: 'player@example.com', password: 'password123' }
        }
  
        if (credentials[type]) {
          form.value.email = credentials[type].email
          form.value.password = credentials[type].password
        }
      }
  
      /**
       * Clear errors when user starts typing
       */
      const clearErrors = () => {
        errors.value = {}
        generalError.value = ''
      }
  
      // Watch for form changes to clear errors
      onMounted(() => {
        // Clear any previous auth errors
        authStore.clearError()
      })
  
      return {
        form,
        showPassword,
        isLoading,
        errors,
        generalError,
        isFormValid,
        handleLogin,
        fillDemoCredentials,
        clearErrors
      }
    }
  }
  </script>