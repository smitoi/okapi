<template>
    <template v-if="rulesForCurrentType.includes('required')">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.required"/>
            <span class="ml-2 text-sm text-gray-600">Required</span>
        </label>
    </template>
    <template v-if="rulesForCurrentType.includes('unique')">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.unique"/>
            <span class="ml-2 text-sm text-gray-600">Unique</span>
        </label>
    </template>
    <template v-if="rulesForCurrentType.includes('min')">
        <BreezeLabel :for="modelValue.min" value="Minimum"/>
        <BreezeInput type="text" class="mt-1 block w-1/2"
                     v-model="modelValue.min"/>
    </template>
    <template v-if="rulesForCurrentType.includes('max')">
        <BreezeLabel :for="modelValue.max" value="Maximum"/>
        <BreezeInput type="text" class="mt-1 block w-1/2"
                     v-model="modelValue.max"/>
    </template>
    <template v-if="rulesForCurrentType.includes('accepted')">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.accepted" name="radio-checked" class="rounded-circle"
                            @input="modelValue.declined = false;"/>
            <span class="ml-2 text-sm text-gray-600">Accepted</span>
        </label>
    </template>
    <template v-if="rulesForCurrentType.includes('declined')">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.declined" name="radio-checked" class="rounded-circle"
                            @input="modelValue.accepted = false;"/>
            <span class="ml-2 text-sm text-gray-600">Declined</span>
        </label>
    </template>
</template>

<script>
import {computed} from "vue";
import BreezeInput from "@/Components/Breeze/Input";
import BreezeLabel from "@/Components/Breeze/Label";
import BreezeCheckbox from "@/Components/Breeze/Checkbox";

export default {
    name: 'OkapiFieldSwitchComponent',
    components: {
        BreezeInput,
        BreezeLabel,
        BreezeCheckbox,
    },
    props: {
        fieldType: {
            type: String,
            required: true,
        },
        modelValue: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:modelValue.required',
        'update:modelValue.unique',
        'update:modelValue.min',
        'update:modelValue.max',
        'update:modelValue.accepted',
        'update:modelValue.declined',
    ],
    setup(props) {
        const rulesForCurrentType = computed(() => {
            switch (props.fieldType) {
                case 'number':
                    return ['required', 'unique', 'min', 'max'];
                case 'string':
                    return ['required', 'unique', 'min', 'max'];
                case 'enum':
                    return ['required', 'unique'];
                case 'boolean':
                    return ['accepted', 'declined'];
                case 'file':
                    return ['required'];
                case 'date':
                    return ['required'];
                case 'hour':
                    return ['required'];
                case 'default':
                    return [];

            }
        });

        return {
            rulesForCurrentType,
        }
    }
}

</script>
