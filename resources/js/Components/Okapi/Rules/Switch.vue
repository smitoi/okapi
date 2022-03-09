<template>
    <template v-if="fieldType === 'text' || fieldType === 'string' || fieldType === 'number'">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.required"/>
            <span class="ml-2 text-sm text-gray-600">Required</span>
        </label>
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.unique"/>
            <span class="ml-2 text-sm text-gray-600">Unique</span>
        </label>
    </template>

    <template v-if="fieldType === 'string' || fieldType === 'number'">
        <BreezeLabel :for="modelValue.min" value="Minimum"/>
        <BreezeInput type="text" class="mt-1 block w-full"
                     v-model="modelValue.min"
                     autofocus/>
        <BreezeLabel :for="modelValue.max" value="Maximum"/>
        <BreezeInput type="text" class="mt-1 block w-full"
                     v-model="modelValue.max"
                     autofocus/>
    </template>

    <template v-if="fieldType === 'boolean'">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.accepted" name="radio-checked" class="rounded-circle"
                            @input="modelValue.declined = false;"/>
            <span class="ml-2 text-sm text-gray-600">Accepted</span>
        </label>
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.declined" name="radio-checked" class="rounded-circle"
                            @input="modelValue.accepted = false;"/>
            <span class="ml-2 text-sm text-gray-600">Declined</span>
        </label>
    </template>
</template>

<script>
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
}

</script>
