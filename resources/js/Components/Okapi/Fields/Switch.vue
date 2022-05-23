<template>
    <template v-if="field.type === 'string'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <BreezeInput type="text" class="mt-1 block w-full"
                     v-model="modelValue"
                     :readonly="readonly"
                     @input="$emit('update:modelValue', $event.target.value)"
                     autofocus :autocomplete="field.slug"/>
    </template>
    <template v-else-if="field.type === 'number'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <BreezeInput type="number" class="mt-1 block w-full"
                     v-model="modelValue"
                     :readonly="readonly"
                     @input="$emit('update:modelValue', $event.target.value)"
                     autofocus :autocomplete="field.slug"/>
    </template>
    <template v-else-if="field.type === 'enum'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <BreezeSelect class="mt-2" v-model="modelValue" @input="$emit('update:modelValue', $event.target.value)"
                      v-bind:keys="transformOptionsToObject(field.properties.options)"
                      :disabled="readonly"></BreezeSelect>
    </template>
    <template v-else-if="field.type === 'boolean'">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue" @input="$emit('update:modelValue', $event.target.checked)"
                            :disabled="readonly"/>
            <span class="ml-2 text-sm text-gray-600">{{ field.name }}</span>
        </label>
    </template>
    <template v-else-if="field.type === 'file'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <BreezeInput type="file" class="mt-1 block w-full"
                     :readonly="readonly" :disabled="readonly"
                     @input="$emit('update:modelValue', $event.target.files[0])"
                     autofocus :autocomplete="field.slug"/>
    </template>
    <template v-else>
        <p>Error rendering field {{ field.name }} - undefined type {{ field.type }}</p>
    </template>
</template>

<script>
import BreezeInput from "@/Components/Breeze/Input";
import BreezeLabel from "@/Components/Breeze/Label";
import BreezeSelect from '@/Components/Breeze/Select.vue';
import BreezeCheckbox from "@/Components/Breeze/Checkbox";
import ButtonInertiaLink from "@/Components/Misc/ButtonInertiaLink";

export default {
    name: 'OkapiFieldSwitchComponent',
    components: {
        BreezeInput,
        BreezeLabel,
        BreezeSelect,
        BreezeCheckbox,
        ButtonInertiaLink,
    },
    props: {
        field: {
            type: Object,
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
        const transformOptionsToObject = (optionsArray) => {
            const optionsObject = {};

            optionsArray.forEach(option => {
                optionsObject[option] = option;
            });

            if (optionsObject[props.modelValue] === undefined) {
                emit('update:modelValue', '')
            }

            return optionsObject;
        }

        return {
            transformOptionsToObject,
        };
    }
}

</script>
