/**
 * Main application entry point
 * Initializes Vue app with router, store, and global configurations
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

// Import global styles
import './assets/main.css'

// Create Vue application instance
const app = createApp(App)

// Install plugins
app.use(createPinia())
app.use(router)

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Global error:', err)
  console.error('Component info:', info)
}

// Mount the application
app.mount('#app')