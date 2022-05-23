<template>
    <InertiaHead title="Users"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Users
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <ButtonInertiaLink :href="route('okapi-users.create')">
                            Add new user
                        </ButtonInertiaLink>
                        <div class="table-auto w-full border-collapse rounded-lg p-8">
                            <table class="table-auto w-full">
                                <thead>
                                <tr>
                                    <th class="border-b text-left">
                                        Name
                                    </th>
                                    <th class="border-b text-left">
                                        Email
                                    </th>
                                    <th class="border-b text-left">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="user of users" :key="user.id">
                                    <td class="p-4">
                                        {{ user.name }}
                                    </td>
                                    <td class="p-4">
                                        {{ user.email }}
                                    </td>
                                    <td class="p-4">
                                        <ButtonInertiaLink :href="route('okapi-users.edit', user.id)" class="mr-2">
                                            Edit
                                        </ButtonInertiaLink>
                                        <template v-if="canDeleteUser">
                                            <BreezeButton @click="deleteUser(user)">Delete</BreezeButton>
                                        </template>
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
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink.vue';

export default {
    name: 'OkapiUserList',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
        BreezeButton,
        ButtonInertiaLink,
    },
    props: {
        users: Array,
    },
    setup(props) {
        const deleteUser = (user) => {
            Inertia.delete(route('users.destroy', user.id));
        }

        const canDeleteUser = props.users.length !== 1;

        return {
            deleteUser,
            canDeleteUser,
        };
    }
}
</script>
