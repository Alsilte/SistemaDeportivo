<template>
    <div class="dashboard">
      <!-- Header del dashboard -->
      <div class="dashboard-header">
        <h1>¡Bienvenido, {{ authStore.userName }}!</h1>
        <p class="dashboard-subtitle">
          Panel de control - {{ userRoleLabel }}
        </p>
      </div>
      
      <!-- Estadísticas rápidas -->
      <div class="stats-grid">
        <div class="stat-card" v-for="stat in statsCards" :key="stat.label">
          <div class="stat-icon" :style="{ backgroundColor: stat.color }">
            {{ stat.icon }}
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
            <div class="stat-change" :class="stat.changeClass">
              {{ stat.change }}
            </div>
          </div>
        </div>
      </div>
      
      <!-- Contenido específico por rol -->
      <div class="dashboard-content">
        <!-- Dashboard para Administradores -->
        <div v-if="authStore.isAdmin" class="admin-dashboard">
          <div class="dashboard-row">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Acciones Rápidas</h3>
              </div>
              <div class="card-body">
                <div class="quick-actions">
                  <router-link to="/torneos/crear" class="action-btn">
                    <span class="action-icon">🏆</span>
                    <span>Crear Torneo</span>
                  </router-link>
                  <router-link to="/equipos/crear" class="action-btn">
                    <span class="action-icon">👥</span>
                    <span>Crear Equipo</span>
                  </router-link>
                  <router-link to="/usuarios" class="action-btn">
                    <span class="action-icon">👤</span>
                    <span>Gestionar Usuarios</span>
                  </router-link>
                  <button @click="generateFixtures" class="action-btn" :disabled="loading">
                    <span class="action-icon">📅</span>
                    <span>Generar Calendarios</span>
                  </button>
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Actividad Reciente</h3>
              </div>
              <div class="card-body">
                <div class="activity-list" v-if="recentActivity.length > 0">
                  <div class="activity-item" v-for="activity in recentActivity" :key="activity.id">
                    <div class="activity-icon">{{ activity.icon }}</div>
                    <div class="activity-content">
                      <div class="activity-text">{{ activity.text }}</div>
                      <div class="activity-time">{{ activity.time }}</div>
                    </div>
                  </div>
                </div>
                
                <div v-else class="no-activity">
                  <p>No hay actividad reciente</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Gráficos y estadísticas adicionales -->
          <div class="dashboard-row">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Torneos por Estado</h3>
              </div>
              <div class="card-body">
                <div class="chart-container">
                  <div class="chart-item" v-for="item in torneosChart" :key="item.label">
                    <div class="chart-bar">
                      <div class="chart-fill" 
                           :style="{ width: item.percentage + '%', backgroundColor: item.color }">
                      </div>
                    </div>
                    <div class="chart-label">
                      <span>{{ item.label }}</span>
                      <span class="chart-value">{{ item.value }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Próximos Partidos</h3>
              </div>
              <div class="card-body">
                <div class="matches-list" v-if="upcomingMatches.length > 0">
                  <div class="match-item" v-for="match in upcomingMatches" :key="match.id">
                    <div class="match-date">{{ formatDate(match.fecha) }}</div>
                    <div class="match-info">
                      <div class="match-teams">{{ match.teams }}</div>
                      <div class="match-tournament">{{ match.tournament }}</div>
                    </div>
                    <router-link :to="`/partidos/${match.id}`" class="match-link">
                      Ver
                    </router-link>
                  </div>
                </div>
                
                <div v-else class="no-matches">
                  <p>No hay partidos programados</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Dashboard para Jugadores -->
        <div v-else-if="authStore.isJugador" class="player-dashboard">
          <div class="dashboard-row">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Mis Equipos</h3>
              </div>
              <div class="card-body">
                <div class="team-list" v-if="userTeams.length > 0">
                  <div class="team-item" v-for="team in userTeams" :key="team.id">
                    <div class="team-logo">{{ team.logo || '🏆' }}</div>
                    <div class="team-info">
                      <div class="team-name">{{ team.name }}</div>
                      <div class="team-sport">{{ team.sport }}</div>
                      <div class="team-position">{{ team.position }}</div>
                    </div>
                    <router-link :to="`/equipos/${team.id}`" class="team-link">
                      Ver
                    </router-link>
                  </div>
                </div>
                
                <div v-else class="no-teams">
                  <p>No perteneces a ningún equipo aún</p>
                  <router-link to="/equipos" class="btn btn-primary btn-sm">
                    Explorar Equipos
                  </router-link>
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Mis Estadísticas</h3>
              </div>
              <div class="card-body">
                <div class="player-stats">
                  <div class="stat-item">
                    <div class="stat-number">{{ playerStats.partidos_jugados }}</div>
                    <div class="stat-label">Partidos Jugados</div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-number">{{ playerStats.goles_favor }}</div>
                    <div class="stat-label">Goles</div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-number">{{ playerStats.asistencias || 0 }}</div>
                    <div class="stat-label">Asistencias</div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-number">{{ playerStats.efectividad }}%</div>
                    <div class="stat-label">Efectividad</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Dashboard para Árbitros -->
        <div v-else-if="authStore.isArbitro" class="referee-dashboard">
          <div class="dashboard-row">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Mis Asignaciones</h3>
              </div>
              <div class="card-body">
                <div class="assignments-list" v-if="refereeAssignments.length > 0">
                  <div class="assignment-item" v-for="assignment in refereeAssignments" :key="assignment.id">
                    <div class="assignment-date">{{ formatDate(assignment.fecha) }}</div>
                    <div class="assignment-info">
                      <div class="assignment-match">{{ assignment.match }}</div>
                      <div class="assignment-location">{{ assignment.lugar }}</div>
                    </div>
                    <div class="assignment-status" :class="assignment.estado">
                      {{ getStatusLabel(assignment.estado) }}
                    </div>
                  </div>
                </div>
                
                <div v-else class="no-assignments">
                  <p>No tienes partidos asignados</p>
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Mis Estadísticas</h3>
              </div>
              <div class="card-body">
                <div class="referee-stats">
                  <div class="stat-item">
                    <div class="stat-number">{{ refereeStats.totalMatches }}</div>
                    <div class="stat-label">Partidos Arbitrados</div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-number">{{ refereeStats.thisMonth }}</div>
                    <div class="stat-label">Este Mes</div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-number">{{ refereeStats.averagePerMonth }}</div>
                    <div class="stat-label">Promedio Mensual</div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-number">{{ refereeStats.experience }}</div>
                    <div class="stat-label">Años de Experiencia</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, onMounted } from 'vue'
  import { useAuthStore } from '@/stores/auth'
  import { useToast } from 'vue-toastification'
  import { api } from '@/services/api'
  
  export default {
    name: 'Dashboard',
    setup() {
      const authStore = useAuthStore()
      const toast = useToast()
      
      // Estado reactivo
      const loading = ref(false)
      const stats = ref({
        torneos: 0,
        equipos: 0,
        partidos: 0,
        usuarios: 0
      })
      
      const recentActivity = ref([])
      const userTeams = ref([])
      const upcomingMatches = ref([])
      const refereeAssignments = ref([])
      const refereeStats = ref({
        totalMatches: 0,
        thisMonth: 0,
        averagePerMonth: 0,
        experience: 0
      })
      const playerStats = ref({
        partidos_jugados: 0,
        goles_favor: 0,
        asistencias: 0,
        efectividad: 0
      })
      
      // Computadas
      const userRoleLabel = computed(() => {
        const roles = {
          'administrador': 'Administrador',
          'jugador': 'Jugador',
          'arbitro': 'Árbitro'
        }
        
        return roles[authStore.userRole] || 'Usuario'
      })
      
      const statsCards = computed(() => [
        {
          icon: '🏆',
          label: 'Torneos Activos',
          value: stats.value.torneos,
          change: '+2 este mes',
          changeClass: 'positive',
          color: '#3b82f6'
        },
        {
          icon: '👥',
          label: 'Equipos',
          value: stats.value.equipos,
          change: '+5 nuevos',
          changeClass: 'positive',
          color: '#10b981'
        },
        {
          icon: '⚽',
          label: 'Partidos Hoy',
          value: stats.value.partidos,
          change: '3 programados',
          changeClass: 'neutral',
          color: '#f59e0b'
        },
        {
          icon: '👤',
          label: 'Usuarios Activos',
          value: stats.value.usuarios,
          change: '+12 esta semana',
          changeClass: 'positive',
          color: '#8b5cf6'
        }
      ])
      
      const torneosChart = computed(() => [
        { label: 'Activos', value: 5, percentage: 50, color: '#10b981' },
        { label: 'Planificación', value: 3, percentage: 30, color: '#3b82f6' },
        { label: 'Finalizados', value: 2, percentage: 20, color: '#6b7280' }
      ])
      
      // Métodos
      const loadDashboardData = async () => {
        loading.value = true
        
        try {
          // Cargar datos según el rol del usuario
          if (authStore.isAdmin) {
            await loadAdminData()
          } else if (authStore.isJugador) {
            await loadPlayerData()
          } else if (authStore.isArbitro) {
            await loadRefereeData()
          }
          
          // Cargar estadísticas generales
          await loadGeneralStats()
          
        } catch (error) {
          console.error('Error loading dashboard data:', error)
          toast.error('Error al cargar los datos del dashboard')
        } finally {
          loading.value = false
        }
      }
      
      const loadGeneralStats = async () => {
        try {
          // Simular datos (en producción vendrían de la API)
          stats.value = {
            torneos: 5,
            equipos: 12,
            partidos: 3,
            usuarios: 45
          }
        } catch (error) {
          console.error('Error loading general stats:', error)
        }
      }
      
      const loadAdminData = async () => {
        try {
          // Actividad reciente
          recentActivity.value = [
            {
              id: 1,
              icon: '🏆',
              text: 'Se creó el torneo "Liga de Primavera 2025"',
              time: 'Hace 2 horas'
            },
            {
              id: 2,
              icon: '👥',
              text: 'Se registró el equipo "Águilas FC"',
              time: 'Hace 5 horas'
            },
            {
              id: 3,
              icon: '⚽',
              text: 'Partido finalizado: Real Madrid 2-1 Barcelona',
              time: 'Ayer'
            },
            {
              id: 4,
              icon: '👤',
              text: 'Nuevo usuario registrado: Juan Pérez',
              time: 'Hace 3 días'
            }
          ]
          
          // Próximos partidos
          upcomingMatches.value = [
            {
              id: 1,
              fecha: new Date(Date.now() + 86400000), // Mañana
              teams: 'FC Barcelona vs Real Madrid',
              tournament: 'Liga Española'
            },
            {
              id: 2,
              fecha: new Date(Date.now() + 172800000), // Pasado mañana
              teams: 'Atlético vs Sevilla',
              tournament: 'Copa del Rey'
            }
          ]
          
        } catch (error) {
          console.error('Error loading admin data:', error)
        }
      }
      
      const loadPlayerData = async () => {
        try {
          // Equipos del jugador
          userTeams.value = [
            {
              id: 1,
              name: 'FC Barcelona',
              sport: 'Fútbol',
              logo: '🔵',
              position: 'Delantero'
            }
          ]
          
          // Estadísticas del jugador
          if (authStore.user?.jugador) {
            playerStats.value = {
              partidos_jugados: authStore.user.jugador.partidos_jugados || 0,
              goles_favor: authStore.user.jugador.goles_favor || 0,
              asistencias: 0, // Se puede agregar este campo
              efectividad: authStore.user.jugador.partidos_jugados > 0 
                ? Math.round((authStore.user.jugador.ganados / authStore.user.jugador.partidos_jugados) * 100)
                : 0
            }
          }
          
        } catch (error) {
          console.error('Error loading player data:', error)
        }
      }
      
      const loadRefereeData = async () => {
        try {
          // Asignaciones del árbitro
          refereeAssignments.value = [
            {
              id: 1,
              fecha: new Date(Date.now() + 86400000),
              match: 'Atlético vs Sevilla',
              lugar: 'Estadio Metropolitano',
              estado: 'programado'
            }
          ]
          
          // Estadísticas del árbitro
          if (authStore.user?.arbitro) {
            refereeStats.value = {
              totalMatches: authStore.user.arbitro.partidos_arbitrados || 0,
              thisMonth: 4,
              averagePerMonth: 3,
              experience: 2
            }
          }
          
        } catch (error) {
          console.error('Error loading referee data:', error)
        }
      }
      
      const generateFixtures = async () => {
        loading.value = true
        
        try {
          toast.info('Generando calendarios de partidos...')
          
          // Simular proceso de generación
          await new Promise(resolve => setTimeout(resolve, 2000))
          
          toast.success('Calendarios generados exitosamente')
          
          // Recargar datos
          await loadDashboardData()
          
        } catch (error) {
          console.error('Error generating fixtures:', error)
          toast.error('Error al generar calendarios')
        } finally {
          loading.value = false
        }
      }
      
      const formatDate = (date) => {
        if (!date) return ''
        
        const d = new Date(date)
        const today = new Date()
        const tomorrow = new Date(today)
        tomorrow.setDate(tomorrow.getDate() + 1)
        
        if (d.toDateString() === today.toDateString()) {
          return `Hoy ${d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}`
        } else if (d.toDateString() === tomorrow.toDateString()) {
          return `Mañana ${d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}`
        } else {
          return d.toLocaleDateString('es-ES', { 
            weekday: 'short', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          })
        }
      }
      
      const getStatusLabel = (status) => {
        const labels = {
          'programado': 'Programado',
          'en_curso': 'En Curso',
          'finalizado': 'Finalizado',
          'suspendido': 'Suspendido',
          'cancelado': 'Cancelado'
        }
        return labels[status] || status
      }
      
      // Ciclo de vida
      onMounted(() => {
        loadDashboardData()
      })
      
      return {
        authStore,
        loading,
        stats,
        statsCards,
        torneosChart,
        recentActivity,
        userTeams,
        upcomingMatches,
        refereeAssignments,
        refereeStats,
        playerStats,
        userRoleLabel,
        generateFixtures,
        formatDate,
        getStatusLabel
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .dashboard {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .dashboard-header {
    margin-bottom: 2rem;
    
    h1 {
      font-size: 2rem;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 0.5rem;
    }
    
    .dashboard-subtitle {
      color: #6b7280;
      font-size: 1rem;
    }
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .stat-icon {
      font-size: 1.5rem;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.75rem;
      color: white;
    }
    
    .stat-content {
      flex: 1;
      
      .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
      }
      
      .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
      }
      
      .stat-change {
        font-size: 0.75rem;
        
        &.positive {
          color: #10b981;
        }
        
        &.negative {
          color: #ef4444;
        }
        
        &.neutral {
          color: #6b7280;
        }
      }
    }
  }
  
  .dashboard-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    
    .card-header {
      padding: 1.5rem 1.5rem 0;
      
      .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
      }
    }
    
    .card-body {
      padding: 1.5rem;
    }
  }
  
  .quick-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  
  .action-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    text-decoration: none;
    color: #374151;
    transition: all 0.2s;
    cursor: pointer;
    
    &:hover:not(:disabled) {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
      transform: translateY(-1px);
    }
    
    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    .action-icon {
      font-size: 1.25rem;
    }
    
    span:last-child {
      font-weight: 500;
      font-size: 0.875rem;
    }
  }
  
  .activity-list, .team-list, .matches-list, .assignments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 300px;
    overflow-y: auto;
  }
  
  .activity-item, .team-item, .match-item, .assignment-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    transition: background-color 0.2s;
    
    &:hover {
      background: #f3f4f6;
    }
  }
  
  .activity-icon, .team-logo {
    font-size: 1.25rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    flex-shrink: 0;
  }
  
  .activity-content, .team-info, .match-info, .assignment-info {
    flex: 1;
    
    > div:first-child {
      font-weight: 500;
      color: #1f2937;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
    }
    
    > div:last-child {
      font-size: 0.75rem;
      color: #6b7280;
    }
  }
  
  .match-date, .assignment-date {
    font-size: 0.75rem;
    font-weight: 600;
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    white-space: nowrap;
    flex-shrink: 0;
  }
  
  .match-link, .team-link {
    background: #e5e7eb;
    color: #374151;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s;
    
    &:hover {
      background: #3b82f6;
      color: white;
    }
  }
  
  .assignment-status {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    
    &.programado {
      background: #dbeafe;
      color: #1d4ed8;
    }
    
    &.en_curso {
      background: #dcfce7;
      color: #166534;
    }
    
    &.finalizado {
      background: #f3f4f6;
      color: #374151;
    }
  }
  
  .referee-stats, .player-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    
    .stat-item {
      text-align: center;
      padding: 1rem;
      background: #f9fafb;
      border-radius: 0.5rem;
      
      .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #3b82f6;
        margin-bottom: 0.25rem;
      }
      
      .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
      }
    }
  }
  
  .chart-container {
    space-y: 1rem;
  }
  
  .chart-item {
    margin-bottom: 1rem;
    
    .chart-bar {
      width: 100%;
      height: 8px;
      background: #f3f4f6;
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 0.5rem;
      
      .chart-fill {
        height: 100%;
        transition: width 0.3s ease;
      }
    }
    
    .chart-label {
      display: flex;
      justify-content: space-between;
      font-size: 0.875rem;
      
      .chart-value {
        font-weight: 600;
        color: #374151;
      }
    }
  }
  
  .no-activity, .no-teams, .no-matches, .no-assignments {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    
    p {
      margin: 0 0 1rem 0;
      font-style: italic;
    }
  }
  
  .btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    
    &.btn-primary {
      background: #3b82f6;
      color: white;
      
      &:hover {
        background: #2563eb;
      }
    }
    
    &.btn-sm {
      padding: 0.375rem 0.75rem;
      font-size: 0.75rem;
    }
  }
  
  // Responsive
  @media (max-width: 768px) {
    .dashboard-row {
      grid-template-columns: 1fr;
    }
    
    .stats-grid {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .quick-actions {
      grid-template-columns: 1fr;
    }
    
    .referee-stats, .player-stats {
      grid-template-columns: 1fr;
    }
    
    .dashboard-header h1 {
      font-size: 1.5rem;
    }
  }
  </style>