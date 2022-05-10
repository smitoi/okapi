<template>
    <InertiaHead title="View Role"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Role
            </h2>
        </template>
        <okapi-role-form :permissions="permissions" :types="types" :role="role" :readonly="true"></okapi-role-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiRoleForm from '@/Pages/Okapi/Roles/Partials/OkapiRoleForm';

export default {
    name: 'OkapiRoleShow',
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
