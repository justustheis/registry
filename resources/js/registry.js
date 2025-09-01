import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import '../css/app.css'
import Index from './pages/Index.vue'

createInertiaApp({
    resolve: name => {
        if (name === 'Index') {
            return Index;
        }
        throw new Error(`Page not found: ${name}`);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mixin({
                methods: {
                    route: window.route
                }
            })
            .mount(el);
    },
    progress: {
        color: '#4f46e5',
    },
});
