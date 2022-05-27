<template>
    <template
        v-if="['has one', 'has many', 'belongs to many', 'belongs to one'].indexOf(props.relationship.type) !== -1">
        <BreezeLabel :for="props.relationship.toType.slug"
                     :value="props.relationship.toType.name"/>
        <v-select class="mt-2"
                  :disabled="readonly"
                  @option:selected="setSelected"
                  :reduce="option => option.value"
                  v-model="modelValue"
                  :options="instances"
                  :multiple="multiple"></v-select>
    </template>
    <template v-else>
        <p>Error rendering relationship {{ props.relationship.toType.name }} - undefined type {{
                props.relationship.type
            }}</p>
    </template>
</template>

<script>
import BreezeLabel from "@/Components/Breeze/Label";
import vSelect from 'vue-select';
import "vue-select/dist/vue-select.css";
import {computed} from "vue";

export default {
    name: 'OkapiRelationshipSwitchComponent',
    components: {
        BreezeLabel,
        vSelect,
    },
    props: {
        type: {
            type: Object,
            required: true,
        },
        relationship: {
            type: Object,
            required: true,
        },
        instances: {
            type: Array,
            required: true,
        },
        modelValue: {},
        readonly: {
            type: Boolean,
            default: false,
        },
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

        const multiple = computed(() => {
            return ['has many', 'belongs to many'].indexOf(props.relationship.type) !== -1;
        });

        return {
            multiple,
            setSelected,
        };
    }
}
</script>
