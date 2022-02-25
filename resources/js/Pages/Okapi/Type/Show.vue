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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-for="field of type.fields" :key="field.id">
                            <h4>{{ field.name }} - {{ field.type }}</h4>
                        </div>
                        <InertiaLink :href="route('okapi-instances.index', type.slug)">
                            See instances
                        </InertiaLink>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated";
import {Head, Link} from "@inertiajs/inertia-vue3";

export default {
    name: 'OkapiTypeShow',
    components: {
        BreezeAuthenticatedLayout,
        InertiaHead: Head,
        InertiaLink: Link,
    },
    props: {
        type: {
            type: Object,
            required: true,
        },
    },
}
</script>
