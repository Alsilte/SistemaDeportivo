<template>
    <div id="app" class="app">
        <router-view />

        <!-- Componente de notificaciones -->
        <notifications-container />
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import NotificationsContainer from "@/components/ui/NotificationsContainer.vue";

const authStore = useAuthStore();

// Inicializar la aplicación
onMounted(async () => {
    // Verificar si hay token guardado e intentar restaurar sesión
    await authStore.checkAuthStatus();
});
</script>

<style lang="scss">
@import "~sass/main.scss";
.app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
</style>
