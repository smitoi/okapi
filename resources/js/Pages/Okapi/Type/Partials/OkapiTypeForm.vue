<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div>
                            <BreezeLabel for="name" value="Name"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.name"
                                         required autofocus autocomplete="name"
                                         @blur="handleSlug"/>
                            <BreezeInputError :message="form.errors.name"></BreezeInputError>
                        </div>
                        <div class="mt-4">
                            <BreezeLabel for="slug" value="Slug"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.slug"
                                         required autofocus autocomplete="slug"
                                         @input="customSlug = true"/>
                            <BreezeInputError :message="form.errors.slug"></BreezeInputError>
                        </div>
                        <label class="flex items-center mt-4 mb-4">
                            <BreezeCheckbox name="is_collection" v-model:checked="form.is_collection"/>
                            <span class="ml-2 text-sm text-gray-600">Is collection?</span>
                        </label>
                        <div>
                            <BreezeButton @click.prevent="addField">
                                Add new field
                            </BreezeButton>
                            <div v-for="(_, index) of form.fields" :key="index" class="mt-4">
                                <BreezeLabel for="field">Field {{ index + 1 }}</BreezeLabel>
                                <BreezeInput type="text" class="mt-1 block w-full"
                                             v-model="form.fields[index].name"
                                             required autofocus/>
                                <BreezeSelect class="mt-2" v-model="form.fields[index].type"
                                              v-bind:keys="fieldTypes" @input="changeFieldType(index)"></BreezeSelect>
                                <OkapiRuleSwitch :field-type="form.fields[index].type"
                                                 :model-value="form.fields[index].rules"></OkapiRuleSwitch>
                                <br/>
                                <BreezeButton class="bg-red-900 ml-4 mt-2" @click.prevent="removeField(index)"
                                              v-show="form.fields.length > 1">
                                    Remove field
                                </BreezeButton>
                                <BreezeInputError :message="form.errors[`fields.${index}.name`]"></BreezeInputError>
                                <BreezeInputError :message="form.errors[`fields.${index}.type`]"></BreezeInputError>
                            </div>

                            <div v-for="error of form.errors.fields">
                                <BreezeInputError :message="error"></BreezeInputError>
                            </div>
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <BreezeButton class="ml-4" :class="{ 'opacity-25': form.processing }"
                                          :disabled="form.processing">
                                {{ createForm ? 'Create' : 'Update' }}
                            </BreezeButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {ref} from "vue";
import {useForm} from "@inertiajs/inertia-vue3";

import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeSelect from '@/Components/Breeze/Select.vue';
import BreezeCheckbox from '@/Components/Breeze/Checkbox.vue';
import BreezeButton from '@/Components/Breeze/Button.vue';

import OkapiRuleSwitch from '@/Components/Okapi/Rules/Switch.vue';

import slugify from '@/utils/slugify';

export default {
    name: 'OkapiTypeForm',
    components: {
        BreezeInput,
        BreezeCheckbox,
        BreezeLabel,
        BreezeInputError,
        BreezeButton,
        BreezeSelect,
        OkapiRuleSwitch,
    },
    props: {
        createForm: {
            type: Boolean,
            required: true,
        },
        fieldTypes: {
            type: Object,
            required: true,
        },
        type: {
            type: Object,
            required: false,
        },
    },
    setup(props) {
        let form = null;

        if (props.type) {
            form = useForm({
                name: props.type.name,
                slug: props.type.slug,
                is_collection: props.type.is_collection,
                fields: props.type.fields?.map((field) => ({
                        id: field.id,
                        name: field.name,
                        type: field.type,
                        rules: field.rules,
                    }
                )),
            });
        } else {
            form = useForm({
                name: '',
                slug: '',
                is_collection: false,
                fields: [{
                    name: '',
                    type: '',
                    rules: {},
                }],
            });
        }

        const submit = () => {
            if (props.createForm) {
                form.post(route('okapi-types.store'));
            } else {
                form.put(route('okapi-types.update', props.type.slug));
            }
        };

        const addField = () => {
            form.fields.push({
                name: '',
                type: '',
                rules: {},
            });
        };

        const removeField = (index) => {
            form.fields.splice(index, 1);
        };

        const changeFieldType = (index) => {
            form.fields[index].rules = {};
        }

        const customSlug = ref(Boolean(props.type));
        const handleSlug = () => {
            if (!customSlug.value) {
                form.slug = slugify(form.name);
            }
        };

        return {
            customSlug,
            form,
            submit,
            addField,
            changeFieldType,
            handleSlug,
            removeField,
        }
    }
}
</script>
