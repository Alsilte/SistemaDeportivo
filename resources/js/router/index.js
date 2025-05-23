import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

// ============================================================================
// IMPORTAR LAYOUTS
// ============================================================================
import AuthLayout from "@/layouts/AuthLayout.vue";
import AppLayout from "@/layouts/AppLayout.vue";

// ============================================================================
// IMPORTAR PÁGINAS
// ============================================================================

// Páginas de autenticación
const Login = () => import("@/pages/auth/Login.vue");
const Register = () => import("@/pages/auth/Register.vue");
//const ForgotPassword = () => import("@/pages/auth/ForgotPassword.vue");

// Dashboard
const Dashboard = () => import("@/pages/app/Dashboard.vue");

// Torneos
//const TorneosList = () => import("@/pages/torneos/TorneosList.vue");
//const TorneoDetail = () => import("@/pages/torneos/TorneoDetail.vue");
//const TorneoCreate = () => import("@/pages/torneos/TorneoCreate.vue");
//const TorneoEdit = () => import("@/pages/torneos/TorneoEdit.vue");

// Equipos
//const EquiposList = () => import("@/pages/equipos/EquiposList.vue");
//const EquipoDetail = () => import("@/pages/equipos/EquipoDetail.vue");
//const EquipoCreate = () => import("@/pages/equipos/EquipoCreate.vue");
//const EquipoEdit = () => import("@/pages/equipos/EquipoEdit.vue");

// Partidos
//const PartidosList = () => import("@/pages/partidos/PartidosList.vue");
//const PartidoDetail = () => import("@/pages/partidos/PartidoDetail.vue");
//const PartidoCreate = () => import("@/pages/partidos/PartidoCreate.vue");

// Usuario
//const UserProfile = () => import("@/pages/user/UserProfile.vue");
//const UserSettings = () => import("@/pages/user/UserSettings.vue");

// Páginas de error
const NotFound = () => import("@/pages/errors/NotFound.vue");
//const Forbidden = () => import("@/pages/errors/Forbidden.vue");

// ============================================================================
// DEFINIR RUTAS
// ============================================================================

const routes = [
    // Ruta raíz - redirige según autenticación
    {
        path: "/",
        redirect: () => {
            const authStore = useAuthStore();
            return authStore.isAuthenticated ? "/dashboard" : "/login";
        },
    },

    // ========================================
    // RUTAS DE AUTENTICACIÓN
    // ========================================
    {
        path: "/auth",
        component: AuthLayout,
        redirect: "/login",
        meta: { requiresGuest: true },
        children: [
            {
                path: "/login",
                name: "login",
                component: Login,
                meta: {
                    title: "Iniciar Sesión",
                    description: "Accede a tu cuenta del sistema deportivo",
                },
            },
            {
                path: "/register",
                name: "register",
                component: Register,
                meta: {
                    title: "Crear Cuenta",
                    description: "Regístrate en el sistema deportivo",
                },
            },
            {
                path: "/forgot-password",
                name: "forgot-password",
                component: ForgotPassword,
                meta: {
                    title: "Recuperar Contraseña",
                    description: "Recupera el acceso a tu cuenta",
                },
            },
        ],
    },

    // ========================================
    // RUTAS PRINCIPALES (REQUIEREN AUTH)
    // ========================================
    {
        path: "/app",
        component: AppLayout,
        redirect: "/dashboard",
        meta: { requiresAuth: true },
        children: [
            // Dashboard
            {
                path: "/dashboard",
                name: "dashboard",
                component: Dashboard,
                meta: {
                    title: "Dashboard",
                    icon: "dashboard",
                    description: "Panel principal del sistema",
                },
            },

            // ========================================
            // RUTAS DE TORNEOS
            // ========================================
            {
                path: "/torneos",
                name: "torneos.index",
                component: TorneosList,
                meta: {
                    title: "Torneos",
                    icon: "trophy",
                    description: "Gestión de torneos deportivos",
                },
            },
            {
                path: "/torneos/crear",
                name: "torneos.create",
                component: TorneoCreate,
                meta: {
                    title: "Crear Torneo",
                    requiresRoles: ["administrador"],
                    breadcrumb: [
                        { name: "Torneos", route: "torneos.index" },
                        { name: "Crear Torneo" },
                    ],
                },
            },
            {
                path: "/torneos/:id",
                name: "torneos.show",
                component: TorneoDetail,
                props: true,
                meta: {
                    title: "Detalle del Torneo",
                    breadcrumb: [
                        { name: "Torneos", route: "torneos.index" },
                        { name: "Detalle" },
                    ],
                },
            },
            {
                path: "/torneos/:id/editar",
                name: "torneos.edit",
                component: TorneoEdit,
                props: true,
                meta: {
                    title: "Editar Torneo",
                    requiresRoles: ["administrador"],
                    breadcrumb: [
                        { name: "Torneos", route: "torneos.index" },
                        { name: "Editar" },
                    ],
                },
            },

            // ========================================
            // RUTAS DE EQUIPOS
            // ========================================
            {
                path: "/equipos",
                name: "equipos.index",
                component: EquiposList,
                meta: {
                    title: "Equipos",
                    icon: "users",
                    description: "Gestión de equipos deportivos",
                },
            },
            {
                path: "/equipos/crear",
                name: "equipos.create",
                component: EquipoCreate,
                meta: {
                    title: "Crear Equipo",
                    requiresRoles: ["administrador"],
                    breadcrumb: [
                        { name: "Equipos", route: "equipos.index" },
                        { name: "Crear Equipo" },
                    ],
                },
            },
            {
                path: "/equipos/:id",
                name: "equipos.show",
                component: EquipoDetail,
                props: true,
                meta: {
                    title: "Detalle del Equipo",
                    breadcrumb: [
                        { name: "Equipos", route: "equipos.index" },
                        { name: "Detalle" },
                    ],
                },
            },
            {
                path: "/equipos/:id/editar",
                name: "equipos.edit",
                component: EquipoEdit,
                props: true,
                meta: {
                    title: "Editar Equipo",
                    requiresRoles: ["administrador"],
                    breadcrumb: [
                        { name: "Equipos", route: "equipos.index" },
                        { name: "Editar" },
                    ],
                },
            },

            // ========================================
            // RUTAS DE PARTIDOS
            // ========================================
            {
                path: "/partidos",
                name: "partidos.index",
                component: PartidosList,
                meta: {
                    title: "Partidos",
                    icon: "calendar",
                    description: "Gestión de partidos y resultados",
                },
            },
            {
                path: "/partidos/crear",
                name: "partidos.create",
                component: PartidoCreate,
                meta: {
                    title: "Crear Partido",
                    requiresRoles: ["administrador"],
                    breadcrumb: [
                        { name: "Partidos", route: "partidos.index" },
                        { name: "Crear Partido" },
                    ],
                },
            },
            {
                path: "/partidos/:id",
                name: "partidos.show",
                component: PartidoDetail,
                props: true,
                meta: {
                    title: "Detalle del Partido",
                    breadcrumb: [
                        { name: "Partidos", route: "partidos.index" },
                        { name: "Detalle" },
                    ],
                },
            },

            // ========================================
            // RUTAS DE USUARIO
            // ========================================
            {
                path: "/perfil",
                name: "user.profile",
                component: UserProfile,
                meta: {
                    title: "Mi Perfil",
                    icon: "user",
                    description: "Información personal y estadísticas",
                },
            },
            {
                path: "/configuracion",
                name: "user.settings",
                component: UserSettings,
                meta: {
                    title: "Configuración",
                    icon: "settings",
                    description: "Configuración de cuenta",
                },
            },
        ],
    },

    // ========================================
    // RUTAS DE ERROR
    // ========================================
    {
        path: "/403",
        name: "forbidden",
        component: Forbidden,
        meta: {
            title: "Acceso Denegado",
            hideNavigation: true,
        },
    },
    {
        path: "/:pathMatch(.*)*",
        name: "not-found",
        component: NotFound,
        meta: {
            title: "Página No Encontrada",
            hideNavigation: true,
        },
    },
];

// ============================================================================
// CREAR ROUTER
// ============================================================================

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else if (to.hash) {
            return { el: to.hash, behavior: "smooth" };
        } else {
            return { top: 0 };
        }
    },
});

// ============================================================================
// GUARDS DE NAVEGACIÓN
// ============================================================================

// Guard global antes de cada ruta
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Actualizar título de la página
    document.title = to.meta.title
        ? `${to.meta.title} - Sistema Deportivo`
        : "Sistema Deportivo";

    // Si la ruta requiere autenticación
    if (to.meta.requiresAuth) {
        // Verificar si está autenticado
        if (!authStore.isAuthenticated) {
            // Si no está autenticado, intentar verificar token
            await authStore.checkAuthStatus();

            // Si sigue sin estar autenticado, redirigir al login
            if (!authStore.isAuthenticated) {
                next({
                    name: "login",
                    query: { redirect: to.fullPath },
                });
                return;
            }
        }

        // Verificar roles si es necesario
        if (to.meta.requiresRoles && to.meta.requiresRoles.length > 0) {
            const userRole = authStore.user?.tipo_usuario;

            if (!to.meta.requiresRoles.includes(userRole)) {
                next({ name: "forbidden" });
                return;
            }
        }
    }

    // Si la ruta es solo para invitados (no autenticados)
    if (to.meta.requiresGuest && authStore.isAuthenticated) {
        next({ name: "dashboard" });
        return;
    }

    // Permitir navegación
    next();
});

// Guard después de cada navegación
router.afterEach((to, from) => {
    // Log de navegación en desarrollo
    if (import.meta.env.DEV) {
        console.log(`Navegación: ${from.path} → ${to.path}`);
    }
});

export default router;

// ============================================================================
// UTILIDADES DE NAVEGACIÓN
// ============================================================================

/**
 * Navegar a una ruta de forma programática
 */
export function navigateTo(name, params = {}, query = {}) {
    return router.push({ name, params, query });
}

/**
 * Verificar si una ruta está activa
 */
export function isRouteActive(routeName) {
    return router.currentRoute.value.name === routeName;
}

/**
 * Obtener breadcrumbs de la ruta actual
 */
export function getCurrentBreadcrumbs() {
    const route = router.currentRoute.value;
    return route.meta.breadcrumb || [];
}
