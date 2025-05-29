<template>
    <div class="torneo-show">
      <!-- Cabecera del torneo -->
      <div class="torneo-header">
        <div class="torneo-info">
          <h1>{{ torneo.nombre }}</h1>
          <p class="torneo-descripcion">{{ torneo.descripcion }}</p>
          
          <div class="torneo-meta">
            <span class="meta-item">
              <strong>Deporte:</strong> {{ torneo.deporte?.nombre }}
            </span>
            <span class="meta-item">
              <strong>Formato:</strong> {{ formatoLabel }}
            </span>
            <span class="meta-item">
              <strong>Estado:</strong> 
              <span :class="estadoClass">{{ estadoLabel }}</span>
            </span>
          </div>
        </div>
      </div>
  
      <!-- Fechas del torneo -->
      <div class="card">
        <div class="card-header">
          <h3>Información del Torneo</h3>
        </div>
        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <label>Fecha de Inicio:</label>
              <span>{{ formatearFecha(torneo.fecha_inicio) }}</span>
            </div>
            <div class="info-item">
              <label>Fecha de Fin:</label>
              <span>{{ formatearFecha(torneo.fecha_fin) }}</span>
            </div>
            <div class="info-item">
              <label>Duración:</label>
              <span>{{ calcularDuracion() }} días</span>
            </div>
            <div class="info-item">
              <label>Equipos Inscritos:</label>
              <span>{{ torneo.equipos?.length || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Equipos participantes -->
      <div class="card" v-if="torneo.equipos && torneo.equipos.length > 0">
        <div class="card-header">
          <h3>Equipos Participantes</h3>
        </div>
        <div class="card-body">
          <div class="equipos-grid">
            <div 
              v-for="equipo in torneo.equipos" 
              :key="equipo.id"
              class="equipo-card"
            >
              <div class="equipo-logo">
                <img v-if="equipo.logo" :src="equipo.logo" :alt="equipo.nombre" />
                <span v-else class="logo-placeholder">{{ equipo.nombre.charAt(0) }}</span>
              </div>
              <div class="equipo-info">
                <h4>{{ equipo.nombre }}</h4>
                <span class="estado-participacion">{{ equipo.pivot?.estado_participacion || 'inscrito' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Clasificación actual -->
      <div class="card" v-if="torneo.clasificacion && torneo.clasificacion.length > 0">
        <div class="card-header">
          <h3>Clasificación Actual</h3>
        </div>
        <div class="card-body">
          <div class="clasificacion-list">
            <div 
              v-for="(clasificacion, index) in torneo.clasificacion.slice(0, 5)" 
              :key="clasificacion.equipo_id"
              class="clasificacion-item"
            >
              <div class="posicion">{{ index + 1 }}°</div>
              <div class="equipo-nombre">{{ clasificacion.equipo?.nombre || 'Equipo' }}</div>
              <div class="stats">
                <span class="puntos">{{ clasificacion.puntos }} pts</span>
                <span class="partidos">{{ clasificacion.partidos_jugados }}PJ</span>
                <span class="diferencia">{{ clasificacion.diferencia_goles > 0 ? '+' : '' }}{{ clasificacion.diferencia_goles }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Próximos partidos -->
      <div class="card" v-if="torneo.partidos && proximosPartidos.length > 0">
        <div class="card-header">
          <h3>Próximos Partidos</h3>
        </div>
        <div class="card-body">
          <div class="partidos-list">
            <div 
              v-for="partido in proximosPartidos" 
              :key="partido.id"
              class="partido-item"
            >
              <div class="partido-fecha">{{ formatearFechaHora(partido.fecha) }}</div>
              <div class="partido-equipos">
                {{ partido.equipoLocal?.nombre || 'TBD' }} vs {{ partido.equipoVisitante?.nombre || 'TBD' }}
              </div>
              <div class="partido-lugar" v-if="partido.lugar">{{ partido.lugar }}</div>
              <div class="partido-estado">{{ getEstadoPartido(partido.estado) }}</div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Estadísticas -->
      <div class="card" v-if="torneo.partidos">
        <div class="card-header">
          <h3>Estadísticas del Torneo</h3>
        </div>
        <div class="card-body">
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-number">{{ torneo.partidos?.length || 0 }}</div>
              <div class="stat-label">Total Partidos</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">{{ partidosJugados }}</div>
              <div class="stat-label">Jugados</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">{{ partidosPendientes }}</div>
              <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">{{ progresoTorneo }}%</div>
              <div class="stat-label">Progreso</div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Loading y Error -->
      <div v-if="loading" class="loading-container">
        <div class="loading">Cargando torneo...</div>
      </div>
  
      <div v-if="error" class="error-container">
        <p class="error-message">{{ error }}</p>
        <button @click="cargarTorneo" class="btn btn-primary">Reintentar</button>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  import { torneosAPI } from '@/services/api'
  
  export default {
    name: 'TorneoShow',
    props: {
      id: {
        type: [String, Number],
        default: null
      }
    },
    setup(props) {
      const route = useRoute()
      const torneo = ref({})
      const loading = ref(false)
      const error = ref('')
  
      // Obtener ID del torneo desde props o route
      const torneoId = computed(() => props.id || route.params.id)
  
      // Computadas para datos calculados
      const proximosPartidos = computed(() => {
        if (!torneo.value.partidos) return []
        return torneo.value.partidos
          .filter(p => p.estado === 'programado' && new Date(p.fecha) > new Date())
          .sort((a, b) => new Date(a.fecha) - new Date(b.fecha))
          .slice(0, 5)
      })
  
      const partidosJugados = computed(() => {
        if (!torneo.value.partidos) return 0
        return torneo.value.partidos.filter(p => p.estado === 'finalizado').length
      })
  
      const partidosPendientes = computed(() => {
        if (!torneo.value.partidos) return 0
        return torneo.value.partidos.filter(p => p.estado === 'programado').length
      })
  
      const progresoTorneo = computed(() => {
        const total = torneo.value.partidos?.length || 0
        if (total === 0) return 0
        return Math.round((partidosJugados.value / total) * 100)
      })
  
      // Computadas para formatear datos basadas en los modelos
      const formatoLabel = computed(() => {
        const formatos = {
          'liga': 'Liga',
          'eliminacion': 'Eliminación',
          'grupos': 'Grupos'
        }
        return formatos[torneo.value.formato] || torneo.value.formato
      })
  
      const estadoLabel = computed(() => {
        const estados = {
          'planificacion': 'En Planificación',
          'activo': 'Activo',
          'finalizado': 'Finalizado',
          'cancelado': 'Cancelado'
        }
        return estados[torneo.value.estado] || torneo.value.estado
      })
  
      const estadoClass = computed(() => {
        const clases = {
          'planificacion': 'estado-planificacion',
          'activo': 'estado-activo',
          'finalizado': 'estado-finalizado',
          'cancelado': 'estado-cancelado'
        }
        return clases[torneo.value.estado] || ''
      })
  
      // Métodos
      const cargarTorneo = async () => {
        if (!torneoId.value) return
  
        loading.value = true
        error.value = ''
  
        try {
          const response = await torneosAPI.getById(torneoId.value)
          torneo.value = response.data.data
        } catch (err) {
          console.error('Error al cargar torneo:', err)
          error.value = 'Error al cargar el torneo. Por favor, intenta de nuevo.'
        } finally {
          loading.value = false
        }
      }
  
      const formatearFecha = (fecha) => {
        if (!fecha) return 'No definida'
        
        try {
          return new Date(fecha).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          })
        } catch (err) {
          return fecha
        }
      }
  
      const formatearFechaHora = (fecha) => {
        if (!fecha) return 'No definida'
        
        try {
          return new Date(fecha).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          })
        } catch (err) {
          return fecha
        }
      }
  
      const calcularDuracion = () => {
        if (!torneo.value.fecha_inicio || !torneo.value.fecha_fin) return 0
        const inicio = new Date(torneo.value.fecha_inicio)
        const fin = new Date(torneo.value.fecha_fin)
        const diferencia = fin.getTime() - inicio.getTime()
        return Math.ceil(diferencia / (1000 * 60 * 60 * 24))
      }
  
      const getEstadoPartido = (estado) => {
        const estados = {
          'programado': 'Programado',
          'en_curso': 'En Curso',
          'finalizado': 'Finalizado',
          'suspendido': 'Suspendido',
          'cancelado': 'Cancelado'
        }
        return estados[estado] || estado
      }
  
      // Ciclo de vida
      onMounted(() => {
        cargarTorneo()
      })
  
      return {
        torneo,
        loading,
        error,
        formatoLabel,
        estadoLabel,
        estadoClass,
        proximosPartidos,
        partidosJugados,
        partidosPendientes,
        progresoTorneo,
        cargarTorneo,
        formatearFecha,
        formatearFechaHora,
        calcularDuracion,
        getEstadoPartido
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .torneo-show {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-lg);
  }
  
  .torneo-header {
    margin-bottom: var(--spacing-xl);
    
    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--gray-800);
      margin-bottom: var(--spacing-sm);
    }
    
    .torneo-descripcion {
      font-size: 1.125rem;
      color: var(--gray-600);
      margin-bottom: var(--spacing-md);
      line-height: 1.6;
    }
    
    .torneo-meta {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing-lg);
      
      .meta-item {
        font-size: 0.875rem;
        color: var(--gray-700);
      }
    }
  }
  
  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    
    .info-item {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-xs);
      
      label {
        font-weight: 600;
        color: var(--gray-700);
        font-size: 0.875rem;
      }
      
      span {
        color: var(--gray-800);
        font-size: 1rem;
      }
    }
  }
  
  .equipos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-md);
  }
  
  .equipo-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    background: var(--gray-50);
    
    .equipo-logo {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      overflow: hidden;
      flex-shrink: 0;
      
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      
      .logo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-color);
        color: white;
        font-weight: 600;
        font-size: 1.25rem;
      }
    }
    
    .equipo-info {
      flex: 1;
      
      h4 {
        margin: 0 0 var(--spacing-xs) 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800);
      }
      
      .estado-participacion {
        font-size: 0.75rem;
        color: var(--gray-600);
        text-transform: capitalize;
      }
    }
  }
  
  .clasificacion-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
  }
  
  .clasificacion-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--gray-50);
    border-radius: var(--border-radius);
    
    .posicion {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }
    
    .equipo-nombre {
      flex: 1;
      font-weight: 500;
      color: var(--gray-800);
    }
    
    .stats {
      display: flex;
      gap: var(--spacing-sm);
      align-items: center;
      
      .puntos {
        font-weight: 600;
        color: var(--primary-color);
      }
      
      .partidos, .diferencia {
        font-size: 0.75rem;
        color: var(--gray-600);
      }
    }
  }
  
  .partidos-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
  }
  
  .partido-item {
    padding: var(--spacing-md);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    background: white;
    
    .partido-fecha {
      font-size: 0.875rem;
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: var(--spacing-xs);
    }
    
    .partido-equipos {
      font-weight: 600;
      color: var(--gray-800);
      margin-bottom: var(--spacing-xs);
    }
    
    .partido-lugar {
      font-size: 0.875rem;
      color: var(--gray-600);
    }
    
    .partido-estado {
      font-size: 0.75rem;
      color: var(--primary-color);
      font-weight: 500;
      text-transform: uppercase;
    }
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: var(--spacing-lg);
    
    .stat-item {
      text-align: center;
      
      .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: var(--spacing-xs);
      }
      
      .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
      }
    }
  }
  
  // Estados
  .estado-planificacion {
    color: var(--info-color);
  }
  
  .estado-activo {
    color: var(--success-color);
  }
  
  .estado-finalizado {
    color: var(--gray-600);
  }
  
  .estado-cancelado {
    color: var(--error-color);
  }
  
  // Loading y Error
  .loading-container, .error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-2xl);
    text-align: center;
  }
  
  .error-message {
    color: var(--error-color);
    margin-bottom: var(--spacing-md);
  }
  
  // Responsive
  @media (max-width: 768px) {
    .torneo-show {
      padding: var(--spacing-md);
    }
    
    .torneo-header h1 {
      font-size: 2rem;
    }
    
    .torneo-meta {
      flex-direction: column;
      gap: var(--spacing-sm);
    }
    
    .info-grid {
      grid-template-columns: 1fr;
    }
    
    .equipos-grid {
      grid-template-columns: 1fr;
    }
    
    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  </style>