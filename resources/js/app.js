import { createApp } from 'vue';
import App from './App.vue';

const el = document.getElementById('app');
const initial = JSON.parse(el.dataset.expenses || '[]');

createApp(App, { initial }).mount(el);
