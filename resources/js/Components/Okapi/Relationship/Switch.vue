<template>
    <template v-if="['has one', 'has many', 'belongs to many', 'belongs to one'].indexOf(relationship.type) !== -1">
        <BreezeLabel :for="relationship.slug" :value="relationship.name"/>
        <v-select class="mt-2"
                  @option:selected="$emit('update:modelValue', $event.value)"
                  v-model="modelValue"
                  :options="instances"
                  :reduce="instance => instance.value"
                  :multiple="['has many', 'belongs to many'].indexOf(relationship.type) !== -1"></v-select>
    </template>
    <template v-else>
        <p>Error rendering relationship {{ relationship.name }} - undefined type {{ relationship.type }}</p>
    </template>
</template>

<script>
import BreezeLabel from "@/Components/Breeze/Label";
import vSelect from 'vue-select';
import "vue-select/dist/vue-select.css";

export default {
    name: 'OkapiRelationshipSwitchComponent',
    components: {
        BreezeLabel,
        vSelect,
    },
    props: {
        relationship: {
            type: Object,
            required: true,
        },
        instances: {
            type: Array,
            required: true,
        },
        modelValue: {}
    },
    emits: [
        'update:modelValue',
    ],
    setup() {
        const consoleLogEvent = (event) => {
            console.log()
        };

        return {
            consoleLogEvent
        };
    }
}
</script>
