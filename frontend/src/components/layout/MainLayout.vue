<template>
    <div class="min-h-screen bg-gray-50">
      <!-- Navigation Header -->
      <NavigationHeader />
      
      <!-- Mobile Navigation Overlay -->
      <MobileNavigation :isOpen="isMobileMenuOpen" @close="isMobileMenuOpen = false" />
      
      <!-- Main Content -->
      <main class="relative">
        <!-- Page Header (if provided) -->
        <div v-if="$slots.header" class="bg-white shadow-sm border-b border-gray-200">
          <div class="container-custom py-6">
            <slot name="header" />
          </div>
        </div>
        
        <!-- Page Content -->
        <div class="container-custom py-8">
          <slot />
        </div>
      </main>
      
      <!-- Footer -->
      <AppFooter />
    </div>
  </template>
  
  <script>
  /**
   * Main Layout Component
   * Provides consistent layout structure for authenticated and public pages
   */
  
  import { ref, provide } from 'vue'
  import NavigationHeader from './NavigationHeader.vue'
  import MobileNavigation from './MobileNavigation.vue'
  import AppFooter from './AppFooter.vue'
  
  export default {
    name: 'MainLayout',
    components: {
      NavigationHeader,
      MobileNavigation,
      AppFooter
    },
    setup() {
      const isMobileMenuOpen = ref(false)
      
      // Provide mobile menu state to child components
      provide('mobileMenu', {
        isOpen: isMobileMenuOpen,
        toggle: () => isMobileMenuOpen.value = !isMobileMenuOpen.value,
        close: () => isMobileMenuOpen.value = false
      })
      
      return {
        isMobileMenuOpen
      }
    }
  }
  </script>