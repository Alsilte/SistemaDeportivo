import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

// Crear la aplicación Vue
const app = createApp(App);

// Configurar Pinia (estado global)
const pinia = createPinia();
app.use(pinia);

// Configurar Vue Router
app.use(router);

// Montar la aplicación
app.mount('#app');