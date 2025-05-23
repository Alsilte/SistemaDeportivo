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
      <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.torneos || 0 }}</div>
          <div class="stat-label">Torneos Activos</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.equipos || 0 }}</div>
          <div class="stat-label">Equipos</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⚽</div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.partidos || 0 }}</div>
          <div class="stat-label">Partidos Hoy</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.usuarios || 0 }}</div>
          <div class="stat-label">Usuarios Activos</div>
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
              </div>
            </div>
          </div>
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Actividad Reciente</h3>
            </div>
            <div class="card-body">
              <div class="activity-list">
                <div class="activity-item" v-for="activity in recentActivity" :key="activity.id">
                  <div class="activity-icon">{{ activity.icon }}</div>
                  <div class="activity-content">
                    <div class="activity-text">{{ activity.text }}</div>
                    <div class="activity-time">{{ activity.time }}</div>
                  </div>
                </div>
                
                <div v-if="recentActivity.length === 0" class="no-activity">
                  <p>No hay actividad reciente</p>
                </div>
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
              <div class="team-list">
                <div class="team-item" v-for="team in userTeams" :key="team.id">
                  <div class="team-logo">{{ team.logo || '🏆' }}</div>
                  <div class="team-info">
                    <div class="team-name">{{ team.name }}</div>
                    <div class="team-sport">{{ team.sport }}</div>
                  </div>
                </div>
                
                <div v-if="userTeams.length === 0" class="no-teams">
                  <p>No perteneces a ningún equipo aún</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Próximos Partidos</h3>
            </div>
            <div class="card-body">
              <div class="matches-list">
                <div class="match-item" v-for="match in upcomingMatches" :key="match.id">
                  <div class="match-date">{{ match.date }}</div>
                  <div class="match-info">
                    <div class="match-teams">{{ match.teams }}</div>
                    <div class="match-tournament">{{ match.tournament }}</div>
                  </div>
                </div>
                
                <div v-if="upcomingMatches.length === 0" class="no-matches">
                  <p>No hay partidos programados</p>
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
              <div class="assignments-list">
                <div class="assignment-item" v-for="assignment in refereeAssignments" :key="assignment.id">
                  <div class="assignment-date">{{ assignment.date }}</div>
                  <div class="assignment-info">
                    <div class="assignment-match">{{ assignment.match }}</div>
                    <div class="assignment-location">{{ assignment.location }}</div>
                  </div>
                </div>
                
                <div v-if="refereeAssignments.length === 0" class="no-assignments">
                  <p>No tienes partidos asignados</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Estadísticas</h3>
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
import { useAuthStore } from '../stores/auth'

export default {
  name: 'Dashboard',
  setup() {
    const authStore = useAuthStore()
    
    // Estado reactivo
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
      thisMonth: 0
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
    
    // Métodos
    const loadDashboardStats = async () => {
      try {
        // Simular datos para el desarrollo
        // En producción, esto vendría de la API
        stats.value = {
          torneos: 5,
          equipos: 12,
          partidos: 3,
          usuarios: 45
        }
        
        // Actividad reciente de ejemplo
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
          }
        ]
        
        // Datos específicos por rol
        if (authStore.isJugador) {
          userTeams.value = [
            {
              id: 1,
              name: 'FC Barcelona',
              sport: 'Fútbol',
              logo: '🔵'
            }
          ]
          
          upcomingMatches.value = [
            {
              id: 1,
              date: '25 May',
              teams: 'FC Barcelona vs Real Madrid',
              tournament: 'Liga Española'
            }
          ]
        }
        
        if (authStore.isArbitro) {
          refereeAssignments.value = [
            {
              id: 1,
              date: '24 May 16:00',
              match: 'Atlético vs Sevilla',
              location: 'Estadio Metropolitano'
            }
          ]
          
          refereeStats.value = {
            totalMatches: 25,
            thisMonth: 4
          }
        }
      } catch (error) {
        console.error('Error loading dashboard stats:', error)
      }
    }
    
    // Ciclo de vida
    onMounted(() => {
      loadDashboardStats()
    })
    
    return {
      authStore,
      stats,
      recentActivity,
      userTeams,
      upcomingMatches,
      refereeAssignments,
      refereeStats,
      userRoleLabel
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
  margin-bottom: var(--spacing-xl);
  
  h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: var(--spacing-xs);
  }
  
  .dashboard-subtitle {
    color: var(--gray-600);
    font-size: 1rem;
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
}

.stat-card {
  background: white;
  padding: var(--spacing-lg);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  
  .stat-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-color);
    border-radius: var(--border-radius);
    color: white;
  }
  
  .stat-content {
    flex: 1;
    
    .stat-number {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--gray-800);
      margin-bottom: var(--spacing-xs);
    }
    
    .stat-label {
      color: var(--gray-600);
      font-size: 0.875rem;
    }
  }
}

.dashboard-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-lg);
}

.quick-actions {
  display: grid;
  gap: var(--spacing-md);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  background: var(--gray-50);
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius);
  text-decoration: none;
  color: var(--gray-700);
  transition: all var(--transition-fast);
  
  &:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }
  
  .action-icon {
    font-size: 1.25rem;
  }
  
  span:last-child {
    font-weight: 500;
  }
}

.activity-list, .team-list, .matches-list, .assignments-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.activity-item, .team-item, .match-item, .assignment-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  background: var(--gray-50);
  border-radius: var(--border-radius);
}

.activity-icon, .team-logo {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary-color);
  color: white;
  border-radius: 50%;
}

.activity-content, .team-info, .match-info, .assignment-info {
  flex: 1;
  
  > div:first-child {
    font-weight: 500;
    color: var(--gray-800);
    margin-bottom: 2px;
  }
  
  > div:last-child {
    font-size: 0.75rem;
    color: var(--gray-500);
  }
}

.match-date, .assignment-date {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--primary-color);
  background: var(--primary-color);
  color: white;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--border-radius-sm);
  white-space: nowrap;
}

.referee-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-lg);
  
  .stat-item {
    text-align: center;
    
    .stat-number {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: var(--spacing-xs);
    }
    
    .stat-label {
      font-size: 0.875rem;
      color: var(--gray-600);
    }
  }
}

.no-activity, .no-teams, .no-matches, .no-assignments {
  text-align: center;
  padding: var(--spacing-xl);
  color: var(--gray-500);
  
  p {
    margin: 0;
    font-style: italic;
  }
}

// Responsive
@media (max-width: 768px) {
  .dashboard-row {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .dashboard-header h1 {
    font-size: 1.5rem;
  }
}
</style>