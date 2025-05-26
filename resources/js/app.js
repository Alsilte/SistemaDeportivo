// Importar estilos
import '@/../../resources/sass/main.scss';

// Bootstrap (Axios config)
import './bootstrap';

// Vue y dependencias
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router/index';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

// Crear aplicación
const app = createApp(App);

// Configurar Pinia
const pinia = createPinia();
app.use(pinia);

// Configurar Router
app.use(router);

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

// Montar aplicación
app.mount('#app');