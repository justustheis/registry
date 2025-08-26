import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import '../css/app.css'

const pages = import.meta.glob('./pages/**/*.vue', { eager: true });


createInertiaApp({
    resolve: name => {
        const page = pages[`./pages/${name}.vue`];
        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }
        return page.default;
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
