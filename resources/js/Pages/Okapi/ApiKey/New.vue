<template>
    <InertiaHead title="Create Api Key"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Api Key
            </h2>
        </template>
        <okapi-api-key-form :permissions="permissions" :types="types"></okapi-api-key-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiApiKeyForm from '@/Pages/Okapi/ApiKey/Partials/OkapiApiKeyForm';

export default {
    name: 'OkapiApiKeyNew',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        OkapiApiKeyForm,
    },
    props: {
        permissions: {
            type: Array,
            required: true,
        },
        types: {
            type: Array,
            required: true,
        },
    },
}
</script>
