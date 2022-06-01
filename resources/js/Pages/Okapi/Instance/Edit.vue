<template>
    <InertiaHead title="Edit Instance"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit {{ type.name }}
            </h2>
        </template>
        <okapi-instance-form :create-form="false" :type="type" :instance="instance"
                             :relationships="relationships"
                             :reverse-relationships="reverseRelationships"></okapi-instance-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiInstanceForm from '@/Pages/Okapi/Instance/Partials/OkapiInstanceForm';

export default {
    name: 'OkapiInstanceEdit',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        OkapiInstanceForm,
    },
    props: {
        type: {
            type: Object,
            required: true,
        },
        relationships: {
            type: Object,
            required: true,
        },
        reverseRelationships: {
            type: Object,
            required: true,
        },
        instance: {
            type: Object,
            required: true,
        },
    },
}
</script>
