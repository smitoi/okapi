<template>
    <InertiaHead title="Okapi Roles"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Okapi Roles
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <InertiaLink :href="route('okapi-roles.create')">
                            Add new role
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
                                <tr v-for="role of roles" :key="role.id">
                                    <td>
                                        {{ role.name }}
                                    </td>
                                    <td>
                                        <InertiaLink :href="route('okapi-roles.show', role.id)">
                                            View
                                        </InertiaLink>
                                        <InertiaLink :href="route('okapi-roles.edit', role.id)">
                                            Edit
                                        </InertiaLink>
                                        <button @click="deleteRole(role)">Delete</button>
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

export default {
    name: 'OkapiUserList',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
    },
    props: {
        roles: Object,
    },
    setup() {
        const deleteRole = (role) => {
            Inertia.delete(route('okapi-roles.destroy', role.id));
        }

        return {
            deleteRole,
        };
    }
}
</script>
