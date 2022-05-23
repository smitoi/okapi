<template>
    <InertiaHead :title="type.name"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ type.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <ButtonInertiaLink :href="route('okapi-instances.create', type.slug)" class="mb-2">
                            Add new {{ type.name }}
                        </ButtonInertiaLink>
                        <div class="table-auto w-full border-collapse rounded-lg p-8">
                            <table class="table-auto w-full">
                                <thead>
                                <tr class="border-b text-left">
                                    <th class="p-4">
                                        ID
                                    </th>
                                    <th v-for="field in getFields(type)" class="p-4">
                                        {{ field.name }}
                                    </th>
                                    <th class="p-4">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="instance of instances" :key="instance.id">
                                    <td class="p-4">
                                        {{ instance.id }}
                                    </td>
                                    <td v-for="field in getFields(type)" class="p-4">
                                        {{ getFieldValueFromInstance(instance, field)?.value }}
                                    </td>
                                    <td class="p-4">
                                        <ButtonInertiaLink :href="route('okapi-instances.show', [type.slug, instance.id])" class="mr-2">
                                            View
                                        </ButtonInertiaLink>
                                        <ButtonInertiaLink :href="route('okapi-instances.edit', [type.slug, instance.id])" class="mr-2">
                                            Edit
                                        </ButtonInertiaLink>
                                        <BreezeButton @click="deleteInstance(instance)">Delete</BreezeButton>
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
import {Head, Link, usePage} from '@inertiajs/inertia-vue3';
import {Inertia} from "@inertiajs/inertia";
import BreezeButton from '@/Components/Breeze/Button.vue';
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink.vue';

export default {
    name: 'OkapiInstanceList',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
        BreezeButton,
        ButtonInertiaLink,
    },
    props: {
        type: Object,
        instances: Object,
    },
    setup(props) {
        const getFieldValueFromInstance = (instance, field) => {
            let value = instance.values.find((fieldValue) => {
                return fieldValue.okapi_field_id === field.id;
            });

            if (field.type === 'boolean') {
                value = Boolean(value === '1');
            }

            return value;
        };

        const getFields = (type) => {
            return type.fields.filter(field => field.type !== 'file');
        }

        const deleteInstance = (instance) => {
            Inertia.delete(route('okapi-instances.destroy', {'type': props.type.slug, 'instance': instance.id}));
        }

        return {
            deleteInstance,
            getFields,
            getFieldValueFromInstance,
        };
    }
}
</script>
