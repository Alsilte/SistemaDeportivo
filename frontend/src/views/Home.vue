<template>
  <MainLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white overflow-hidden">
      <div class="absolute inset-0 bg-black opacity-10"></div>
      <div class="relative container-custom py-24 lg:py-32">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6 text-balance">
            Complete Tournament Management
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-primary-100 text-balance">
            Organize, manage, and track sports tournaments with our comprehensive platform
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <RouterLink 
              v-if="!authStore.isAuthenticated"
              to="/register" 
              class="btn-primary bg-white text-primary-600 hover:bg-gray-100 px-8 py-4 text-lg"
            >
              Get Started Free
            </RouterLink>
            <RouterLink 
              v-else
              to="/dashboard" 
              class="btn-primary bg-white text-primary-600 hover:bg-gray-100 px-8 py-4 text-lg"
            >
              Go to Dashboard
            </RouterLink>
            <RouterLink 
              to="/tournaments" 
              class="btn-secondary bg-transparent border-2 border-white text-white hover:bg-white hover:text-primary-600 px-8 py-4 text-lg"
            >
              View Tournaments
            </RouterLink>
          </div>
        </div>
      </div>
      <!-- Decorative elements -->
      <div class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2">
        <div class="w-96 h-96 bg-primary-500 rounded-full opacity-20"></div>
      </div>
      <div class="absolute bottom-0 left-0 transform -translate-x-1/2 translate-y-1/2">
        <div class="w-96 h-96 bg-primary-500 rounded-full opacity-20"></div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-white">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Everything You Need
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            From tournament creation to match results, manage every aspect of your sports tournaments
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div 
            v-for="feature in features" 
            :key="feature.title"
            class="card p-8 text-center hover:shadow-card-hover transition-shadow"
          >
            <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <component :is="feature.icon" class="w-8 h-8 text-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-24 bg-gray-50">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Platform Statistics
          </h2>
          <p class="text-xl text-gray-600">
            Join thousands of tournament organizers and sports enthusiasts
          </p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div 
            v-for="stat in stats" 
            :key="stat.label"
            class="text-center"
          >
            <div class="text-4xl md:text-5xl font-bold text-primary-600 mb-2">
              {{ stat.value }}
            </div>
            <div class="text-gray-600 font-medium">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Tournaments -->
    <section class="py-24 bg-white">
      <div class="container-custom">
        <div class="flex justify-between items-center mb-12">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Recent Tournaments</h2>
            <p class="text-gray-600">Check out the latest tournaments on our platform</p>
          </div>
          <RouterLink to="/tournaments" class="btn-primary">
            View All Tournaments
          </RouterLink>
        </div>

        <!-- Loading State -->
        <div v-if="isLoadingTournaments" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="card p-6">
            <div class="animate-pulse">
              <div class="h-4 bg-gray-200 rounded mb-4"></div>
              <div class="h-3 bg-gray-200 rounded mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-3/4"></div>
            </div>
          </div>
        </div>

        <!-- Tournaments Grid -->
        <div v-else-if="recentTournaments.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <TournamentCard 
            v-for="tournament in recentTournaments" 
            :key="tournament.id"
            :tournament="tournament"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <CalendarIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No tournaments yet</h3>
          <p class="text-gray-600 mb-6">Be the first to create a tournament on our platform</p>
          <RouterLink 
            v-if="authStore.isAdmin"
            to="/tournaments/create" 
            class="btn-primary"
          >
            Create Tournament
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-primary-600">
      <div class="container-custom text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
          Ready to Get Started?
        </h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
          Join our platform today and start managing your tournaments like a pro
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <RouterLink 
            v-if="!authStore.isAuthenticated"
            to="/register" 
            class="btn-primary bg-white text-primary-600 hover:bg-gray-100 px-8 py-4 text-lg"
          >
            Sign Up Now
          </RouterLink>
          <RouterLink 
            to="/tournaments" 
            class="btn-secondary bg-transparent border-2 border-white text-white hover:bg-white hover:text-primary-600 px-8 py-4 text-lg"
          >
            Browse Tournaments
          </RouterLink>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script>
/**
 * Home Page Component
 * Landing page with hero section, features, stats, and recent tournaments
 */

import { ref, onMounted } from 'vue'
import { 
  CalendarIcon, 
  UserGroupIcon, 
  ChartBarIcon, 
  CogIcon,
  TrophyIcon,
  PlayIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'
import TournamentCard from '@/components/tournaments/TournamentCard.vue'

export default {
  name: 'Home',
  components: {
    MainLayout,
    TournamentCard,
    CalendarIcon,
    UserGroupIcon,
    ChartBarIcon,
    CogIcon,
    TrophyIcon,
    PlayIcon
  },
  setup() {
    const authStore = useAuthStore()
    const recentTournaments = ref([])
    const isLoadingTournaments = ref(false)

    // Platform features
    const features = [
      {
        title: 'Tournament Management',
        description: 'Create and manage tournaments with flexible formats including leagues, knockouts, and group stages.',
        icon: CalendarIcon
      },
      {
        title: 'Team Organization',
        description: 'Register teams, manage player rosters, and track team statistics throughout tournaments.',
        icon: UserGroupIcon
      },
      {
        title: 'Match Scheduling',
        description: 'Schedule matches, assign referees, and track results with our comprehensive match system.',
        icon: PlayIcon
      },
      {
        title: 'Live Statistics',
        description: 'Real-time standings, player statistics, and tournament analytics at your fingertips.',
        icon: ChartBarIcon
      },
      {
        title: 'User Management',
        description: 'Role-based access control for administrators, team managers, players, and referees.',
        icon: CogIcon
      },
      {
        title: 'Awards & Recognition',
        description: 'Track achievements, awards, and recognition for outstanding performance in tournaments.',
        icon: TrophyIcon
      }
    ]

    // Platform statistics (mock data - would come from API in real app)
    const stats = [
      { value: '150+', label: 'Active Tournaments' },
      { value: '500+', label: 'Registered Teams' },
      { value: '2,000+', label: 'Players' },
      { value: '1,200+', label: 'Matches Played' }
    ]

    /**
     * Fetch recent tournaments
     */
    const fetchRecentTournaments = async () => {
      isLoadingTournaments.value = true
      try {
        const response = await tournamentAPI.getAll({ 
          per_page: 6,
          status: 'registration_open,in_progress'
        })
        recentTournaments.value = response.data.data || []
      } catch (error) {
        console.error('Failed to fetch recent tournaments:', error)
        recentTournaments.value = []
      } finally {
        isLoadingTournaments.value = false
      }
    }

    onMounted(() => {
      fetchRecentTournaments()
    })

    return {
      authStore,
      features,
      stats,
      recentTournaments,
      isLoadingTournaments
    }
  }
}
</script>