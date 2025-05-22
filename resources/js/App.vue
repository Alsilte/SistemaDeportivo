<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
import { useAuthStore } from './stores/auth'
import { onMounted } from 'vue'

export default {
  name: 'App',
  setup() {
    const authStore = useAuthStore()
    
    // Verificar si hay token guardado al iniciar la app
    onMounted(() => {
      const token = localStorage.getItem('auth_token')
      const userData = localStorage.getItem('user_data')
      
      if (token && userData) {
        authStore.setToken(token)
        authStore.setUser(JSON.parse(userData))
      }
    })
    
    return {}
  }
}
</script>

<style lang="scss">
// Los estilos globales ya están importados en main.scss
// Aquí solo estilos específicos del componente App si son necesarios
</style>