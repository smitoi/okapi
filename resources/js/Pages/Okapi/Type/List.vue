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
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <ButtonInertiaLink :href="route('okapi-types.create')">
                            Add new type
                        </ButtonInertiaLink>
                        <ButtonLink :href="route('okapi-documentation')" class="ml-4">
                            Generate documentation
                        </ButtonLink>
                        <div class="table-auto w-full border-collapse rounded-lg p-8">
                            <table class="table-auto w-full">
                                <thead>
                                <tr>
                                    <th class="border-b text-left">
                                        Name
                                    </th>
                                    <th class="border-b text-left">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="type of types" :key="type.id">
                                    <td class="p-4">
                                        {{ type.name }}
                                    </td>
                                    <td class="p-4">
                                        <ButtonInertiaLink :href="route('okapi-instances.index', type.slug)" class="mr-2">
                                            View
                                        </ButtonInertiaLink>
                                        <ButtonInertiaLink :href="route('okapi-types.edit', type.slug)" class="mr-2">
                                            Edit
                                        </ButtonInertiaLink>
                                        <BreezeButton @click="deleteType(type)">Delete</BreezeButton>
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
import {Inertia} from "@inertiajs/inertia";
import BreezeButton from '@/Components/Breeze/Button.vue';
import ButtonLink from "@/Components/Misc/ButtonLink";
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink.vue';

export default {
    name: 'OkapiTypeList',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
        BreezeButton,
        ButtonLink,
        ButtonInertiaLink,
    },
    props: {
        types: Array,
    },
    setup() {
        const deleteType = (type) => {
            Inertia.delete(route('okapi-types.destroy', type.slug));
        }

        return {
            deleteType,
        };
    }
}
</script>
