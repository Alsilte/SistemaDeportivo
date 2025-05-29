<template>
    <div class="card card-hover overflow-hidden">
      <!-- Tournament Image/Header -->
      <div class="relative h-48 bg-gradient-to-br from-primary-500 to-primary-700">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="absolute top-4 left-4">
          <span :class="statusBadgeClasses" class="px-3 py-1 rounded-full text-xs font-medium">
            {{ formatStatus(tournament.status) }}
          </span>
        </div>
        <div class="absolute top-4 right-4">
          <span class="bg-white bg-opacity-20 text-white px-2 py-1 rounded text-xs font-medium">
            {{ tournament.sport_type }}
          </span>
        </div>
        <div class="absolute bottom-4 left-4 right-4">
          <h3 class="text-white text-xl font-bold mb-1 truncate">
            {{ tournament.name }}
          </h3>
          <p class="text-primary-100 text-sm">
            {{ formatTournamentType(tournament.tournament_type) }}
          </p>
        </div>
      </div>
  
      <!-- Tournament Details -->
      <div class="p-6">
        <!-- Description -->
        <p v-if="tournament.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
          {{ tournament.description }}
        </p>
  
        <!-- Tournament Info -->
        <div class="space-y-3 mb-6">
          <!-- Dates -->
          <div class="flex items-center text-sm text-gray-600">
            <CalendarIcon class="w-4 h-4 mr-2 text-gray-400" />
            <span>{{ formatDateRange(tournament.start_date, tournament.end_date) }}</span>
          </div>
  
          <!-- Location -->
          <div v-if="tournament.location" class="flex items-center text-sm text-gray-600">
            <MapPinIcon class="w-4 h-4 mr-2 text-gray-400" />
            <span>{{ tournament.location }}</span>
          </div>
  
          <!-- Teams -->
          <div class="flex items-center text-sm text-gray-600">
            <UserGroupIcon class="w-4 h-4 mr-2 text-gray-400" />
            <span>{{ tournament.registered_teams_count || 0 }}/{{ tournament.max_teams }} teams</span>
          </div>
  
          <!-- Prize Pool -->
          <div v-if="tournament.prize_pool" class="flex items-center text-sm text-gray-600">
            <CurrencyDollarIcon class="w-4 h-4 mr-2 text-gray-400" />
            <span>${{ formatMoney(tournament.prize_pool) }}</span>
          </div>
        </div>
  
        <!-- Progress Bar (for team registration) -->
        <div v-if="tournament.status === 'registration_open'" class="mb-4">
          <div class="flex justify-between text-xs text-gray-600 mb-1">
            <span>Registration Progress</span>
            <span>{{ Math.round(registrationProgress) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-primary-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: `${registrationProgress}%` }"
            ></div>
          </div>
        </div>
  
        <!-- Creator Info -->
        <div v-if="tournament.creator" class="flex items-center text-sm text-gray-600 mb-6">
          <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center mr-2">
            <UserIcon class="w-4 h-4 text-primary-600" />
          </div>
          <span>Created by {{ tournament.creator.name }}</span>
        </div>
  
        <!-- Actions -->
        <div class="flex gap-3">
          <RouterLink 
            :to="{ name: 'tournament-detail', params: { id: tournament.id } }"
            class="btn-primary flex-1 text-center"
          >
            View Details
          </RouterLink>
          
          <button
            v-if="tournament.status === 'registration_open' && canRegisterTeam"
            @click="$emit('register', tournament)"
            class="btn-secondary px-4"
            :disabled="isRegistrationFull"
          >
            <PlusIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
  
      <!-- Registration Status Footer -->
      <div 
        v-if="tournament.status === 'registration_open'" 
        :class="[
          'px-6 py-3 text-center text-sm',
          isRegistrationFull ? 'bg-danger-50 text-danger-700' : 'bg-success-50 text-success-700'
        ]"
      >
        <template v-if="isRegistrationFull">
          <ExclamationTriangleIcon class="w-4 h-4 inline mr-1" />
          Registration Full
        </template>
        <template v-else>
          <CheckCircleIcon class="w-4 h-4 inline mr-1" />
          Registration Open
        </template>
      </div>
    </div>
  </template>
  
  <script>
  /**
   * Tournament Card Component
   * Displays tournament information in a card format
   */
  
  import { computed } from 'vue'
  import {
    CalendarIcon,
    MapPinIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    UserIcon,
    PlusIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon
  } from '@heroicons/vue/24/outline'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'TournamentCard',
    components: {
      CalendarIcon,
      MapPinIcon,
      UserGroupIcon,
      CurrencyDollarIcon,
      UserIcon,
      PlusIcon,
      CheckCircleIcon,
      ExclamationTriangleIcon
    },
    props: {
      tournament: {
        type: Object,
        required: true
      }
    },
    emits: ['register'],
    setup(props) {
      const authStore = useAuthStore()
  
      // Computed properties
      const registrationProgress = computed(() => {
        const registered = props.tournament.registered_teams_count || 0
        const max = props.tournament.max_teams || 1
        return Math.min((registered / max) * 100, 100)
      })
  
      const isRegistrationFull = computed(() => {
        const registered = props.tournament.registered_teams_count || 0
        const max = props.tournament.max_teams || 0
        return registered >= max
      })
  
      const canRegisterTeam = computed(() => {
        return authStore.isAuthenticated && 
               (authStore.isAdmin || authStore.isTeamManager)
      })
  
      const statusBadgeClasses = computed(() => {
        const baseClasses = 'px-3 py-1 rounded-full text-xs font-medium'
        const statusClasses = {
          'draft': 'bg-gray-100 text-gray-800',
          'registration_open': 'bg-success-100 text-success-800',
          'in_progress': 'bg-primary-100 text-primary-800',
          'completed': 'bg-secondary-100 text-secondary-800',
          'cancelled': 'bg-danger-100 text-danger-800'
        }
        return `${baseClasses} ${statusClasses[props.tournament.status] || statusClasses.draft}`
      })
  
      // Helper functions
      const formatStatus = (status) => {
        const statusMap = {
          'draft': 'Draft',
          'registration_open': 'Open',
          'in_progress': 'In Progress',
          'completed': 'Completed',
          'cancelled': 'Cancelled'
        }
        return statusMap[status] || status
      }
  
      const formatTournamentType = (type) => {
        const typeMap = {
          'league': 'League',
          'knockout': 'Knockout',
          'group_knockout': 'Group + Knockout'
        }
        return typeMap[type] || type
      }
  
      const formatDateRange = (startDate, endDate) => {
        const start = new Date(startDate)
        const end = endDate ? new Date(endDate) : null
        
        const options = { month: 'short', day: 'numeric' }
        const startFormatted = start.toLocaleDateString('en-US', options)
        
        if (end && end.getTime() !== start.getTime()) {
          const endFormatted = end.toLocaleDateString('en-US', options)
          return `${startFormatted} - ${endFormatted}`
        }
        
        return startFormatted
      }
  
      const formatMoney = (amount) => {
        return Number(amount).toLocaleString()
      }
  
      return {
        authStore,
        registrationProgress,
        isRegistrationFull,
        canRegisterTeam,
        statusBadgeClasses,
        formatStatus,
        formatTournamentType,
        formatDateRange,
        formatMoney
      }
    }
  }
  </script>
  
  <style scoped>
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  </style>