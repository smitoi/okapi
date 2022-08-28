<template>
    <InertiaHead title="Update User"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Update User
            </h2>
        </template>
        <okapi-user-form :create-form="false"
                         :user="user"
                         :roles="roles"></okapi-user-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiUserForm from '@/Pages/Okapi/User/Partials/OkapiUserForm';

export default {
    name: 'OkapiUserEdit',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        OkapiUserForm,
    },
    props: {
        user: {
            type: Object,
            required: true,
        },
        roles: {
            type: Object,
            required: true,
        },
    },
}
</script>
