import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import '../css/app.css'

const appName = 'Registry Editor'

createInertiaApp({
    title: () => `${appName}`,
    resolve: (name) => resolvePageComponent(`../../../vendor/justustheis/registry/resources/js/Pages/${name}.vue`, import.meta.glob('../../../vendor/justustheis/registry/resources/js/Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mixin({
                methods: {
                    route: window.route
                }
            })
            .mount(el)
    },
    progress: {
        color: '#4f46e5',
    },
})
