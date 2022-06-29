<template>
    <InertiaHead title="View Instance"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View {{ type.name }}
            </h2>
        </template>
        <okapi-instance-form :type="type" :instance="instance" :relationships="relationships"
                             :readonly="true" :reverse-relationships="reverseRelationships">
        </okapi-instance-form>
    </BreezeAuthenticatedLayout>
</template>


<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import OkapiInstanceForm from '@/Pages/Okapi/Instance/Partials/OkapiInstanceForm';
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink.vue';

export default {
    name: 'OkapiInstanceShow',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        OkapiInstanceForm,
        ButtonInertiaLink,
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
