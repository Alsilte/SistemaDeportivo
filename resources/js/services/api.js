import axios from "axios";
import { useToast } from "vue-toastification";

// ============================================================================
// CONFIGURACIÓN DE AXIOS
// ============================================================================

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL || "/api",
    timeout: 30000,
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: true,
});

// ============================================================================
// INTERCEPTORES
// ============================================================================

// Interceptor para requests
apiClient.interceptors.request.use(
    (config) => {
        // Agregar token de autenticación si existe
        const token = localStorage.getItem("auth_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // Agregar CSRF token si está disponible
        const csrfToken = document.head.querySelector(
            'meta[name="csrf-token"]'
        );
        if (csrfToken) {
            config.headers["X-CSRF-TOKEN"] = csrfToken.getAttribute("content");
        }

        // Log en desarrollo
        if (import.meta.env.DEV) {
            console.log(
                `🚀 API Request: ${config.method?.toUpperCase()} ${config.url}`,
                config.data
            );
        }

        return config;
    },
    (error) => {
        console.error("❌ Request Error:", error);
        return Promise.reject(error);
    }
);

// Interceptor para responses
apiClient.interceptors.response.use(
    (response) => {
        // Log en desarrollo
        if (import.meta.env.DEV) {
            console.log(
                `✅ API Response: ${response.status} ${response.config.url}`,
                response.data
            );
        }

        return response;
    },
    async (error) => {
        const toast = useToast();
        const { response, config } = error;
        const status = response?.status;
        const data = response?.data;

        // Log del error
        console.error("❌ API Error:", {
            url: config?.url,
            method: config?.method,
            status,
            message: data?.message,
            errors: data?.errors,
        });

        // Manejo específico por código de estado
        switch (status) {
            case 401:
                // Token expirado o inválido
                await handleUnauthorized();
                break;

            case 403:
                // Sin permisos
                if (!config.url?.includes("/auth/me")) {
                    toast.error(
                        data?.message ||
                            "No tienes permisos para realizar esta acción"
                    );
                }
                break;

            case 404:
                // Recurso no encontrado
                if (!config.url?.includes("/auth/me")) {
                    toast.error(data?.message || "Recurso no encontrado");
                }
                break;

            case 422:
                // Errores de validación - se manejan en los componentes
                break;

            case 429:
                // Rate limiting
                toast.warning(
                    "Demasiadas peticiones. Por favor, espera un momento"
                );
                break;

            case 500:
                // Error interno del servidor
                toast.error("Error interno del servidor. Intenta más tarde");
                break;

            default:
                // Error de red u otros
                if (!response) {
                    toast.error(
                        "Error de conexión. Verifica tu conexión a internet"
                    );
                }
        }

        return Promise.reject(error);
    }
);

// ============================================================================
// FUNCIONES AUXILIARES
// ============================================================================

/**
 * Manejar errores de autenticación (401)
 */
async function handleUnauthorized() {
    // Limpiar token local
    localStorage.removeItem("auth_token");
    localStorage.removeItem("user_data");

    // Redirigir al login si no estamos ya ahí
    const currentPath = window.location.pathname;
    const publicPaths = ["/login", "/register", "/forgot-password"];

    if (!publicPaths.some((path) => currentPath.startsWith(path))) {
        // Importación dinámica para evitar dependencias circulares
        const { default: router } = await import("@/router");
        router.push({
            name: "login",
            query: { redirect: currentPath },
        });
    }
}

// ============================================================================
// CLASE API SERVICE
// ============================================================================

class ApiService {
    constructor(axiosInstance) {
        this.client = axiosInstance;
    }

    // ============================================================================
    // MÉTODOS HTTP BÁSICOS
    // ============================================================================

    async get(url, config = {}) {
        return this.client.get(url, config);
    }

    async post(url, data = {}, config = {}) {
        return this.client.post(url, data, config);
    }

    async put(url, data = {}, config = {}) {
        return this.client.put(url, data, config);
    }

    async patch(url, data = {}, config = {}) {
        return this.client.patch(url, data, config);
    }

    async delete(url, config = {}) {
        return this.client.delete(url, config);
    }

    // ============================================================================
    // MÉTODOS ESPECIALES
    // ============================================================================

    /**
     * Upload de archivos
     */
    async upload(url, formData, onProgress = null) {
        const config = {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        };

        if (onProgress) {
            config.onUploadProgress = (progressEvent) => {
                const percentCompleted = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
                onProgress(percentCompleted);
            };
        }

        return this.client.post(url, formData, config);
    }

    /**
     * Descargar archivos
     */
    async download(url, filename = null) {
        const response = await this.client.get(url, {
            responseType: "blob",
        });

        // Crear URL temporal para descarga
        const blob = new Blob([response.data]);
        const downloadUrl = window.URL.createObjectURL(blob);

        // Crear y hacer clic en enlace temporal
        const link = document.createElement("a");
        link.href = downloadUrl;
        link.download = filename || "download";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Limpiar URL temporal
        window.URL.revokeObjectURL(downloadUrl);

        return response;
    }

    /**
     * Configurar token de autenticación
     */
    setAuthToken(token) {
        if (token) {
            this.client.defaults.headers.common[
                "Authorization"
            ] = `Bearer ${token}`;
        } else {
            delete this.client.defaults.headers.common["Authorization"];
        }
    }

    /**
     * Limpiar token de autenticación
     */
    clearAuthToken() {
        delete this.client.defaults.headers.common["Authorization"];
    }

    // ============================================================================
    // MÉTODOS DE PAGINACIÓN
    // ============================================================================

    /**
     * Obtener datos paginados
     */
    async paginate(url, params = {}) {
        const response = await this.get(url, { params });
        return {
            data: response.data.data?.data || response.data.data,
            meta: response.data.data?.meta || response.data.meta,
            links: response.data.data?.links || response.data.links,
        };
    }

    /**
     * Cargar más datos (scroll infinito)
     */
    async loadMore(nextPageUrl) {
        if (!nextPageUrl) return null;
        return this.get(nextPageUrl);
    }
}

// ============================================================================
// SERVICIOS ESPECÍFICOS POR MÓDULO
// ============================================================================

class AuthService extends ApiService {
    async login(credentials) {
        return this.post("/auth/login", credentials);
    }

    async register(userData) {
        return this.post("/auth/register", userData);
    }

    async logout() {
        return this.post("/auth/logout");
    }

    async me() {
        return this.get("/auth/me");
    }

    async refresh() {
        return this.post("/auth/refresh");
    }

    async forgotPassword(email) {
        return this.post("/auth/forgot-password", { email });
    }

    async resetPassword(data) {
        return this.post("/auth/reset-password", data);
    }

    async changePassword(data) {
        return this.post("/auth/change-password", data);
    }

    async updateProfile(data) {
        return this.put("/auth/profile", data);
    }
}

class TorneosService extends ApiService {
    async getAll(params = {}) {
        return this.paginate("/torneos", params);
    }

    async getById(id) {
        return this.get(`/torneos/${id}`);
    }

    async create(data) {
        return this.post("/torneos", data);
    }

    async update(id, data) {
        return this.put(`/torneos/${id}`, data);
    }

    async delete(id) {
        return this.delete(`/torneos/${id}`);
    }

    async getClasificacion(id) {
        return this.get(`/torneos/${id}/clasificacion`);
    }

    async getPartidos(id) {
        return this.get(`/torneos/${id}/partidos`);
    }

    async inscribirEquipo(torneoId, equipoId, data = {}) {
        return this.post(`/torneos/${torneoId}/inscribir-equipo`, {
            equipo_id: equipoId,
            ...data,
        });
    }

    async generarFixture(id) {
        return this.post(`/torneos/${id}/generar-fixture`);
    }
}

class EquiposService extends ApiService {
    async getAll(params = {}) {
        return this.paginate("/equipos", params);
    }

    async getById(id) {
        return this.get(`/equipos/${id}`);
    }

    async create(data) {
        return this.post("/equipos", data);
    }

    async update(id, data) {
        return this.put(`/equipos/${id}`, data);
    }

    async delete(id) {
        return this.delete(`/equipos/${id}`);
    }

    async getJugadores(id) {
        return this.get(`/equipos/${id}/jugadores`);
    }

    async addJugador(equipoId, jugadorData) {
        return this.post(`/equipos/${equipoId}/jugadores`, jugadorData);
    }

    async updateJugador(equipoId, jugadorId, data) {
        return this.put(`/equipos/${equipoId}/jugadores/${jugadorId}`, data);
    }

    async removeJugador(equipoId, jugadorId) {
        return this.delete(`/equipos/${equipoId}/jugadores/${jugadorId}`);
    }
}

class PartidosService extends ApiService {
    async getAll(params = {}) {
        return this.paginate("/partidos", params);
    }

    async getById(id) {
        return this.get(`/partidos/${id}`);
    }

    async create(data) {
        return this.post("/partidos", data);
    }

    async update(id, data) {
        return this.put(`/partidos/${id}`, data);
    }

    async delete(id) {
        return this.delete(`/partidos/${id}`);
    }

    async iniciar(id) {
        return this.post(`/partidos/${id}/iniciar`);
    }

    async finalizar(id, data) {
        return this.post(`/partidos/${id}/finalizar`, data);
    }

    async addEvento(partidoId, eventoData) {
        return this.post(`/partidos/${partidoId}/eventos`, eventoData);
    }

    async getEventos(id) {
        return this.get(`/partidos/${id}/eventos`);
    }
}

class UsuariosService extends ApiService {
    async getAll(params = {}) {
        return this.paginate("/usuarios", params);
    }

    async getById(id) {
        return this.get(`/usuarios/${id}`);
    }

    async create(data) {
        return this.post("/usuarios", data);
    }

    async update(id, data) {
        return this.put(`/usuarios/${id}`, data);
    }

    async delete(id) {
        return this.delete(`/usuarios/${id}`);
    }
}

// ============================================================================
// INSTANCIAS DE SERVICIOS
// ============================================================================

const apiService = new ApiService(apiClient);
const authService = new AuthService(apiClient);
const torneosService = new TorneosService(apiClient);
const equiposService = new EquiposService(apiClient);
const partidosService = new PartidosService(apiClient);
const usuariosService = new UsuariosService(apiClient);

// ============================================================================
// EXPORTACIONES
// ============================================================================

export default apiService;

export {
    apiClient,
    authService,
    torneosService,
    equiposService,
    partidosService,
    usuariosService,
};
