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
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <ButtonInertiaLink :href="route('okapi-roles.create')" class="mb-2">
                            Add new role
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
                                <tr v-for="role of roles" :key="role.id">
                                    <td class="p-4">
                                        {{ role.name }}
                                    </td>
                                    <td class="p-4">
                                        <ButtonInertiaLink :href="route('okapi-roles.show', role.id)" class="mr-2"
                                                    v-show="role.name !== $page.props.admin_role">
                                            View
                                        </ButtonInertiaLink>
                                        <ButtonInertiaLink :href="route('okapi-roles.edit', role.id)" class="mr-2"
                                                    v-show="role.name !== $page.props.admin_role">
                                            Edit
                                        </ButtonInertiaLink>
                                        <BreezeButton @click="deleteRole(role)"
                                                      v-show="[$page.props.admin_role, $page.props.public_role].indexOf(role.name) === -1">
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
    name: 'OkapiUserList',
    components: {
        BreezeAuthenticatedLayout,
        BreezeButton,
        InertiaHead: Head,
        ButtonInertiaLink,
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
