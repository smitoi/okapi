<template>
    <InertiaLink :href="href"
       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150">
        <slot/>
    </InertiaLink>
</template>


<script>
import {InertiaLink} from "@inertiajs/inertia-vue3";
import BreezeButton from '@/Components/Breeze/Button.vue';

export default {
    props: {
        href: {
            type: String,
            required: true,
        },
    },
    components: {
        InertiaLink,
        BreezeButton,
    }
}
</script>


