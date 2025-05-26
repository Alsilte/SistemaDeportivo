import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  // Estado
  const isLoading = ref(false)
  const isOnline = ref(navigator.onLine)
  const currentUser = ref(null)
  const sidebarCollapsed = ref(false)
  const notifications = ref([])
  
  // Métodos
  const setLoading = (value) => {
    isLoading.value = value
  }
  
  const setCurrentUser = (user) => {
    currentUser.value = user
  }
  
  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }
  
  const addNotification = (notification) => {
    notifications.value.push({
      id: Date.now(),
      timestamp: new Date(),
      ...notification
    })
  }
  
  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  const clearNotifications = () => {
    notifications.value = []
  }
  
  // Detectar cambios de conectividad
  const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine
  }
  
  // Inicializar
  const initialize = () => {
    // Escuchar eventos de conectividad
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)
    
    // Verificar estado inicial
    updateOnlineStatus()
  }
  
  // Limpiar
  const cleanup = () => {
    window.removeEventListener('online', updateOnlineStatus)
    window.removeEventListener('offline', updateOnlineStatus)
  }
  
  // Computadas
  const hasNotifications = computed(() => notifications.value.length > 0)
  const unreadNotificationsCount = computed(() => {
    return notifications.value.filter(n => !n.read).length
  })
  
  return {
    // Estado
    isLoading,
    isOnline,
    currentUser,
    sidebarCollapsed,
    notifications,
    
    // Computadas
    hasNotifications,
    unreadNotificationsCount,
    
    // Métodos
    setLoading,
    setCurrentUser,
    toggleSidebar,
    addNotification,
    removeNotification,
    clearNotifications,
    updateOnlineStatus,
    initialize,
    cleanup
  }
})