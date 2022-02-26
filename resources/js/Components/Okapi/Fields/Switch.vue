<template>
    <template v-if="field.type === 'text' || field.type === 'string'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <BreezeInput type="text" class="mt-1 block w-full"
                     v-model="modelValue"
                     @input="$emit('update:modelValue', $event.target.value)"
                     required autofocus :autocomplete="field.slug"/>
    </template>
    <template v-else-if="field.type === 'number'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <BreezeInput type="number" class="mt-1 block w-full"
                     v-model="modelValue"
                     @input="$emit('update:modelValue', $event.target.value)"
                     required autofocus :autocomplete="field.slug"/>
    </template>
    <template v-else-if="field.type === 'date'">

    </template>
    <template v-else-if="field.type === 'boolean'">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue" @input="$emit('update:modelValue', $event.target.checked)"/>
            <span class="ml-2 text-sm text-gray-600">{{ field.name }}</span>
        </label>
    </template>
    <template v-else>
        <p>Error rendering field {{ field.name }} - undefined type {{ field.type }}</p>
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
        field: {
            type: Object,
            required: true,
        },
        modelValue: {}
    },
    emits: [
        'update:modelValue',
    ],
}

</script>
