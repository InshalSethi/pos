import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createVfm } from 'vue-final-modal';
import Alpine from 'alpinejs';
import '@splinetool/viewer';
import router from './router';
import App from './App.vue';

// Initialize Alpine.js locally
window.Alpine = Alpine;
Alpine.start();

// Import Vue Final Modal styles
import 'vue-final-modal/style.css';

// Create Pinia store
const pinia = createPinia();

// Create Vue Final Modal instance
const vfm = createVfm();

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(pinia);
app.use(router);
app.use(vfm);

// Mount app
app.config.globalProperties.$baseCurrency = window.baseCurrency;
app.mount('#app');
