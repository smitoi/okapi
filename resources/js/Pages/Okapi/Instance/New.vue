<template>
    <InertiaHead title="Create Instance"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create {{ type.name }}
            </h2>
        </template>
        <okapi-instance-form :type="type" :relationships="relationships" :relationship-reverses="relationshipReverses"></okapi-instance-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiInstanceForm from '@/Pages/Okapi/Instance/Partials/OkapiInstanceForm';

export default {
    name: 'OkapiInstanceNew',
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
        relationshipReverses: {
            type: Object,
            required: true,
        },
    },
}
</script>
