<template>
    <div class="guest-layout">
      <!-- Header público -->
      <header class="guest-header">
        <div class="header-container">
          <div class="logo-section">
            <router-link to="/" class="logo-link">
              <span class="logo-icon">🏆</span>
              <span class="logo-text">SportManager</span>
            </router-link>
          </div>
          
          <nav class="guest-nav">
            <router-link to="/login" class="nav-link">
              Iniciar Sesión
            </router-link>
            <router-link to="/register" class="nav-link nav-cta">
              Registrarse
            </router-link>
          </nav>
        </div>
      </header>
  
      <!-- Contenido principal -->
      <main class="guest-main">
        <div class="content-container">
          <router-view />
        </div>
      </main>
  
      <!-- Footer -->
      <footer class="guest-footer">
        <div class="footer-container">
          <div class="footer-content">
            <div class="footer-section">
              <h4>SportManager</h4>
              <p>Sistema integral de gestión deportiva</p>
            </div>
            
            <div class="footer-section">
              <h5>Enlaces</h5>
              <ul>
                <li><router-link to="/">Inicio</router-link></li>
                <li><router-link to="/login">Iniciar Sesión</router-link></li>
                <li><router-link to="/register">Registrarse</router-link></li>
              </ul>
            </div>
            
            <div class="footer-section">
              <h5>Deportes</h5>
              <ul>
                <li>Fútbol</li>
                <li>Baloncesto</li>
                <li>Voleibol</li>
                <li>Otros</li>
              </ul>
            </div>
          </div>
          
          <div class="footer-bottom">
            <p>&copy; {{ currentYear }} SportManager. Todos los derechos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  </template>
  
  <script>
  import { computed } from 'vue'
  
  export default {
    name: 'GuestLayout',
    setup() {
      const currentYear = computed(() => new Date().getFullYear())
      
      return {
        currentYear
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
@import '@/../../../sass/mixins.scss';
.guest-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--gray-50);
  }
  
  .guest-header {
    background: white;
    border-bottom: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 0;
    z-index: 100;
  }
  
  .header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
  }
  
  .logo-section {
    .logo-link {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      text-decoration: none;
      color: var(--primary-color);
      font-weight: 700;
      font-size: 1.5rem;
      
      .logo-icon {
        font-size: 2rem;
      }
      
      &:hover {
        color: var(--primary-dark);
      }
    }
  }
  
  .guest-nav {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    
    .nav-link {
      padding: var(--spacing-sm) var(--spacing-md);
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 500;
      transition: all var(--transition-fast);
      
      &:not(.nav-cta) {
        color: var(--gray-700);
        
        &:hover {
          color: var(--primary-color);
          background: var(--gray-50);
        }
      }
      
      &.nav-cta {
        background: var(--primary-color);
        color: white;
        
        &:hover {
          background: var(--primary-dark);
          transform: translateY(-1px);
        }
      }
    }
  }
  
  .guest-main {
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  
  .content-container {
    flex: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-lg);
    width: 100%;
  }
  
  .guest-footer {
    background: var(--gray-800);
    color: var(--gray-100);
    margin-top: auto;
  }
  
  .footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-lg) var(--spacing-lg);
  }
  
  .footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
    
    .footer-section {
      h4, h5 {
        color: white;
        margin-bottom: var(--spacing-md);
      }
      
      p {
        color: var(--gray-300);
        line-height: 1.6;
      }
      
      ul {
        list-style: none;
        padding: 0;
        margin: 0;
        
        li {
          margin-bottom: var(--spacing-sm);
          
          a {
            color: var(--gray-300);
            text-decoration: none;
            
            &:hover {
              color: white;
            }
          }
        }
      }
    }
  }
  
  .footer-bottom {
    border-top: 1px solid var(--gray-700);
    padding-top: var(--spacing-lg);
    text-align: center;
    color: var(--gray-400);
    font-size: 0.875rem;
  }
  
  // Responsive
  @media (max-width: 768px) {
    .header-container {
      padding: 0 var(--spacing-md);
    }
    
    .logo-section .logo-link {
      font-size: 1.25rem;
      
      .logo-icon {
        font-size: 1.5rem;
      }
    }
    
    .guest-nav {
      gap: var(--spacing-sm);
      
      .nav-link {
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: 0.875rem;
      }
    }
    
    .content-container {
      padding: var(--spacing-lg) var(--spacing-md);
    }
    
    .footer-content {
      grid-template-columns: 1fr;
      gap: var(--spacing-lg);
      text-align: center;
    }
  }
  </style>