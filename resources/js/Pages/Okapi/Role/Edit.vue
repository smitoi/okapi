<template>
    <InertiaHead title="Edit Role"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Role
            </h2>
        </template>
        <okapi-role-form :create-form="false" :permissions="permissions" :types="types" :role="role"></okapi-role-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiRoleForm from '@/Pages/Okapi/Role/Partials/OkapiRoleForm';

export default {
    name: 'OkapiRoleEdit',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        OkapiRoleForm,
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
        role: {
            type: Object,
            required: true,
        },
    },
}
</script>
