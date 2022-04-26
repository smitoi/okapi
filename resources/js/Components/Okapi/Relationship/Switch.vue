<template>
    <template v-if="['has one', 'has many', 'belongs to many', 'belongs to one'].indexOf(relationship.type) !== -1">
        <BreezeLabel :for="relationship.slug" :value="relationship.name"/>
        <v-select class="mt-2"
                  @option:selected="setSelected"
                  :reduce="option => option.value"
                  v-model="modelValue"
                  :options="instances"
                  :multiple="multiple"></v-select>
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
    setup(props, {emit}) {
        const setSelected = (element) => {
            if (element.map) {
                emit('update:modelValue', element.map(item => item.value));
            } else {
                emit('update:modelValue', element.value);
            }
        };

        const multiple = ['has many', 'belongs to many'].indexOf(props.relationship.type) !== -1;

        return {
            multiple,
            setSelected,
        };
    }
}
</script>
