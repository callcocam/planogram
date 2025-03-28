import './../css/app.css';
import type { App } from 'vue' 
import AppPlanogram from './App.vue';
import GondolaModal from './components/modal/gondola/Add.vue';


interface PluginOptions {
    [key: string]: any
} 

const install = (app: App, options: PluginOptions = {}) => {
    const componentRegistry: string[] = [];
    app.component('Planogram', AppPlanogram);
    app.component('v-planogram', AppPlanogram);  

    app.component('GondolaModal', GondolaModal);

    app.config.globalProperties.$planogram = options;
}

export default {
    install
}