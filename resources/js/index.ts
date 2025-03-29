import './../css/app.css';
import type { App } from 'vue'
import AppPlanogram from './App.vue';
import GondolaModal from './components/modal/gondola/Add.vue';
// @ts-ignore
import { Toaster } from '@/components/ui/toast'
import ConfirmProvider from './ConfirmProvider.vue';
import ConfirmModal from './Confirm.vue';

interface PluginOptions {
    [key: string]: any
}

const install = (app: App, options: PluginOptions = {}) => {
    const componentRegistry: string[] = [];
    app.component('Planogram', AppPlanogram);
    app.component('v-planogram', AppPlanogram);

    // Componente global Toast
    app.component('Toaster', Toaster)

    // Configuração global de modais e providers
    app.component('ConfirmProvider', ConfirmProvider)
    app.component('v-confirm-provider', ConfirmProvider)
    app.component('ConfirmModal', ConfirmModal)
    app.component('v-confirm', ConfirmProvider)

    app.component('GondolaModal', GondolaModal);
    app.component('v-gondola-modal', GondolaModal);

    app.config.globalProperties.$planogram = options;
}

export default {
    install
}