<template>
    <div class="space-y-8">
      <!-- System Overview -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">System Overview</h2>
          <button 
            @click="refreshData"
            :disabled="isLoading"
            class="btn-secondary flex items-center"
          >
            <ArrowPathIcon :class="['w-4 h-4 mr-2', isLoading ? 'animate-spin' : '']" />
            Refresh
          </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center p-4 bg-primary-50 rounded-lg">
            <CalendarIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-primary-600">{{ systemStats.totalTournaments }}</div>
            <div class="text-sm text-gray-600">Total Tournaments</div>
          </div>
          <div class="text-center p-4 bg-success-50 rounded-lg">
            <UserGroupIcon class="w-8 h-8 text-success-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-success-600">{{ systemStats.totalTeams }}</div>
            <div class="text-sm text-gray-600">Registered Teams</div>
          </div>
          <div class="text-center p-4 bg-warning-50 rounded-lg">
            <UsersIcon class="w-8 h-8 text-warning-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-warning-600">{{ systemStats.totalUsers }}</div>
            <div class="text-sm text-gray-600">Active Users</div>
          </div>
        </div>
      </div>
  
      <!-- Recent Tournaments -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Recent Tournaments</h2>
          <RouterLink to="/tournaments/create" class="btn-primary">
            <PlusIcon class="w-4 h-4 mr-2" />
            Create Tournament
          </RouterLink>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tournament
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Teams
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Start Date
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="tournament in recentTournaments" :key="tournament.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ tournament.name }}</div>
                  <div class="text-sm text-gray-500">{{ tournament.sport_type }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(tournament.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ formatStatus(tournament.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ tournament.registered_teams_count || 0 }}/{{ tournament.max_teams }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(tournament.start_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <RouterLink 
                    :to="`/tournaments/${tournament.id}`"
                    class="text-primary-600 hover:text-primary-900 mr-3"
                  >
                    View
                  </RouterLink>
                  <button 
                    @click="editTournament(tournament.id)"
                    class="text-secondary-600 hover:text-secondary-900"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  
      <!-- Pending Actions -->
      <div class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Pending Actions</h2>
        
        <div class="space-y-4">
          <div 
            v-for="action in pendingActions" 
            :key="action.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center mr-4',
                action.bgColor
              ]">
                <component :is="action.icon" :class="['w-5 h-5', action.iconColor]" />
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900">{{ action.title }}</div>
                <div class="text-sm text-gray-500">{{ action.description }}</div>
              </div>
            </div>
            <div class="flex space-x-2">
              <button 
                v-if="action.type === 'team_registration'"
                @click="approveTeamRegistration(action.id)"
                class="btn-success text-xs px-3 py-1"
              >
                Approve
              </button>
              <button 
                v-if="action.type === 'team_registration'"
                @click="rejectTeamRegistration(action.id)"
                class="btn-danger text-xs px-3 py-1"
              >
                Reject
              </button>
              <button 
                v-if="action.type === 'match_result'"
                @click="reviewMatchResult(action.id)"
                class="btn-primary text-xs px-3 py-1"
              >
                Review
              </button>
            </div>
          </div>
          
          <div v-if="pendingActions.length === 0" class="text-center py-8 text-gray-500">
            <CheckCircleIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <p>No pending actions</p>
          </div>
        </div>
      </div>
  
      <!-- System Health -->
      <div class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">System Health</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Database Status</h3>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-success-500 rounded-full mr-2"></div>
              <span class="text-sm text-gray-600">Operational</span>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">API Status</h3>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-success-500 rounded-full mr-2"></div>
              <span class="text-sm text-gray-600">Operational</span>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Active Sessions</h3>
            <div class="text-sm text-gray-600">{{ systemStats.activeSessions }} users online</div>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Last Backup</h3>
            <div class="text-sm text-gray-600">2 hours ago</div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  /**
   * Admin Dashboard Component
   * Provides comprehensive system overview and management tools for administrators
   */
  
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import {
    CalendarIcon,
    UserGroupIcon,
    UsersIcon,
    PlusIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon
  } from '@heroicons/vue/24/outline'
  import { tournamentAPI, apiHelpers } from '@/services/api'
  
  export default {
    name: 'AdminDashboard',
    components: {
      CalendarIcon,
      UserGroupIcon,
      UsersIcon,
      PlusIcon,
      ArrowPathIcon,
      CheckCircleIcon,
      ExclamationTriangleIcon,
      ClockIcon
    },
    setup() {
      const router = useRouter()
      const isLoading = ref(false)
      
      // System statistics
      const systemStats = ref({
        totalTournaments: 0,
        totalTeams: 0,
        totalUsers: 0,
        activeSessions: 0
      })
  
      // Recent tournaments
      const recentTournaments = ref([])
  
      // Pending actions that need admin attention
      const pendingActions = ref([
        {
          id: 1,
          type: 'team_registration',
          title: 'Team Registration Pending',
          description: 'Eagles FC wants to join Spring League',
          icon: UserGroupIcon,
          bgColor: 'bg-warning-100',
          iconColor: 'text-warning-600'
        },
        {
          id: 2,
          type: 'match_result',
          title: 'Match Result Dispute',
          description: 'Lions vs Tigers match result needs review',
          icon: ExclamationTriangleIcon,
          bgColor: 'bg-danger-100',
          iconColor: 'text-danger-600'
        },
        {
          id: 3,
          type: 'tournament_approval',
          title: 'Tournament Approval',
          description: 'Winter Championship needs final approval',
          icon: CalendarIcon,
          bgColor: 'bg-primary-100',
          iconColor: 'text-primary-600'
        }
      ])
  
      /**
       * Fetch dashboard data
       */
      const fetchDashboardData = async () => {
        isLoading.value = true
        try {
          // Fetch recent tournaments
          const tournamentResponse = await tournamentAPI.getAll({ per_page: 5 })
          if (apiHelpers.isSuccess(tournamentResponse)) {
            recentTournaments.value = apiHelpers.getData(tournamentResponse).data || []
          }
  
          // Mock system stats (would come from dedicated admin API)
          systemStats.value = {
            totalTournaments: 45,
            totalTeams: 180,
            totalUsers: 850,
            activeSessions: 23
          }
        } catch (error) {
          console.error('Failed to fetch dashboard data:', error)
          window.$notify?.error('Failed to load dashboard data')
        } finally {
          isLoading.value = false
        }
      }
  
      /**
       * Refresh dashboard data
       */
      const refreshData = () => {
        fetchDashboardData()
      }
  
      /**
       * Navigate to edit tournament
       */
      const editTournament = (tournamentId) => {
        router.push(`/tournaments/${tournamentId}/edit`)
      }
  
      /**
       * Handle team registration approval
       */
      const approveTeamRegistration = (actionId) => {
        // Remove from pending actions (mock implementation)
        pendingActions.value = pendingActions.value.filter(action => action.id !== actionId)
        window.$notify?.success('Team registration approved')
      }
  
      /**
       * Handle team registration rejection
       */
      const rejectTeamRegistration = (actionId) => {
        // Remove from pending actions (mock implementation)
        pendingActions.value = pendingActions.value.filter(action => action.id !== actionId)
        window.$notify?.warning('Team registration rejected')
      }
  
      /**
       * Handle match result review
       */
      const reviewMatchResult = (actionId) => {
        // Navigate to match review page
        router.push(`/admin/matches/review/${actionId}`)
      }
  
      /**
       * Format tournament status for display
       */
      const formatStatus = (status) => {
        const statusMap = {
          'draft': 'Draft',
          'registration_open': 'Registration Open',
          'in_progress': 'In Progress',
          'completed': 'Completed',
          'cancelled': 'Cancelled'
        }
        return statusMap[status] || status
      }
  
      /**
       * Get CSS classes for status badge
       */
      const getStatusBadgeClass = (status) => {
        const classMap = {
          'draft': 'bg-gray-100 text-gray-800',
          'registration_open': 'bg-success-100 text-success-800',
          'in_progress': 'bg-primary-100 text-primary-800',
          'completed': 'bg-secondary-100 text-secondary-800',
          'cancelled': 'bg-danger-100 text-danger-800'
        }
        return classMap[status] || classMap.draft
      }
  
      /**
       * Format date for display
       */
      const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      }
  
      // Initialize dashboard
      onMounted(() => {
        fetchDashboardData()
      })
  
      return {
        isLoading,
        systemStats,
        recentTournaments,
        pendingActions,
        refreshData,
        editTournament,
        approveTeamRegistration,
        rejectTeamRegistration,
        reviewMatchResult,
        formatStatus,
        getStatusBadgeClass,
        formatDate
      }
    }
  }
  </script>