require('./bootstrap');

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import {InertiaProgress} from '@inertiajs/progress';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import Timepicker from 'vue3-timepicker'
import 'vue3-timepicker/dist/VueTimepicker.css'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => require(`./Pages/${name}.vue`),
    setup({el, app, props, plugin}) {
        return createApp({render: () => h(app, props)})
            .use(plugin)
            .component('Datepicker', Datepicker)
            .component('Timepicker', Timepicker)
            .mixin({methods: {route}})
            .mount(el);
    },
});

InertiaProgress.init({color: '#4B5563'});
