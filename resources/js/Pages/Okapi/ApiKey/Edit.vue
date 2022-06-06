<template>
    <InertiaHead title="Edit Api Key"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Api Key
            </h2>
        </template>
        <okapi-api-key-form :create-form="false" :permissions="permissions" :types="types"
                            :api-key="apiKey"></okapi-api-key-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiApiKeyForm from '@/Pages/Okapi/ApiKey/Partials/OkapiApiKeyForm';

export default {
    name: 'OkapiApiKeyEdit',
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
        apiKey: {
            type: Object,
            required: true,
        },
    },
}
</script>
