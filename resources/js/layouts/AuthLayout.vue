<template>
    <div class="auth-layout">
        <div class="auth-container">
            <!-- Panel izquierdo - Información -->
            <div class="auth-info">
                <div class="auth-info-content">
                    <!-- Logo -->
                    <div class="auth-logo">
                        <div class="logo-icon">🏆</div>
                        <h1 class="logo-text">Sistema Deportivo</h1>
                    </div>

                    <!-- Información principal -->
                    <div class="auth-welcome">
                        <h2>Gestiona tu mundo deportivo</h2>
                        <p>
                            Sistema integral para la administración de torneos,
                            equipos y partidos. Lleva el control completo de tu
                            organización deportiva.
                        </p>
                    </div>

                    <!-- Características -->
                    <div class="auth-features">
                        <div class="feature-item">
                            <div class="feature-icon">🏆</div>
                            <div class="feature-text">
                                <h4>Gestión de Torneos</h4>
                                <p>
                                    Crea y administra torneos de diferentes
                                    formatos
                                </p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">👥</div>
                            <div class="feature-text">
                                <h4>Equipos y Jugadores</h4>
                                <p>
                                    Organiza equipos y gestiona información de
                                    jugadores
                                </p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">📊</div>
                            <div class="feature-text">
                                <h4>Estadísticas</h4>
                                <p>
                                    Análisis detallado y reportes en tiempo real
                                </p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">⚽</div>
                            <div class="feature-text">
                                <h4>Seguimiento en Vivo</h4>
                                <p>
                                    Actualiza resultados y eventos en tiempo
                                    real
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho - Formulario -->
            <div class="auth-form">
                <div class="auth-form-content">
                    <router-view />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// No necesita lógica adicional por ahora
</script>

<style lang="scss" scoped>
@import '@/../../../sass/mixins.scss';
.auth-layout {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(
        135deg,
        var(--primary-600) 0%,
        var(--secondary-600) 100%
    );
    padding: var(--spacing-md);
}

.auth-container {
    display: flex;
    max-width: 1200px;
    width: 100%;
    background: var(--white);
    border-radius: var(--border-radius-2xl);
    box-shadow: var(--shadow-2xl);
    overflow: hidden;
    min-height: 600px;
}

.auth-info {
    flex: 1;
    background: linear-gradient(
        135deg,
        var(--primary-600) 0%,
        var(--primary-800) 100%
    );
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-3xl);
    color: var(--white);
    position: relative;

    &::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="2" height="2" patternUnits="userSpaceOnUse"><circle cx="1" cy="1" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
}

.auth-info-content {
    position: relative;
    z-index: 1;
    max-width: 400px;
    width: 100%;
}

.auth-logo {
    text-align: center;
    margin-bottom: var(--spacing-2xl);

    .logo-icon {
        font-size: 3rem;
        margin-bottom: var(--spacing-sm);
        display: block;
    }

    .logo-text {
        font-size: var(--text-3xl);
        font-weight: 700;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
}

.auth-welcome {
    text-align: center;
    margin-bottom: var(--spacing-2xl);

    h2 {
        font-size: var(--text-2xl);
        font-weight: 600;
        margin-bottom: var(--spacing-md);
        color: var(--white);
    }

    p {
        font-size: var(--text-base);
        line-height: var(--leading-relaxed);
        opacity: 0.9;
        color: var(--white);
    }
}

.auth-features {
    display: grid;
    gap: var(--spacing-lg);
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius-lg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all var(--transition-normal);

    &:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .feature-icon {
        font-size: var(--text-2xl);
        min-width: 40px;
        text-align: center;
    }

    .feature-text {
        h4 {
            font-size: var(--text-base);
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
            color: var(--white);
        }

        p {
            font-size: var(--text-sm);
            opacity: 0.8;
            margin: 0;
            color: var(--white);
        }
    }
}

.auth-form {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-3xl);
    background: var(--gray-50);
}

.auth-form-content {
    width: 100%;
    max-width: 400px;
}

// Responsive design
@include respond-to("lg") {
    .auth-container {
        max-width: 1000px;
    }

    .auth-info,
    .auth-form {
        padding: var(--spacing-2xl);
    }
}

@media (max-width: 1023px) {
    .auth-container {
        flex-direction: column;
        max-width: 500px;
        min-height: auto;
    }

    .auth-info {
        padding: var(--spacing-xl);

        .auth-features {
            display: none; // Ocultar características en pantallas pequeñas
        }
    }

    .auth-form {
        padding: var(--spacing-xl);
    }
}

@media (max-width: 640px) {
    .auth-layout {
        padding: var(--spacing-sm);
    }

    .auth-info {
        padding: var(--spacing-lg);

        .logo-text {
            font-size: var(--text-2xl);
        }

        .auth-welcome h2 {
            font-size: var(--text-xl);
        }
    }

    .auth-form {
        padding: var(--spacing-lg);
    }
}
</style>
