<template>
    <!-- Mobile Navigation Overlay -->
    <Teleport to="body">
      <Transition name="mobile-overlay">
        <div v-if="isOpen" class="fixed inset-0 z-50 md:hidden">
          <!-- Backdrop -->
          <div 
            class="fixed inset-0 bg-black bg-opacity-50" 
            @click="$emit('close')"
          ></div>
          
          <!-- Navigation Panel -->
          <Transition name="mobile-panel">
            <div v-if="isOpen" class="fixed inset-y-0 right-0 max-w-xs w-full bg-white shadow-xl">
              <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                  <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                      <TrophyIcon class="w-5 h-5 text-white" />
                    </div>
                    <span class="font-bold text-lg text-gray-900">
                      Tournament Manager
                    </span>
                  </div>
                  <button
                    @click="$emit('close')"
                    class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                  >
                    <XMarkIcon class="w-6 h-6" />
                  </button>
                </div>
  
                <!-- Navigation Content -->
                <div class="flex-1 overflow-y-auto">
                  <!-- User Info (if authenticated) -->
                  <div v-if="authStore.isAuthenticated" class="p-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                      <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                        <UserIcon class="w-6 h-6 text-primary-600" />
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ authStore.userName }}</p>
                        <p class="text-xs text-gray-500">{{ authStore.userEmail }}</p>
                        <p class="text-xs text-primary-600 capitalize mt-1">{{ authStore.userRole }}</p>
                      </div>
                    </div>
                  </div>
  
                  <!-- Navigation Items -->
                  <nav class="py-4">
                    <!-- Public Navigation -->
                    <div class="space-y-1">
                      <RouterLink
                        v-for="item in publicNavItems"
                        :key="item.name"
                        :to="item.to"
                        @click="$emit('close')"
                        class="mobile-nav-link"
                        active-class="mobile-nav-link-active"
                      >
                        <component :is="item.icon" class="w-5 h-5 mr-3" />
                        {{ item.name }}
                      </RouterLink>
                    </div>
  
                    <!-- Authenticated User Navigation -->
                    <div v-if="authStore.isAuthenticated" class="mt-6 pt-6 border-t border-gray-200">
                      <div class="px-4 pb-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</h3>
                      </div>
                      <div class="space-y-1">
                        <RouterLink
                          v-for="item in userNavItems"
                          :key="item.name"
                          :to="item.to"
                          @click="$emit('close')"
                          class="mobile-nav-link"
                          active-class="mobile-nav-link-active"
                        >
                          <component :is="item.icon" class="w-5 h-5 mr-3" />
                          {{ item.name }}
                        </RouterLink>
                      </div>
                    </div>
  
                    <!-- Admin Navigation -->
                    <div v-if="authStore.isAdmin" class="mt-6 pt-6 border-t border-gray-200">
                      <div class="px-4 pb-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</h3>
                      </div>
                      <div class="space-y-1">
                        <RouterLink
                          v-for="item in adminNavItems"
                          :key="item.name"
                          :to="item.to"
                          @click="$emit('close')"
                          class="mobile-nav-link"
                          active-class="mobile-nav-link-active"
                        >
                          <component :is="item.icon" class="w-5 h-5 mr-3" />
                          {{ item.name }}
                        </RouterLink>
                      </div>
                    </div>
  
                    <!-- Team Manager Navigation -->
                    <div v-if="authStore.canManageTeams" class="mt-6 pt-6 border-t border-gray-200">
                      <div class="px-4 pb-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</h3>
                      </div>
                      <div class="space-y-1">
                        <RouterLink
                          v-for="item in managerNavItems"
                          :key="item.name"
                          :to="item.to"
                          @click="$emit('close')"
                          class="mobile-nav-link"
                          active-class="mobile-nav-link-active"
                        >
                          <component :is="item.icon" class="w-5 h-5 mr-3" />
                          {{ item.name }}
                        </RouterLink>
                      </div>
                    </div>
                  </nav>
                </div>
  
                <!-- Bottom Actions -->
                <div class="border-t border-gray-200 p-4">
                  <div v-if="!authStore.isAuthenticated" class="space-y-2">
                    <RouterLink
                      to="/login"
                      @click="$emit('close')"
                      class="block w-full text-center btn-secondary"
                    >
                      Sign In
                    </RouterLink>
                    <RouterLink
                      to="/register"
                      @click="$emit('close')"
                      class="block w-full text-center btn-primary"
                    >
                      Sign Up
                    </RouterLink>
                  </div>
                  <div v-else>
                    <button
                      @click="handleLogout"
                      class="flex items-center w-full text-left p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                      <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3 text-gray-400" />
                      Sign Out
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </template>
  
  <script>
  /**
   * Mobile Navigation Component
   * Responsive mobile navigation panel with slide-out animation
   */
  
  import {
    TrophyIcon,
    UserIcon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    HomeIcon,
    CalendarIcon,
    UserGroupIcon,
    UsersIcon,
    PlayIcon,
    CogIcon,
    ChartBarIcon,
    PlusIcon,
    ShieldCheckIcon,
    AdjustmentsHorizontalIcon
  } from '@heroicons/vue/24/outline'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'MobileNavigation',
    components: {
      TrophyIcon,
      UserIcon,
      XMarkIcon,
      ArrowRightOnRectangleIcon,
      HomeIcon,
      CalendarIcon,
      UserGroupIcon,
      UsersIcon,
      PlayIcon,
      CogIcon,
      ChartBarIcon,
      PlusIcon,
      ShieldCheckIcon,
      AdjustmentsHorizontalIcon
    },
    props: {
      isOpen: {
        type: Boolean,
        default: false
      }
    },
    emits: ['close'],
    setup(props, { emit }) {
      const authStore = useAuthStore()
  
      // Navigation items
      const publicNavItems = [
        { name: 'Home', to: '/', icon: HomeIcon },
        { name: 'Tournaments', to: '/tournaments', icon: CalendarIcon },
        { name: 'Teams', to: '/teams', icon: UserGroupIcon },
        { name: 'Players', to: '/players', icon: UsersIcon },
        { name: 'Matches', to: '/matches', icon: PlayIcon }
      ]
  
      const userNavItems = [
        { name: 'Dashboard', to: '/dashboard', icon: ChartBarIcon },
        { name: 'Profile', to: '/profile', icon: UserIcon },
        { name: 'Settings', to: '/settings', icon: CogIcon }
      ]
  
      const adminNavItems = [
        { name: 'Admin Panel', to: '/admin', icon: ShieldCheckIcon },
        { name: 'User Management', to: '/admin/users', icon: UsersIcon },
        { name: 'Create Tournament', to: '/tournaments/create', icon: PlusIcon }
      ]
  
      const managerNavItems = [
        { name: 'Create Team', to: '/teams/create', icon: PlusIcon },
        { name: 'Manage Teams', to: '/teams/manage', icon: AdjustmentsHorizontalIcon }
      ]
  
      const handleLogout = async () => {
        await authStore.logout()
        emit('close')
      }
  
      return {
        authStore,
        publicNavItems,
        userNavItems,
        adminNavItems,
        managerNavItems,
        handleLogout
      }
    }
  }
  </script>
  
  <style scoped>
  .mobile-nav-link {
    @apply flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors;
  }
  
  .mobile-nav-link-active {
    @apply bg-primary-50 text-primary-600 border-r-2 border-primary-600;
  }
  
  /* Transitions */
  .mobile-overlay-enter-active,
  .mobile-overlay-leave-active {
    transition: opacity 0.3s ease;
  }
  
  .mobile-overlay-enter-from,
  .mobile-overlay-leave-to {
    opacity: 0;
  }
  
  .mobile-panel-enter-active,
  .mobile-panel-leave-active {
    transition: transform 0.3s ease;
  }
  
  .mobile-panel-enter-from,
  .mobile-panel-leave-to {
    transform: translateX(100%);
  }
    </style>