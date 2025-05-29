// Importar estilos
import '@/../../resources/sass/main.scss';

// Bootstrap (Axios config)
import './bootstrap';

console.log('Starting application initialization...');

// Vue y dependencias
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router/index';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

console.log('All dependencies imported successfully');

// Crear aplicación
const app = createApp(App);
console.log('Vue app instance created');

// Configurar Pinia
const pinia = createPinia();
app.use(pinia);
console.log('Pinia store setup complete');

// Configurar Router
app.use(router);
console.log('Vue Router setup complete');

// Configurar Toast
const toastOptions = {
    position: "top-right",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false,
    transition: "Vue-Toastification__fade",
    maxToasts: 5,
    newestOnTop: true
};

app.use(Toast, toastOptions);
console.log('Toast notification system setup complete');

// Montar aplicación
console.log('Attempting to mount app to #app element...');
app.mount('#app');
console.log('App mounted successfully');
