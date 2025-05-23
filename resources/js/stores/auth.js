import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useToast } from "vue-toastification";
import apiService from "@/services/api";

export const useAuthStore = defineStore("auth", () => {
    // ============================================================================
    // ESTADO
    // ============================================================================

    const user = ref(null);
    const token = ref(localStorage.getItem("auth_token"));
    const loading = ref(false);
    const lastActivity = ref(Date.now());

    // ============================================================================
    // GETTERS COMPUTADOS
    // ============================================================================

    const isAuthenticated = computed(() => !!token.value && !!user.value);

    const userRole = computed(() => user.value?.tipo_usuario || null);

    const userName = computed(() => user.value?.nombre || "");

    const userEmail = computed(() => user.value?.email || "");

    const userAvatar = computed(() => {
        if (user.value?.avatar) {
            return user.value.avatar.startsWith("http")
                ? user.value.avatar
                : `${import.meta.env.VITE_APP_URL}/storage/${
                      user.value.avatar
                  }`;
        }
        return null;
    });

    // Verificaciones de rol
    const isAdmin = computed(() => userRole.value === "administrador");
    const isJugador = computed(() => userRole.value === "jugador");
    const isArbitro = computed(() => userRole.value === "arbitro");

    // Información específica del perfil
    const userProfile = computed(() => {
        if (!user.value) return null;

        switch (userRole.value) {
            case "jugador":
                return user.value.jugador;
            case "arbitro":
                return user.value.arbitro;
            case "administrador":
                return user.value.administrador;
            default:
                return null;
        }
    });

    // ============================================================================
    // ACCIONES
    // ============================================================================

    const toast = useToast();

    /**
     * Configurar token de autenticación
     */
    function setToken(newToken) {
        token.value = newToken;
        if (newToken) {
            localStorage.setItem("auth_token", newToken);
            apiService.setAuthToken(newToken);
        } else {
            localStorage.removeItem("auth_token");
            apiService.clearAuthToken();
        }
    }

    /**
     * Configurar datos del usuario
     */
    function setUser(userData) {
        user.value = userData;
        lastActivity.value = Date.now();
    }

    /**
     * Limpiar datos de autenticación
     */
    function clearAuth() {
        user.value = null;
        setToken(null);
        localStorage.removeItem("user_data");
    }

    /**
     * Iniciar sesión
     */
    async function login(credentials) {
        loading.value = true;

        try {
            const response = await apiService.post("/auth/login", {
                email: credentials.email,
                password: credentials.password,
                remember: credentials.remember || false,
                device_name: getDeviceName(),
            });

            if (response.data.success) {
                const { user: userData, token: userToken } = response.data.data;

                setUser(userData);
                setToken(userToken);

                // Guardar datos del usuario en localStorage para persistencia
                localStorage.setItem("user_data", JSON.stringify(userData));

                toast.success(`¡Bienvenido, ${userData.nombre}!`);

                return { success: true, user: userData };
            } else {
                throw new Error(
                    response.data.message || "Error al iniciar sesión"
                );
            }
        } catch (error) {
            const message =
                error.response?.data?.message ||
                error.message ||
                "Error al iniciar sesión";
            toast.error(message);
            return { success: false, error: message };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Registrar nuevo usuario
     */
    async function register(userData) {
        loading.value = true;

        try {
            const response = await apiService.post("/auth/register", userData);

            if (response.data.success) {
                const { user: newUser, token: userToken } = response.data.data;

                setUser(newUser);
                setToken(userToken);

                localStorage.setItem("user_data", JSON.stringify(newUser));

                toast.success(
                    `¡Cuenta creada exitosamente! Bienvenido, ${newUser.nombre}`
                );

                return { success: true, user: newUser };
            } else {
                throw new Error(
                    response.data.message || "Error al registrar usuario"
                );
            }
        } catch (error) {
            const message =
                error.response?.data?.message ||
                error.message ||
                "Error al registrar usuario";

            // No mostrar toast para errores de validación (se manejan en el componente)
            if (error.response?.status !== 422) {
                toast.error(message);
            }

            return {
                success: false,
                error: message,
                errors: error.response?.data?.errors,
            };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Cerrar sesión
     */
    async function logout() {
        loading.value = true;

        try {
            // Intentar cerrar sesión en el servidor
            if (token.value) {
                await apiService.post("/auth/logout");
            }
        } catch (error) {
            console.warn("Error al cerrar sesión en el servidor:", error);
        } finally {
            // Siempre limpiar datos locales
            clearAuth();
            loading.value = false;
            toast.info("Sesión cerrada correctamente");
        }
    }

    /**
     * Verificar estado de autenticación
     */
    async function checkAuthStatus() {
        if (!token.value) {
            // Si no hay token, verificar si hay datos guardados
            const savedUserData = localStorage.getItem("user_data");
            if (!savedUserData) {
                return false;
            }
        }

        try {
            const response = await apiService.get("/auth/me");

            if (response.data.success) {
                setUser(response.data.data);
                return true;
            } else {
                clearAuth();
                return false;
            }
        } catch (error) {
            // Si hay error 401, el token es inválido
            if (error.response?.status === 401) {
                clearAuth();
            }
            return false;
        }
    }

    /**
     * Actualizar perfil del usuario
     */
    async function updateProfile(profileData) {
        loading.value = true;

        try {
            const response = await apiService.put("/auth/profile", profileData);

            if (response.data.success) {
                setUser(response.data.data);
                localStorage.setItem(
                    "user_data",
                    JSON.stringify(response.data.data)
                );

                toast.success("Perfil actualizado correctamente");

                return { success: true, user: response.data.data };
            } else {
                throw new Error(
                    response.data.message || "Error al actualizar perfil"
                );
            }
        } catch (error) {
            const message =
                error.response?.data?.message ||
                error.message ||
                "Error al actualizar perfil";
            toast.error(message);
            return { success: false, error: message };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Cambiar contraseña
     */
    async function changePassword(passwordData) {
        loading.value = true;

        try {
            const response = await apiService.post(
                "/auth/change-password",
                passwordData
            );

            if (response.data.success) {
                toast.success("Contraseña cambiada correctamente");
                return { success: true };
            } else {
                throw new Error(
                    response.data.message || "Error al cambiar contraseña"
                );
            }
        } catch (error) {
            const message =
                error.response?.data?.message ||
                error.message ||
                "Error al cambiar contraseña";
            toast.error(message);
            return { success: false, error: message };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Solicitar restablecimiento de contraseña
     */
    async function forgotPassword(email) {
        loading.value = true;

        try {
            const response = await apiService.post("/auth/forgot-password", {
                email,
            });

            if (response.data.success) {
                toast.success(
                    "Se ha enviado un enlace de restablecimiento a tu email"
                );
                return { success: true };
            } else {
                throw new Error(
                    response.data.message || "Error al enviar email"
                );
            }
        } catch (error) {
            const message =
                error.response?.data?.message ||
                error.message ||
                "Error al enviar email";
            toast.error(message);
            return { success: false, error: message };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Refrescar token
     */
    async function refreshToken() {
        try {
            const response = await apiService.post("/auth/refresh");

            if (response.data.success) {
                const { user: userData, token: newToken } = response.data.data;
                setUser(userData);
                setToken(newToken);
                return true;
            }
            return false;
        } catch (error) {
            clearAuth();
            return false;
        }
    }

    /**
     * Actualizar actividad del usuario
     */
    function updateActivity() {
        lastActivity.value = Date.now();
    }

    /**
     * Verificar si tiene un rol específico
     */
    function hasRole(role) {
        return userRole.value === role;
    }

    /**
     * Verificar si tiene alguno de los roles especificados
     */
    function hasAnyRole(roles) {
        return roles.includes(userRole.value);
    }

    /**
     * Obtener información resumida del usuario
     */
    function getUserSummary() {
        if (!user.value) return null;

        return {
            id: user.value.id,
            nombre: user.value.nombre,
            email: user.value.email,
            tipo_usuario: user.value.tipo_usuario,
            avatar: userAvatar.value,
            activo: user.value.activo,
        };
    }

    // ============================================================================
    // UTILIDADES
    // ============================================================================

    function getDeviceName() {
        const ua = navigator.userAgent;
        if (ua.includes("Mobile")) return "Mobile";
        if (ua.includes("Tablet")) return "Tablet";
        return "Desktop";
    }

    // ============================================================================
    // INICIALIZACIÓN
    // ============================================================================

    // Configurar token si existe al inicializar
    if (token.value) {
        apiService.setAuthToken(token.value);

        // Intentar cargar datos del usuario desde localStorage
        const savedUserData = localStorage.getItem("user_data");
        if (savedUserData) {
            try {
                setUser(JSON.parse(savedUserData));
            } catch (error) {
                console.warn(
                    "Error al cargar datos de usuario desde localStorage"
                );
                localStorage.removeItem("user_data");
            }
        }
    }

    // ============================================================================
    // RETORNAR API PÚBLICA
    // ============================================================================

    return {
        // Estado
        user,
        token,
        loading,
        lastActivity,

        // Getters
        isAuthenticated,
        userRole,
        userName,
        userEmail,
        userAvatar,
        isAdmin,
        isJugador,
        isArbitro,
        userProfile,

        // Acciones
        login,
        register,
        logout,
        checkAuthStatus,
        updateProfile,
        changePassword,
        forgotPassword,
        refreshToken,
        updateActivity,
        clearAuth,

        // Utilidades
        hasRole,
        hasAnyRole,
        getUserSummary,
    };
});
