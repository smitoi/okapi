<template>
    <InertiaHead title="Types"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ type.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <InertiaLink :href="route('okapi-instances.create', type.slug)">
                            Add new {{ type.name }}
                        </InertiaLink>
                        <div class="w-full p-4">
                            <table class="table-auto w-full">
                                <thead>
                                <tr>
                                    <td>
                                        ID
                                    </td>
                                    <td v-for="field in type.fields">
                                        {{ field.name }}
                                    </td>
                                    <td>
                                        Actions
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="instance of instances" :key="instance.id">
                                    <td>
                                        {{ instance.id }}
                                    </td>
                                    <td v-for="field in type.fields">
                                        {{ getFieldValueFromInstance(instance, field)?.value }}
                                    </td>
                                    <td>
                                        <InertiaLink :href="route('okapi-instances.edit', [type.slug, instance.id])">
                                            Edit
                                        </InertiaLink>
                                        <button @click="deleteInstance(instance)">Delete</button>
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
    name: 'OkapiInstanceList',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
    },
    props: {
        type: Object,
        instances: Object,
    },
    setup(props) {
        const getFieldValueFromInstance = (instance, field) => {
            return instance.values.find((fieldValue) => {
                return fieldValue.okapi_field_id === field.id;
            });
        };

        const deleteInstance = (instance) => {
            Inertia.delete(route('okapi-instances.destroy', {'type': props.type.slug, 'instance': instance.id}));
        }

        return {
            deleteInstance,
            getFieldValueFromInstance,
        };
    }
}
</script>
