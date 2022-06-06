<template>
    <InertiaHead title="Okapi Api Keys"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Okapi Api Keys
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <ButtonInertiaLink :href="route('okapi-api-keys.create')" class="mb-2">
                            Add new Api key
                        </ButtonInertiaLink>
                        <div class="w-full p-4 border rounded-xl">
                            <table class="table-auto w-full border-collapse rounded-lg p-8">
                                <thead>
                                <tr>
                                    <th class="border-b text-left p-4">
                                        Name
                                    </th>
                                    <th class="border-b text-left p-4">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="apiKey of apiKeys" :key="apiKey.id">
                                    <td class="p-4">
                                        {{ apiKey.name }}
                                    </td>
                                    <td class="p-4">
                                        <ButtonInertiaLink :href="route('okapi-api-keys.show', apiKey.id)" class="mr-2">
                                            View
                                        </ButtonInertiaLink>
                                        <ButtonInertiaLink :href="route('okapi-api-keys.edit', apiKey.id)" class="mr-2">
                                            Edit
                                        </ButtonInertiaLink>
                                        <BreezeButton @click="deleteApiKey(apiKey)">
                                            Delete
                                        </BreezeButton>
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
import {Head} from '@inertiajs/inertia-vue3';
import {Inertia} from "@inertiajs/inertia";
import BreezeButton from '@/Components/Breeze/Button.vue';
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink.vue';

export default {
    name: 'OkapiApiKeyList',
    components: {
        BreezeAuthenticatedLayout,
        BreezeButton,
        InertiaHead: Head,
        ButtonInertiaLink,
    },
    props: {
        apiKeys: Object,
    },
    setup() {
        const deleteApiKey = (apiKey) => {
            Inertia.delete(route('okapi-api-keys.destroy', apiKey.id));
        }

        return {
            deleteApiKey,
        };
    }
}
</script>
