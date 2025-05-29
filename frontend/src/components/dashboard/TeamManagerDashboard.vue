<template>
    <div class="space-y-8">
      <!-- My Teams Overview -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">My Teams</h2>
          <RouterLink to="/teams/create" class="btn-primary">
            <PlusIcon class="w-4 h-4 mr-2" />
            Create Team
          </RouterLink>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="team in managedTeams" 
            :key="team.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold text-gray-900">{{ team.name }}</h3>
              <span :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                team.is_active ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800'
              ]">
                {{ team.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-600 mb-4">
              <div class="flex items-center">
                <UsersIcon class="w-4 h-4 mr-2" />
                <span>{{ team.players_count || 0 }} players</span>
              </div>
              <div class="flex items-center">
                <CalendarIcon class="w-4 h-4 mr-2" />
                <span>{{ team.tournaments_count || 0 }} tournaments</span>
              </div>
              <div class="flex items-center">
                <TrophyIcon class="w-4 h-4 mr-2" />
                <span>{{ team.wins || 0 }} wins</span>
              </div>
            </div>
            
            <div class="flex space-x-2">
              <RouterLink 
                :to="`/teams/${team.id}`"
                class="btn-primary text-xs px-3 py-1 flex-1 text-center"
              >
                View
              </RouterLink>
              <RouterLink 
                :to="`/teams/${team.id}/roster`"
                class="btn-secondary text-xs px-3 py-1 flex-1 text-center"
              >
                Roster
              </RouterLink>
            </div>
          </div>
          
          <!-- Create new team card -->
          <RouterLink 
            to="/teams/create"
            class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-primary-300 hover:bg-primary-50 transition-colors flex flex-col items-center justify-center text-center"
          >
            <PlusIcon class="w-8 h-8 text-gray-400 mb-2" />
            <span class="text-sm font-medium text-gray-600">Create New Team</span>
          </RouterLink>
        </div>
      </div>
  
      <!-- Upcoming Matches -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Upcoming Matches</h2>
          <RouterLink to="/matches" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            View All
          </RouterLink>
        </div>
        
        <div class="space-y-4">
          <div 
            v-for="match in upcomingMatches" 
            :key="match.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-4">
              <div class="text-center">
                <div class="text-sm font-medium text-gray-900">{{ formatMatchDate(match.match_date) }}</div>
                <div class="text-xs text-gray-500">{{ formatMatchTime(match.match_date) }}</div>
              </div>
              <div class="flex items-center space-x-3">
                <div class="text-right">
                  <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                  <div class="text-xs text-gray-500">Home</div>
                </div>
                <div class="text-gray-400 font-bold">VS</div>
                <div class="text-left">
                  <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                  <div class="text-xs text-gray-500">Away</div>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div v-if="match.venue" class="text-sm text-gray-600">{{ match.venue }}</div>
              <div class="text-xs text-gray-500">{{ match.tournament?.name }}</div>
            </div>
          </div>
          
          <div v-if="upcomingMatches.length === 0" class="text-center py-8 text-gray-500">
            <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <p>No upcoming matches</p>
          </div>
        </div>
      </div>
  
      <!-- Player Management -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Player Management</h2>
          <button 
            @click="showPlayerSearch = true"
            class="btn-secondary"
          >
            <MagnifyingGlassIcon class="w-4 h-4 mr-2" />
            Find Players
          </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Recent Player Additions -->
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Additions</h3>
            <div class="space-y-3">
              <div 
                v-for="player in recentPlayers" 
                :key="player.id"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                    <UserIcon class="w-4 h-4 text-primary-600" />
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ player.user?.name }}</div>
                    <div class="text-xs text-gray-500">{{ player.position || 'No position' }}</div>
                  </div>
                </div>
                <div class="text-xs text-gray-500">{{ player.team?.name }}</div>
              </div>
            </div>
          </div>
          
          <!-- Player Statistics -->
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Player Stats</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Total Players</span>
                <span class="text-sm font-medium text-gray-900">{{ playerStats.total }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Active Players</span>
                <span class="text-sm font-medium text-gray-900">{{ playerStats.active }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Top Scorer</span>
                <span class="text-sm font-medium text-gray-900">{{ playerStats.topScorer }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Most Active</span>
                <span class="text-sm font-medium text-gray-900">{{ playerStats.mostActive }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Tournament Registrations -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Tournament Registrations</h2>
          <RouterLink to="/tournaments" class="btn-primary">
            <CalendarIcon class="w-4 h-4 mr-2" />
            Browse Tournaments
          </RouterLink>
        </div>
        
        <div class="space-y-4">
          <div 
            v-for="registration in tournamentRegistrations" 
            :key="registration.id"
            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
          >
            <div>
              <div class="font-medium text-gray-900">{{ registration.tournament?.name }}</div>
              <div class="text-sm text-gray-600">Team: {{ registration.team?.name }}</div>
              <div class="text-xs text-gray-500">
                Registered: {{ formatDate(registration.registration_date) }}
              </div>
            </div>
            <div class="text-right">
              <span :class="getRegistrationStatusClass(registration.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatRegistrationStatus(registration.status) }}
              </span>
              <div class="text-xs text-gray-500 mt-1">
                {{ registration.tournament?.start_date ? formatDate(registration.tournament.start_date) : 'TBD' }}
              </div>
            </div>
          </div>
          
          <div v-if="tournamentRegistrations.length === 0" class="text-center py-8 text-gray-500">
            <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <p>No tournament registrations</p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  /**
   * Team Manager Dashboard Component
   * Provides team management tools and overview for team managers
   */
  
  import { ref, onMounted } from 'vue'
  import {
    PlusIcon,
    UsersIcon,
    CalendarIcon,
    TrophyIcon,
    UserIcon,
    MagnifyingGlassIcon
  } from '@heroicons/vue/24/outline'
  import { teamAPI, matchAPI, apiHelpers } from '@/services/api'
  
  export default {
    name: 'TeamManagerDashboard',
    components: {
      PlusIcon,
      UsersIcon,
      CalendarIcon,
      TrophyIcon,
      UserIcon,
      MagnifyingGlassIcon
    },
    setup() {
      const showPlayerSearch = ref(false)
      
      // Data
      const managedTeams = ref([])
      const upcomingMatches = ref([])
      const recentPlayers = ref([])
      const tournamentRegistrations = ref([])
      
      // Mock player statistics
      const playerStats = ref({
        total: 45,
        active: 42,
        topScorer: 'John Smith (12 goals)',
        mostActive: 'Mike Johnson (8 matches)'
      })
  
      /**
       * Fetch dashboard data
       */
      const fetchDashboardData = async () => {
        try {
          // Fetch managed teams (mock implementation)
          managedTeams.value = [
            {
              id: 1,
              name: 'Eagles FC',
              is_active: true,
              players_count: 15,
              tournaments_count: 3,
              wins: 8
            },
            {
              id: 2,
              name: 'Lions United',
              is_active: true,
              players_count: 18,
              tournaments_count: 2,
              wins: 12
            }
          ]
  
          // Fetch upcoming matches
          const matchResponse = await matchAPI.getAll({ 
            per_page: 5,
            status: 'scheduled'
          })
          if (apiHelpers.isSuccess(matchResponse)) {
            upcomingMatches.value = apiHelpers.getData(matchResponse).data || []
          }
  
          // Mock recent players and registrations
          recentPlayers.value = [
            {
              id: 1,
              user: { name: 'Alex Rodriguez' },
              position: 'Forward',
              team: { name: 'Eagles FC' }
            },
            {
              id: 2,
              user: { name: 'Sarah Wilson' },
              position: 'Midfielder',
              team: { name: 'Lions United' }
            }
          ]
  
          tournamentRegistrations.value = [
            {
              id: 1,
              tournament: { name: 'Spring League', start_date: '2024-03-15' },
              team: { name: 'Eagles FC' },
              status: 'approved',
              registration_date: '2024-02-20'
            },
            {
              id: 2,
              tournament: { name: 'Summer Cup', start_date: '2024-06-01' },
              team: { name: 'Lions United' },
              status: 'pending',
              registration_date: '2024-02-25'
            }
          ]
        } catch (error) {
          console.error('Failed to fetch dashboard data:', error)
          window.$notify?.error('Failed to load dashboard data')
        }
      }
  
      /**
       * Format match date
       */
      const formatMatchDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        })
      }
  
      /**
       * Format match time
       */
      const formatMatchTime = (dateString) => {
        return new Date(dateString).toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit'
        })
      }
  
      /**
       * Format general date
       */
      const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      }
  
      /**
       * Format registration status
       */
      const formatRegistrationStatus = (status) => {
        const statusMap = {
          'pending': 'Pending',
          'approved': 'Approved',
          'rejected': 'Rejected'
        }
        return statusMap[status] || status
      }
  
      /**
       * Get CSS classes for registration status
       */
      const getRegistrationStatusClass = (status) => {
        const classMap = {
          'pending': 'bg-warning-100 text-warning-800',
          'approved': 'bg-success-100 text-success-800',
          'rejected': 'bg-danger-100 text-danger-800'
        }
        return classMap[status] || classMap.pending
      }
  
      // Initialize dashboard
      onMounted(() => {
        fetchDashboardData()
      })
  
      return {
        showPlayerSearch,
        managedTeams,
        upcomingMatches,
        recentPlayers,
        tournamentRegistrations,
        playerStats,
        formatMatchDate,
        formatMatchTime,
        formatDate,
        formatRegistrationStatus,
        getRegistrationStatusClass
      }
    }
  }
  </script>