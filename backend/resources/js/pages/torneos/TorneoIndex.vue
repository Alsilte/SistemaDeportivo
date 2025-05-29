<template>
    <div class="torneo-index">
      <!-- Cabecera -->
      <div class="page-header">
        <div class="header-content">
          <h1>Torneos</h1>
          <p>Gestiona y consulta todos los torneos disponibles</p>
        </div>
        
        <div class="header-actions" v-if="authStore.isAdmin">
          <router-link to="/torneos/crear" class="btn btn-primary">
            <span class="btn-icon">➕</span>
            Crear Torneo
          </router-link>
        </div>
      </div>
  
      <!-- Filtros -->
      <div class="card filtros-card">
        <div class="filtros">
          <!-- Búsqueda -->
          <div class="filtro-grupo">
            <label>Buscar:</label>
            <input 
              type="text" 
              v-model="filtros.buscar"
              placeholder="Buscar por nombre..."
              class="filtro-input"
              @input="aplicarFiltros"
            />
          </div>
  
          <!-- Estado -->
          <div class="filtro-grupo">
            <label>Estado:</label>
            <select v-model="filtros.estado" @change="aplicarFiltros" class="filtro-select">
              <option value="">Todos</option>
              <option value="planificacion">En Planificación</option>
              <option value="activo">Activo</option>
              <option value="finalizado">Finalizado</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
  
          <!-- Formato -->
          <div class="filtro-grupo">
            <label>Formato:</label>
            <select v-model="filtros.formato" @change="aplicarFiltros" class="filtro-select">
              <option value="">Todos</option>
              <option value="liga">Liga</option>
              <option value="eliminacion">Eliminación</option>
              <option value="grupos">Grupos</option>
            </select>
          </div>
  
          <!-- Deporte -->
          <div class="filtro-grupo">
            <label>Deporte:</label>
            <select v-model="filtros.deporte_id" @change="aplicarFiltros" class="filtro-select">
              <option value="">Todos</option>
              <option v-for="deporte in deportes" :key="deporte.id" :value="deporte.id">
                {{ deporte.nombre }}
              </option>
            </select>
          </div>
  
          <!-- Botón limpiar -->
          <button @click="limpiarFiltros" class="btn btn-outline">
            Limpiar Filtros
          </button>
        </div>
      </div>
  
      <!-- Estadísticas rápidas -->
      <div class="stats-cards">
        <div class="stat-card">
          <div class="stat-icon">🏆</div>
          <div class="stat-content">
            <div class="stat-number">{{ estadisticas.total || 0 }}</div>
            <div class="stat-label">Total Torneos</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">🔴</div>
          <div class="stat-content">
            <div class="stat-number">{{ estadisticas.activos || 0 }}</div>
            <div class="stat-label">Activos</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <div class="stat-number">{{ estadisticas.planificacion || 0 }}</div>
            <div class="stat-label">En Planificación</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <div class="stat-number">{{ estadisticas.finalizados || 0 }}</div>
            <div class="stat-label">Finalizados</div>
          </div>
        </div>
      </div>
  
      <!-- Lista de torneos -->
      <div class="torneos-container">
        <div v-if="loading" class="loading-container">
          <div class="loading">Cargando torneos...</div>
        </div>
  
        <div v-else-if="error" class="error-container">
          <p class="error-message">{{ error }}</p>
          <button @click="cargarTorneos" class="btn btn-primary">Reintentar</button>
        </div>
  
        <div v-else-if="torneos.length === 0" class="empty-container">
          <div class="empty-icon">🏆</div>
          <h3>No hay torneos</h3>
          <p>{{ filtrosActivos ? 'No se encontraron torneos con los filtros aplicados.' : 'Aún no hay torneos creados.' }}</p>
          <router-link v-if="authStore.isAdmin && !filtrosActivos" to="/torneos/crear" class="btn btn-primary">
            Crear Primer Torneo
          </router-link>
        </div>
  
        <div v-else class="torneos-grid">
          <div 
            v-for="torneo in torneos" 
            :key="torneo.id"
            class="torneo-card"
            @click="verTorneo(torneo.id)"
          >
            <!-- Estado del torneo -->
            <div class="torneo-estado">
              <span :class="getEstadoClass(torneo.estado)">
                {{ getEstadoLabel(torneo.estado) }}
              </span>
            </div>
  
            <!-- Contenido principal -->
            <div class="torneo-content">
              <h3 class="torneo-nombre">{{ torneo.nombre }}</h3>
              <p class="torneo-descripcion">{{ torneo.descripcion || 'Sin descripción' }}</p>
              
              <div class="torneo-meta">
                <div class="meta-item">
                  <span class="meta-label">Deporte:</span>
                  <span class="meta-value">{{ torneo.deporte?.nombre || 'N/A' }}</span>
                </div>
                <div class="meta-item">
                  <span class="meta-label">Formato:</span>
                  <span class="meta-value">{{ getFormatoLabel(torneo.formato) }}</span>
                </div>
                <div class="meta-item">
                  <span class="meta-label">Equipos:</span>
                  <span class="meta-value">{{ torneo.equipos?.length || 0 }}</span>
                </div>
              </div>
  
              <!-- Fechas -->
              <div class="torneo-fechas">
                <div class="fecha-item">
                  <span class="fecha-label">Inicio:</span>
                  <span class="fecha-value">{{ formatearFecha(torneo.fecha_inicio) }}</span>
                </div>
                <div class="fecha-item">
                  <span class="fecha-label">Fin:</span>
                  <span class="fecha-value">{{ formatearFecha(torneo.fecha_fin) }}</span>
                </div>
              </div>
  
              <!-- Progreso si hay partidos -->
              <div v-if="torneo.partidos && torneo.partidos.length > 0" class="torneo-progreso">
                <div class="progreso-label">
                  Progreso: {{ calcularProgreso(torneo) }}%
                </div>
                <div class="progreso-bar">
                  <div 
                    class="progreso-fill" 
                    :style="{ width: calcularProgreso(torneo) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
  
            <!-- Acciones -->
            <div class="torneo-actions">
              <button 
                @click.stop="verTorneo(torneo.id)" 
                class="btn btn-outline btn-sm"
              >
                Ver Detalles
              </button>
              
              <div v-if="authStore.isAdmin" class="admin-actions">
                <button 
                  @click.stop="editarTorneo(torneo.id)" 
                  class="btn btn-sm"
                  title="Editar"
                >
                  ✏️
                </button>
                <button 
                  @click.stop="confirmarEliminar(torneo)" 
                  class="btn btn-danger btn-sm"
                  title="Eliminar"
                >
                  🗑️
                </button>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Paginación -->
        <div v-if="paginacion.total > paginacion.per_page" class="paginacion">
          <button 
            @click="cambiarPagina(paginacion.current_page - 1)"
            :disabled="paginacion.current_page === 1"
            class="btn btn-outline"
          >
            Anterior
          </button>
          
          <span class="paginacion-info">
            Página {{ paginacion.current_page }} de {{ paginacion.last_page }}
          </span>
          
          <button 
            @click="cambiarPagina(paginacion.current_page + 1)"
            :disabled="paginacion.current_page === paginacion.last_page"
            class="btn btn-outline"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { torneosAPI } from '@/services/api'
  
  export default {
    name: 'TorneoIndex',
    setup() {
      const router = useRouter()
      const authStore = useAuthStore()
      
      // Estado reactivo
      const torneos = ref([])
      const deportes = ref([])
      const loading = ref(false)
      const error = ref('')
      const estadisticas = ref({})
      const paginacion = ref({
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0
      })
  
      // Filtros
      const filtros = reactive({
        buscar: '',
        estado: '',
        formato: '',
        deporte_id: '',
        page: 1
      })
  
      // Computadas
      const filtrosActivos = computed(() => {
        return filtros.buscar || filtros.estado || filtros.formato || filtros.deporte_id
      })
  
      // Métodos
      const cargarTorneos = async () => {
        loading.value = true
        error.value = ''
  
        try {
          const params = { ...filtros }
          // Limpiar parámetros vacíos
          Object.keys(params).forEach(key => {
            if (!params[key]) delete params[key]
          })
  
          const response = await torneosAPI.getAll(params)
          
          if (response.data.success) {
            torneos.value = response.data.data.data || []
            
            // Actualizar paginación
            const pagination = response.data.data
            paginacion.value = {
              current_page: pagination.current_page,
              last_page: pagination.last_page,
              per_page: pagination.per_page,
              total: pagination.total
            }
  
            // Actualizar estadísticas
            actualizarEstadisticas()
          }
        } catch (err) {
          console.error('Error al cargar torneos:', err)
          error.value = 'Error al cargar los torneos. Por favor, intenta de nuevo.'
        } finally {
          loading.value = false
        }
      }
  
      const cargarDeportes = async () => {
        try {
          // Simulando deportes según tu base de datos
          deportes.value = [
            { id: 1, nombre: 'Fútbol' },
            { id: 2, nombre: 'Baloncesto' },
            { id: 3, nombre: 'Voleibol' },
            { id: 4, nombre: 'Tenis' }
          ]
        } catch (err) {
          console.error('Error al cargar deportes:', err)
        }
      }
  
      const aplicarFiltros = () => {
        filtros.page = 1
        cargarTorneos()
      }
  
      const limpiarFiltros = () => {
        Object.keys(filtros).forEach(key => {
          filtros[key] = key === 'page' ? 1 : ''
        })
        cargarTorneos()
      }
  
      const cambiarPagina = (page) => {
        if (page >= 1 && page <= paginacion.value.last_page) {
          filtros.page = page
          cargarTorneos()
        }
      }
  
      const actualizarEstadisticas = () => {
        const stats = {
          total: torneos.value.length,
          activos: 0,
          planificacion: 0,
          finalizados: 0,
          cancelados: 0
        }
  
        torneos.value.forEach(torneo => {
          if (torneo.estado === 'activo') stats.activos++
          else if (torneo.estado === 'planificacion') stats.planificacion++
          else if (torneo.estado === 'finalizado') stats.finalizados++
          else if (torneo.estado === 'cancelado') stats.cancelados++
        })
  
        estadisticas.value = stats
      }
  
      const calcularProgreso = (torneo) => {
        if (!torneo.partidos || torneo.partidos.length === 0) return 0
        const finalizados = torneo.partidos.filter(p => p.estado === 'finalizado').length
        return Math.round((finalizados / torneo.partidos.length) * 100)
      }
  
      const verTorneo = (id) => {
        router.push(`/torneos/${id}`)
      }
  
      const editarTorneo = (id) => {
        router.push(`/torneos/${id}/editar`)
      }
  
      const confirmarEliminar = (torneo) => {
        if (confirm(`¿Estás seguro de que quieres eliminar el torneo "${torneo.nombre}"?`)) {
          eliminarTorneo(torneo.id)
        }
      }
  
      const eliminarTorneo = async (id) => {
        try {
          await torneosAPI.delete(id)
          cargarTorneos() // Recargar lista
        } catch (err) {
          console.error('Error al eliminar torneo:', err)
          alert('Error al eliminar el torneo')
        }
      }
  
      // Formatters
      const getEstadoLabel = (estado) => {
        const labels = {
          'planificacion': 'En Planificación',
          'activo': 'Activo',
          'finalizado': 'Finalizado',
          'cancelado': 'Cancelado'
        }
        return labels[estado] || estado
      }
  
      const getEstadoClass = (estado) => {
        return `estado-${estado}`
      }
  
      const getFormatoLabel = (formato) => {
        const labels = {
          'liga': 'Liga',
          'eliminacion': 'Eliminación',
          'grupos': 'Grupos'
        }
        return labels[formato] || formato
      }
  
      const formatearFecha = (fecha) => {
        if (!fecha) return 'No definida'
        
        try {
          return new Date(fecha).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          })
        } catch (err) {
          return fecha
        }
      }
  
      // Ciclo de vida
      onMounted(() => {
        cargarTorneos()
        cargarDeportes()
      })
  
      return {
        // Estado
        torneos,
        deportes,
        loading,
        error,
        estadisticas,
        paginacion,
        filtros,
        authStore,
        
        // Computadas
        filtrosActivos,
        
        // Métodos
        cargarTorneos,
        aplicarFiltros,
        limpiarFiltros,
        cambiarPagina,
        verTorneo,
        editarTorneo,
        confirmarEliminar,
        calcularProgreso,
        
        // Formatters
        getEstadoLabel,
        getEstadoClass,
        getFormatoLabel,
        formatearFecha
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .torneo-index {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
  }
  
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-xl);
    
    .header-content {
      h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: var(--spacing-xs);
      }
      
      p {
        color: var(--gray-600);
        font-size: 1.125rem;
      }
    }
    
    .header-actions {
      .btn-icon {
        margin-right: var(--spacing-xs);
      }
    }
  }
  
  .filtros-card {
    margin-bottom: var(--spacing-xl);
    
    .filtros {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing-md);
      align-items: end;
      
      .filtro-grupo {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
        min-width: 150px;
        
        label {
          font-size: 0.875rem;
          font-weight: 500;
          color: var(--gray-700);
        }
        
        .filtro-input, .filtro-select {
          padding: var(--spacing-sm);
          border: 1px solid var(--gray-300);
          border-radius: var(--border-radius);
          font-size: 0.875rem;
          
          &:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
          }
        }
      }
    }
  }
  
  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
    
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
      }
      
      .stat-content {
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
  }
  
  .torneos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
  }
  
  .torneo-card {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    cursor: pointer;
    transition: all var(--transition-normal);
    
    &:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }
    
    .torneo-estado {
      padding: var(--spacing-sm) var(--spacing-md);
      text-align: right;
      
      span {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
        
        &.estado-planificacion {
          background: #dbeafe;
          color: #1d4ed8;
        }
        
        &.estado-activo {
          background: #dcfce7;
          color: #16a34a;
        }
        
        &.estado-finalizado {
          background: #f3f4f6;
          color: #6b7280;
        }
        
        &.estado-cancelado {
          background: #fee2e2;
          color: #dc2626;
        }
      }
    }
    
    .torneo-content {
      padding: 0 var(--spacing-lg) var(--spacing-md);
      
      .torneo-nombre {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: var(--spacing-sm);
        line-height: 1.3;
      }
      
      .torneo-descripcion {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-bottom: var(--spacing-md);
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      
      .torneo-meta {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
        margin-bottom: var(--spacing-md);
        
        .meta-item {
          display: flex;
          justify-content: space-between;
          font-size: 0.875rem;
          
          .meta-label {
            color: var(--gray-600);
          }
          
          .meta-value {
            color: var(--gray-800);
            font-weight: 500;
          }
        }
      }
      
      .torneo-fechas {
        display: flex;
        justify-content: space-between;
        margin-bottom: var(--spacing-md);
        
        .fecha-item {
          display: flex;
          flex-direction: column;
          align-items: center;
          
          .fecha-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-bottom: var(--spacing-xs);
          }
          
          .fecha-value {
            font-size: 0.875rem;
            color: var(--gray-800);
            font-weight: 500;
          }
        }
      }
      
      .torneo-progreso {
        .progreso-label {
          font-size: 0.75rem;
          color: var(--gray-600);
          margin-bottom: var(--spacing-xs);
        }
        
        .progreso-bar {
          height: 6px;
          background: var(--gray-200);
          border-radius: 3px;
          overflow: hidden;
          
          .progreso-fill {
            height: 100%;
            background: var(--primary-color);
            transition: width var(--transition-normal);
          }
        }
      }
    }
    
    .torneo-actions {
      padding: var(--spacing-md) var(--spacing-lg);
      border-top: 1px solid var(--gray-200);
      display: flex;
      justify-content: space-between;
      align-items: center;
      
      .admin-actions {
        display: flex;
        gap: var(--spacing-sm);
      }
    }
  }
  
  .paginacion {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-md);
    
    .paginacion-info {
      font-size: 0.875rem;
      color: var(--gray-600);
    }
  }
  
  // Estados vacíos, loading y error
  .loading-container, .error-container, .empty-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-2xl);
    text-align: center;
    
    .empty-icon {
      font-size: 4rem;
      margin-bottom: var(--spacing-md);
      opacity: 0.5;
    }
    
    h3 {
      color: var(--gray-700);
      margin-bottom: var(--spacing-sm);
    }
    
    p {
      color: var(--gray-600);
      margin-bottom: var(--spacing-lg);
    }
  }
  
  .error-message {
    color: var(--error-color);
    margin-bottom: var(--spacing-md);
  }
  
  // Responsive
  @media (max-width: 768px) {
    .torneo-index {
      padding: var(--spacing-md);
    }
    
    .page-header {
      flex-direction: column;
      gap: var(--spacing-md);
      
      .header-content h1 {
        font-size: 2rem;
      }
    }
    
    .filtros {
      flex-direction: column;
      align-items: stretch !important;
      
      .filtro-grupo {
        min-width: auto;
      }
    }
    
    .stats-cards {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .torneos-grid {
      grid-template-columns: 1fr;
    }
  }
  </style>