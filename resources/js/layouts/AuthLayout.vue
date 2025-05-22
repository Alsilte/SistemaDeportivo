<template>
  <div class="auth-layout">
    <div class="auth-container">
      <!-- Lado izquierdo - Información de la app -->
      <div class="auth-info">
        <div class="auth-info-content">
          <div class="logo">
            <h1>🏆 SportManager</h1>
          </div>
          
          <div class="info-text">
            <h2>Sistema de Gestión Deportiva</h2>
            <p>
              Administra torneos, equipos y partidos de manera profesional. 
              Mantén el control de todos los aspectos de tu organización deportiva.
            </p>
            
            <div class="features">
              <div class="feature">
                <span class="feature-icon">⚽</span>
                <span>Gestión de Torneos</span>
              </div>
              <div class="feature">
                <span class="feature-icon">👥</span>
                <span>Administración de Equipos</span>
              </div>
              <div class="feature">
                <span class="feature-icon">📊</span>
                <span>Estadísticas en Tiempo Real</span>
              </div>
              <div class="feature">
                <span class="feature-icon">🏅</span>
                <span>Clasificaciones Automáticas</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Lado derecho - Formulario de autenticación -->
      <div class="auth-form">
        <div class="auth-form-content">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AuthLayout'
}
</script>

<style lang="scss" scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  padding: var(--spacing-md);
}

.auth-container {
  display: flex;
  max-width: 1200px;
  width: 100%;
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-xl);
  overflow: hidden;
  min-height: 600px;
}

.auth-info {
  flex: 1;
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-2xl);
  color: white;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
  }
}

.auth-info-content {
  position: relative;
  z-index: 1;
  text-align: center;
  max-width: 400px;
}

.logo {
  margin-bottom: var(--spacing-xl);
  
  h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }
}

.info-text {
  h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: var(--spacing-md);
    line-height: 1.3;
  }
  
  p {
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: var(--spacing-xl);
    opacity: 0.9;
  }
}

.features {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
  margin-top: var(--spacing-xl);
}

.feature {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: var(--spacing-md);
  background: rgba(255, 255, 255, 0.1);
  border-radius: var(--border-radius);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  
  .feature-icon {
    font-size: 1.5rem;
    margin-bottom: var(--spacing-sm);
  }
  
  span:last-child {
    font-size: 0.875rem;
    text-align: center;
    font-weight: 500;
  }
}

.auth-form {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-2xl);
  background: var(--gray-50);
}

.auth-form-content {
  width: 100%;
  max-width: 400px;
}

// Responsive
@media (max-width: 968px) {
  .auth-container {
    flex-direction: column;
    max-width: 500px;
  }
  
  .auth-info {
    padding: var(--spacing-xl);
    
    .features {
      display: none;
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
    
    .logo h1 {
      font-size: 2rem;
    }
    
    .info-text h2 {
      font-size: 1.25rem;
    }
  }
  
  .auth-form {
    padding: var(--spacing-lg);
  }
}
</style>