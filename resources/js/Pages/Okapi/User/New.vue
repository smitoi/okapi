<template>
    <InertiaHead title="Create User"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create User
            </h2>
        </template>
        <okapi-user-form :create-form="true"
                         :roles="roles"></okapi-user-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiUserForm from '@/Pages/Okapi/User/Partials/OkapiUserForm';

export default {
    name: 'OkapiUserNew',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        OkapiUserForm,
    },
    props: {
        roles: {
            type: Object,
            required: true,
        },
    },
}
</script>
