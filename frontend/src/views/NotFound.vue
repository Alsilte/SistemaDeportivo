<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center px-4">
      <div class="text-center max-w-md">
        <!-- 404 Illustration -->
        <div class="mb-8">
          <div class="w-32 h-32 mx-auto bg-primary-100 rounded-full flex items-center justify-center mb-6">
            <ExclamationTriangleIcon class="w-16 h-16 text-primary-600" />
          </div>
          <h1 class="text-6xl font-bold text-gray-900 mb-2">404</h1>
          <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
          <p class="text-gray-600 mb-8">
            The page you're looking for doesn't exist or has been moved.
          </p>
        </div>
  
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button
            @click="goBack"
            class="btn-secondary flex items-center justify-center"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Go Back
          </button>
          <RouterLink to="/" class="btn-primary flex items-center justify-center">
            <HomeIcon class="w-4 h-4 mr-2" />
            Go Home
          </RouterLink>
        </div>
  
        <!-- Helpful Links -->
        <div class="mt-12 pt-8 border-t border-gray-200">
          <p class="text-sm text-gray-600 mb-4">You might be looking for:</p>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <RouterLink 
              to="/tournaments" 
              class="text-primary-600 hover:text-primary-700 transition-colors"
            >
              Tournaments
            </RouterLink>
            <RouterLink 
              to="/teams" 
              class="text-primary-600 hover:text-primary-700 transition-colors"
            >
              Teams
            </RouterLink>
            <RouterLink 
              to="/players" 
              class="text-primary-600 hover:text-primary-700 transition-colors"
            >
              Players
            </RouterLink>
            <RouterLink 
              to="/matches" 
              class="text-primary-600 hover:text-primary-700 transition-colors"
            >
              Matches
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  /**
   * 404 Not Found Page Component
   * Displayed when user navigates to non-existent route
   */
  
  import { useRouter } from 'vue-router'
  import {
    ExclamationTriangleIcon,
    ArrowLeftIcon,
    HomeIcon
  } from '@heroicons/vue/24/outline'
  
  export default {
    name: 'NotFound',
    components: {
      ExclamationTriangleIcon,
      ArrowLeftIcon,
      HomeIcon
    },
    setup() {
      const router = useRouter()
  
      const goBack = () => {
        if (window.history.length > 1) {
          router.go(-1)
        } else {
          router.push('/')
        }
      }
  
      return {
        goBack
      }
    }
  }
  </script>