<template>
    <BreezeLabel :for="relationship.key"
                 :value="relationship[reverse ? 'reverse_name' : 'name']"/>
    <v-select class="mt-2"
              :disabled="readonly"

              v-on:update:modelValue="setSelected"
              :reduce="option => option.value"
              v-model="modelValue"
              :options="instances"
              :multiple="multiple"></v-select>
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
        reverse: {
            type: Boolean,
            default: false,
        },
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
        const setSelected = (value) => {
            emit('update:modelValue', value);
        };

        const multiple = computed(() => {
            console.log(['has many', 'belongs to many'].indexOf(props.relationship.type) !== -1 && !props.reverse ||
                ['belongs to one', 'belongs to many'].indexOf(props.relationship.type) !== -1 && props.reverse);
            return ['has many', 'belongs to many'].indexOf(props.relationship.type) !== -1 && !props.reverse ||
                ['belongs to one', 'belongs to many'].indexOf(props.relationship.type) !== -1 && props.reverse;
        });

        return {
            multiple,
            setSelected,
        };
    }
}
</script>
