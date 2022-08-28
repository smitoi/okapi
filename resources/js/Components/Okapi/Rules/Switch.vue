<template>
    <template v-if="typesWhereRuleApply('required').includes(fieldType)">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.required"/>
            <span class="ml-2 text-sm text-gray-600">Required</span>
        </label>
    </template>
    <template v-if="typesWhereRuleApply('unique').includes(fieldType)">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.unique"/>
            <span class="ml-2 text-sm text-gray-600">Unique</span>
        </label>
    </template>
    <template v-if="typesWhereRuleApply('min').includes(fieldType)">
        <BreezeLabel :for="modelValue.min" value="Minimum"/>
        <BreezeInput type="text" class="mt-1 block w-1/2"
                     v-model="modelValue.min"/>
    </template>
    <template v-if="typesWhereRuleApply('max').includes(fieldType)">
        <BreezeLabel :for="modelValue.max" value="Maximum"/>
        <BreezeInput type="text" class="mt-1 block w-1/2"
                     v-model="modelValue.max"/>
    </template>
    <template v-if="typesWhereRuleApply('accepted').includes(fieldType)">
        <label class="flex items-center mt-4 mb-4">
            <BreezeCheckbox v-model:checked="modelValue.accepted" name="radio-checked" class="rounded-circle"
                            @input="modelValue.declined = false;"/>
            <span class="ml-2 text-sm text-gray-600">Accepted</span>
        </label>
    </template>
    <template v-if="typesWhereRuleApply('declined').includes(fieldType)">
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
    name: 'OkapiFieldRuleSwitchComponent',
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
    setup() {
        const typesWhereRuleApply = (rule) => {
            switch (rule) {
                case 'required':
                    return ['string', 'text', 'rich_text', 'email', 'password', 'email', 'integer', 'enum', 'date',
                        'hour', 'file', 'json'];
                case 'unique':
                    return ['string', 'email', 'integer', 'enum', 'date', 'hour'];
                case 'min':
                    return ['string', 'text', 'rich_text', 'email', 'password', 'integer'];
                case 'max':
                    return ['string', 'text', 'rich_text', 'email', 'password', 'integer'];
                case 'accepted':
                    return ['boolean'];
                case 'declined':
                    return ['boolean'];
                case 'default':
                    return [];
            }
        };

        return {
            typesWhereRuleApply,
        }
    }
}

</script>
