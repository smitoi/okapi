<template>
    <template v-if="['has one', 'has many', 'belongs to many', 'belongs to one'].indexOf(getType) !== -1">
        <BreezeLabel :for="getSlug"
                     :value="getName"/>
        <v-select class="mt-2"
                  @option:selected="setSelected"
                  :reduce="option => option.value"
                  v-model="modelValue"
                  :options="instances"
                  :multiple="multiple"></v-select>
    </template>
    <template v-else>
        <p>Error rendering relationship {{ getName }} - undefined type {{ getType }}</p>
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
        relationshipReverses: {
            type: Object,
            required: true,
        },
        modelValue: {}
    },
    emits: [
        'update:modelValue',
    ],
    setup(props, {emit}) {
        const getName = computed(() => {
            return props.type.id === props.relationship.okapi_type_from_id ? props.relationship.name : props.relationship.reverse_name;
        });

        const getSlug = computed(() => {
            return props.type.id === props.relationship.okapi_type_from_id ? props.relationship.slug : props.relationship.reverse_slug;
        });

        const getType = computed(() => {
            return props.type.id === props.relationship.okapi_type_from_id ? props.relationship.type : props.relationshipReverses[props.relationship.type];
        });

        const setSelected = (element) => {
            if (element.map) {
                emit('update:modelValue', element.map(item => item.value));
            } else {
                emit('update:modelValue', element.value);
            }
        };

        const multiple = computed(() => {
            return ['has many', 'belongs to many'].indexOf(getType.value) !== -1;
        });

        return {
            multiple,
            setSelected,
            getName,
            getSlug,
            getType,
        };
    }
}
</script>
