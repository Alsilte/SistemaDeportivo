<template>
    <div class="space-y-8">
      <!-- Referee Overview -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Referee Overview</h2>
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-success-500 rounded-full"></div>
            <span class="text-sm text-gray-600">Available for assignments</span>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="text-center p-4 bg-primary-50 rounded-lg">
            <PlayIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-primary-600">{{ refereeStats.totalMatches }}</div>
            <div class="text-sm text-gray-600">Matches Officiated</div>
          </div>
          <div class="text-center p-4 bg-warning-50 rounded-lg">
            <ClockIcon class="w-8 h-8 text-warning-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-warning-600">{{ refereeStats.upcomingMatches }}</div>
            <div class="text-sm text-gray-600">Upcoming Matches</div>
          </div>
          <div class="text-center p-4 bg-success-50 rounded-lg">
            <CheckCircleIcon class="w-8 h-8 text-success-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-success-600">{{ refereeStats.completedMatches }}</div>
            <div class="text-sm text-gray-600">Completed This Month</div>
          </div>
          <div class="text-center p-4 bg-secondary-50 rounded-lg">
            <StarIcon class="w-8 h-8 text-secondary-600 mx-auto mb-2" />
            <div class="text-2xl font-bold text-secondary-600">{{ refereeStats.averageRating }}</div>
            <div class="text-sm text-gray-600">Average Rating</div>
          </div>
        </div>
      </div>
  
      <!-- My Assigned Matches -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">My Assigned Matches</h2>
          <div class="flex space-x-2">
            <button 
              @click="filterMatches('upcoming')"
              :class="[
                'px-3 py-1 text-sm rounded-lg transition-colors',
                matchFilter === 'upcoming' ? 'bg-primary-100 text-primary-700' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              Upcoming
            </button>
            <button 
              @click="filterMatches('completed')"
              :class="[
                'px-3 py-1 text-sm rounded-lg transition-colors',
                matchFilter === 'completed' ? 'bg-primary-100 text-primary-700' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              Completed
            </button>
          </div>
        </div>
        
        <div class="space-y-4">
          <div 
            v-for="match in filteredMatches" 
            :key="match.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-6">
                <div class="text-center">
                  <div class="text-sm font-medium text-gray-900">{{ formatMatchDate(match.match_date) }}</div>
                  <div class="text-xs text-gray-500">{{ formatMatchTime(match.match_date) }}</div>
                </div>
                <div class="flex items-center space-x-4">
                  <div class="text-right">
                    <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                    <div class="text-xs text-gray-500">Home</div>
                  </div>
                  <div class="text-center">
                    <div v-if="match.status === 'completed'" class="text-xl font-bold text-gray-900">
                      {{ match.home_score }} - {{ match.away_score }}
                    </div>
                    <div v-else class="text-gray-400 font-bold">VS</div>
                  </div>
                  <div class="text-left">
                    <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                    <div class="text-xs text-gray-500">Away</div>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div v-if="match.venue" class="text-sm text-gray-600">{{ match.venue }}</div>
                <div class="text-xs text-gray-500 mb-2">{{ match.tournament?.name }}</div>
                <div class="flex space-x-2">
                  <RouterLink 
                    :to="`/matches/${match.id}`"
                    class="btn-secondary text-xs px-3 py-1"
                  >
                    View Details
                  </RouterLink>
                  <button 
                    v-if="match.status === 'in_progress' || match.status === 'scheduled'"
                    @click="manageMatch(match)"
                    class="btn-primary text-xs px-3 py-1"
                  >
                    {{ match.status === 'in_progress' ? 'Manage' : 'Start Match' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="filteredMatches.length === 0" class="text-center py-8 text-gray-500">
            <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <p>No {{ matchFilter }} matches assigned</p>
          </div>
        </div>
      </div>
  
      <!-- Match Management Tools -->
      <div class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Match Management Tools</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <button 
            @click="openEventRecorder"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
              <DocumentPlusIcon class="w-6 h-6 text-primary-600" />
            </div>
            <div class="text-left">
              <div class="font-medium text-gray-900">Record Events</div>
              <div class="text-sm text-gray-600">Log goals, cards, substitutions</div>
            </div>
          </button>
          
          <button 
            @click="openMatchTimer"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center mr-4">
              <ClockIcon class="w-6 h-6 text-success-600" />
            </div>
            <div class="text-left">
              <div class="font-medium text-gray-900">Match Timer</div>
              <div class="text-sm text-gray-600">Track match time and stoppage</div>
            </div>
          </button>
          
          <button 
            @click="viewRefereeReports"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center mr-4">
              <DocumentTextIcon class="w-6 h-6 text-warning-600" />
            </div>
            <div class="text-left">
              <div class="font-medium text-gray-900">Match Reports</div>
              <div class="text-sm text-gray-600">View and submit reports</div>
            </div>
          </button>
          
          <button 
            @click="accessRules"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 bg-secondary-100 rounded-lg flex items-center justify-center mr-4">
              <BookOpenIcon class="w-6 h-6 text-secondary-600" />
            </div>
            <div class="text-left">
              <div class="font-medium text-gray-900">Rules Reference</div>
              <div class="text-sm text-gray-600">Quick access to game rules</div>
            </div>
          </button>
          
          <button 
            @click="contactSupport"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 bg-danger-100 rounded-lg flex items-center justify-center mr-4">
              <PhoneIcon class="w-6 h-6 text-danger-600" />
            </div>
            <div class="text-left">
              <div class="font-medium text-gray-900">Emergency Contact</div>
              <div class="text-sm text-gray-600">Tournament officials hotline</div>
            </div>
          </button>
          
          <button 
            @click="viewSchedule"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
              <CalendarDaysIcon class="w-6 h-6 text-primary-600" />
            </div>
            <div class="text-left">
              <div class="font-medium text-gray-900">My Schedule</div>
              <div class="text-sm text-gray-600">View full referee schedule</div>
            </div>
          </button>
        </div>
      </div>
  
      <!-- Recent Activity -->
      <div class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Recent Activity</h2>
        
        <div class="space-y-4">
          <div 
            v-for="activity in recentActivity" 
            :key="activity.id"
            class="flex items-start space-x-4"
          >
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
              activity.bgColor
            ]">
              <component :is="activity.icon" :class="['w-5 h-5', activity.iconColor]" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-gray-900">{{ activity.title }}</div>
              <div class="text-sm text-gray-600">{{ activity.description }}</div>
              <div class="text-xs text-gray-500 mt-1">{{ activity.time }}</div>
            </div>
            <div v-if="activity.action" class="flex-shrink-0">
              <button 
                @click="activity.action.callback"
                class="text-primary-600 hover:text-primary-700 text-sm font-medium"
              >
                {{ activity.action.text }}
              </button>
            </div>
          </div>
          
          <div v-if="recentActivity.length === 0" class="text-center py-8 text-gray-500">
            <ClockIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <p>No recent activity</p>
          </div>
        </div>
      </div>
  
      <!-- Performance Metrics -->
      <div class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Performance Metrics</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Match Statistics</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Average Cards per Match</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.avgCards }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Average Goals per Match</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.avgGoals }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">On-time Match Starts</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.onTimeRate }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Matches This Season</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.seasonMatches }}</span>
              </div>
            </div>
          </div>
          
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Feedback Summary</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Overall Rating</span>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-900 mr-1">{{ performanceMetrics.rating }}</span>
                  <div class="flex">
                    <StarIcon 
                      v-for="i in 5" 
                      :key="i"
                      :class="[
                        'w-4 h-4',
                        i <= Math.floor(performanceMetrics.rating) ? 'text-warning-400' : 'text-gray-300'
                      ]"
                    />
                  </div>
                </div>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Positive Feedback</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.positiveFeedback }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Consistency Score</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.consistency }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Punctuality Score</span>
                <span class="text-sm font-medium text-gray-900">{{ performanceMetrics.punctuality }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  /**
   * Referee Dashboard Component
   * Provides match management tools and performance metrics for referees
   */
  
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import {
    PlayIcon,
    ClockIcon,
    CheckCircleIcon,
    StarIcon,
    CalendarIcon,
    DocumentPlusIcon,
    DocumentTextIcon,
    BookOpenIcon,
    PhoneIcon,
    CalendarDaysIcon
  } from '@heroicons/vue/24/outline'
  import { matchAPI, apiHelpers } from '@/services/api'
  import { useAuthStore } from '@/stores/auth'
  
  export default {
    name: 'RefereeDashboard',
    components: {
      PlayIcon,
      ClockIcon,
      CheckCircleIcon,
      StarIcon,
      CalendarIcon,
      DocumentPlusIcon,
      DocumentTextIcon,
      BookOpenIcon,
      PhoneIcon,
      CalendarDaysIcon
    },
    setup() {
      const router = useRouter()
      const authStore = useAuthStore()
      const matchFilter = ref('upcoming')
      
      // Referee statistics
      const refereeStats = ref({
        totalMatches: 0,
        upcomingMatches: 0,
        completedMatches: 0,
        averageRating: 0
      })
  
      // Assigned matches
      const assignedMatches = ref([])
  
      // Performance metrics
      const performanceMetrics = ref({
        avgCards: 3.2,
        avgGoals: 2.8,
        onTimeRate: 96,
        seasonMatches: 24,
        rating: 4.3,
        positiveFeedback: 89,
        consistency: 92,
        punctuality: 96
      })
  
      // Recent activity
      const recentActivity = ref([
        {
          id: 1,
          title: 'Match Completed',
          description: 'Eagles FC vs Lions United - Final Score: 2-1',
          time: '2 hours ago',
          icon: CheckCircleIcon,
          bgColor: 'bg-success-100',
          iconColor: 'text-success-600'
        },
        {
          id: 2,
          title: 'New Assignment',
          description: 'Tigers FC vs Panthers - March 25, 3:00 PM',
          time: '1 day ago',
          icon: CalendarIcon,
          bgColor: 'bg-primary-100',
          iconColor: 'text-primary-600',
          action: {
            text: 'Accept',
            callback: () => acceptAssignment(2)
          }
        },
        {
          id: 3,
          title: 'Match Report Submitted',
          description: 'Wolves vs Sharks - Report approved by tournament officials',
          time: '2 days ago',
          icon: DocumentTextIcon,
          bgColor: 'bg-secondary-100',
          iconColor: 'text-secondary-600'
        }
      ])
  
      // Computed properties
      const filteredMatches = computed(() => {
        if (matchFilter.value === 'upcoming') {
          return assignedMatches.value.filter(match => 
            ['scheduled', 'in_progress'].includes(match.status)
          )
        } else {
          return assignedMatches.value.filter(match => 
            match.status === 'completed'
          )
        }
      })
  
      /**
       * Fetch referee dashboard data
       */
      const fetchDashboardData = async () => {
        try {
          // Fetch matches assigned to this referee
          const matchResponse = await matchAPI.getAll({
            referee_id: authStore.user?.id,
            per_page: 20
          })
          
          if (apiHelpers.isSuccess(matchResponse)) {
            assignedMatches.value = apiHelpers.getData(matchResponse).data || []
            
            // Calculate statistics
            const upcoming = assignedMatches.value.filter(m => 
              ['scheduled', 'in_progress'].includes(m.status)
            ).length
            
            const completedThisMonth = assignedMatches.value.filter(m => {
              const matchDate = new Date(m.match_date)
              const now = new Date()
              return m.status === 'completed' && 
                     matchDate.getMonth() === now.getMonth() &&
                     matchDate.getFullYear() === now.getFullYear()
            }).length
  
            refereeStats.value = {
              totalMatches: assignedMatches.value.length,
              upcomingMatches: upcoming,
              completedMatches: completedThisMonth,
              averageRating: 4.3 // Mock data
            }
          }
        } catch (error) {
          console.error('Failed to fetch referee dashboard data:', error)
          window.$notify?.error('Failed to load dashboard data')
        }
      }
  
      /**
       * Filter matches by type
       */
      const filterMatches = (filter) => {
        matchFilter.value = filter
      }
  
      /**
       * Manage match (start/continue)
       */
      const manageMatch = (match) => {
        router.push(`/matches/${match.id}/manage`)
      }
  
      /**
       * Accept assignment
       */
      const acceptAssignment = (activityId) => {
        // Remove from recent activity
        recentActivity.value = recentActivity.value.filter(a => a.id !== activityId)
        window.$notify?.success('Assignment accepted')
      }
  
      /**
       * Open event recorder tool
       */
      const openEventRecorder = () => {
        router.push('/referee/event-recorder')
      }
  
      /**
       * Open match timer tool
       */
      const openMatchTimer = () => {
        router.push('/referee/timer')
      }
  
      /**
       * View referee reports
       */
      const viewRefereeReports = () => {
        router.push('/referee/reports')
      }
  
      /**
       * Access rules reference
       */
      const accessRules = () => {
        router.push('/referee/rules')
      }
  
      /**
       * Contact support
       */
      const contactSupport = () => {
        window.$notify?.info('Emergency hotline: +1-800-REFEREE')
      }
  
      /**
       * View full schedule
       */
      const viewSchedule = () => {
        router.push('/referee/schedule')
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
  
      // Initialize dashboard
      onMounted(() => {
        fetchDashboardData()
      })
  
      return {
        matchFilter,
        refereeStats,
        assignedMatches,
        filteredMatches,
        performanceMetrics,
        recentActivity,
        filterMatches,
        manageMatch,
        acceptAssignment,
        openEventRecorder,
        openMatchTimer,
        viewRefereeReports,
        accessRules,
        contactSupport,
        viewSchedule,
        formatMatchDate,
        formatMatchTime
      }
    }
  }
  </script>