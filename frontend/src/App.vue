<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Loading overlay -->
    <Transition name="fade">
      <div 
        v-if="isLoading" 
        class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50"
      >
        <div class="text-center">
          <div class="spinner w-8 h-8 mx-auto mb-4"></div>
          <p class="text-gray-600">Loading...</p>
        </div>
      </div>
    </Transition>

    <!-- Main application content -->
    <RouterView />
    
    <!-- Global notification system -->
    <NotificationSystem />
  </div>
</template>

<script>
/**
 * Root App component
 * Handles global state, loading, and notification system
 */

import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import NotificationSystem from '@/components/ui/NotificationSystem.vue'

export default {
  name: 'App',
  components: {
    NotificationSystem
  },
  setup() {
    const authStore = useAuthStore()
    
    // Initialize authentication state
    authStore.initializeAuth()
    
    // Global loading state
    const isLoading = computed(() => authStore.isLoading)
    
    return {
      isLoading
    }
  }
}
</script>

<style scoped>
/**
 * Transition animations
 */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>