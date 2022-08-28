<template>
    <InertiaHead title="Update Type"/>

    <BreezeAuthenticatedLayout>
        <template slot="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Update Type - {{ type.name }}
            </h2>
        </template>
        <type-form :create-form="false"
                   :field-types="fieldTypes"
                   :type="type"
                   :relationship-types="relationshipTypes"
                   :okapi-types="okapiTypes"
                   :okapi-types-fields="okapiTypesFields"></type-form>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head} from '@inertiajs/inertia-vue3';
import TypeForm from '@/Pages/Okapi/Type/Partials/OkapiTypeForm';

export default {
    name: 'OkapiTypeEdit',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        TypeForm,
    },
    props: {
        fieldTypes: {
            type: Object,
            required: true,
        },
        type: {
            type: Object,
            required: true,
        },
        relationshipTypes: {
            type: Object,
            required: true,
        },
        okapiTypes: {
            type: Object,
            required: true,
        },
        okapiTypesFields: {
            type: Object,
            required: true,
        },
    },
}
</script>
