<template>
    <InertiaHead title="Create Role"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Role
            </h2>
        </template>
        <okapi-role-form :permissions="permissions" :types="types"></okapi-role-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiRoleForm from '@/Pages/Okapi/Roles/Partials/OkapiRoleForm';

export default {
    name: 'OkapiRoleNew',
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
    },
}
</script>
