import './../css/app.css';
import type { App } from 'vue'
import AppPlanogram from './App.vue';
import GondolaModal from './components/modal/gondola/Add.vue';
import PlanogramLayout from './PlanogramLayout.vue';
import FlashMessageHandler from './FlashMessageHandler.vue';
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
    app.component('PlanogramLayout', PlanogramLayout);
    app.component('v-planogram-layout', PlanogramLayout);
    app.component('FlashMessageHandler', FlashMessageHandler);
    app.component('v-flash-message-handler', FlashMessageHandler);

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