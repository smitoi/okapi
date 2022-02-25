<template>
    <InertiaHead title="Okapi Types"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Okapi Types
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <InertiaLink :href="route('okapi-types.create')">
                            Add new
                        </InertiaLink>
                        <div class="w-full p-4">
                            <table class="table-auto w-full">
                                <thead>
                                <tr>
                                    <td>
                                        Name
                                    </td>
                                    <td>
                                        Actions
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="type of types" :key="type.id">
                                    <td>
                                        {{ type.name }}
                                    </td>
                                    <td>
                                        <InertiaLink :href="route('okapi-types.show', type.slug)">
                                            View
                                        </InertiaLink>
                                        <InertiaLink :href="route('okapi-types.edit', type.slug)">
                                            Edit
                                        </InertiaLink>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {Head, Link} from '@inertiajs/inertia-vue3';

export default {
    name: 'OkapiTypeList',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
    },
    props: {
        types: Array,
    },
}
</script>
