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
        <template v-if="readonly">
            <img :src="modelValue" :alt="field.name">
        </template>
        <template v-else>
            <BreezeInput type="file" class="mt-1 block w-full"
                         @input="$emit('update:modelValue', $event.target.files[0])"
                         autofocus/>
        </template>

    </template>
    <template v-else-if="field.type === 'date'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <Datepicker :readonly="readonly" :disabled="readonly"
                    v-model="modelValue"
                    @update:modelValue="$emit('update:modelValue', $event.toString())"
                    autofocus></Datepicker>
    </template>
    <template v-else-if="field.type === 'hour'">
        <BreezeLabel :for="field.slug" :value="field.name"/>
        <Timepicker :readonly="readonly" :disabled="readonly"
                    :minute-interval="10"
                    v-model="modelValue"
                    @update:modelValue="$emit('update:modelValue', $event.toString())"
                    autofocus></Timepicker>
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
import Datepicker from '@vuepic/vue-datepicker';
import Timepicker from 'vue3-timepicker'

export default {
    name: 'OkapiFieldSwitchComponent',
    components: {
        BreezeInput,
        BreezeLabel,
        BreezeSelect,
        BreezeCheckbox,
        ButtonInertiaLink,
        Datepicker,
        Timepicker,
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

        const handleTest = (value) => {
            console.log(value);
        }

        return {
            transformOptionsToObject, handleTest,
        };
    }
}

</script>
