<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div>
                            <BreezeLabel for="name" value="Name"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.name"
                                         required autocomplete="name"
                                         @blur="handleSlug"/>
                            <BreezeInputError :message="form.errors.name"></BreezeInputError>
                        </div>
                        <div class="mt-4">
                            <BreezeLabel for="slug" value="Slug"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.slug"
                                         required autocomplete="slug"
                                         @input="customSlug = true"/>
                            <BreezeInputError :message="form.errors.slug"></BreezeInputError>
                        </div>
                        <label class="flex items-center mt-4 mb-4">
                            <BreezeCheckbox name="is_collection" v-model:checked="form.is_collection"/>
                            <span class="ml-2 text-sm text-gray-600">Is collection?</span>
                        </label>
                        <div class="mb-8">
                            <BreezeButton @click.prevent="addField">
                                Add new field
                            </BreezeButton>
                            <div v-for="(_, index) of form.fields" :key="index" class="mt-4">
                                <BreezeLabel for="field">Field {{ index + 1 }}</BreezeLabel>
                                <BreezeInput type="text" class="mt-1 block w-full"
                                             v-model="form.fields[index].name"
                                             required/>
                                <BreezeSelect class="mt-2" v-model="form.fields[index].type"
                                              v-bind:keys="fieldTypes" @input="changeFieldType(index)"></BreezeSelect>
                                <BreezeButton class="bg-red-900 ml-2" @click.prevent="removeField(index)"
                                              v-show="form.fields.length > 1">
                                    Remove field
                                </BreezeButton>
                                <template v-if="form.fields[index].type === 'enum'">
                                    <v-select class="mt-2"
                                              @option:selected="$emit('update:modelValue', $event.value)"
                                              v-model="form.fields[index].properties.options"
                                              :options="[]"
                                              taggable
                                              :multiple="true">
                                        <template v-slot:no-options>Type the desired options for the field.</template>
                                    </v-select>
                                </template>
                                <OkapiRuleSwitch v-if="form.fields[index].type" :field-type="form.fields[index].type"
                                                 :model-value="form.fields[index].rules"></OkapiRuleSwitch>
                                <BreezeInputError :message="form.errors[`fields.${index}.name`]"></BreezeInputError>
                                <BreezeInputError :message="form.errors[`fields.${index}.type`]"></BreezeInputError>
                            </div>
                        </div>
                        <div v-if="Object.keys(okapiTypes).length">
                            <BreezeButton @click.prevent="addRelationship">
                                Add new relationship
                            </BreezeButton>
                            <div v-for="(_, index) of form.relationships" :key="index" class="mt-4">
                                <BreezeLabel for="field">Relationship {{ index + 1 }}</BreezeLabel>
                                <BreezeInput type="text" class="mt-1 block w-full"
                                             v-model="form.relationships[index].name"
                                             required/>
                                <BreezeInput type="text" class="mt-1 block w-full"
                                             v-model="form.relationships[index].reverse_name"
                                             required/>
                                <BreezeSelect class="mt-2" v-model="form.relationships[index].type"
                                              v-bind:keys="relationshipTypes"></BreezeSelect>
                                <BreezeSelect class="mt-2 ml-2" v-model="form.relationships[index].to"
                                              v-bind:keys="okapiTypes"></BreezeSelect>
                                <template v-if="form.relationships[index].to">
                                    <BreezeSelect class="mt-2 ml-2"
                                                  v-model="form.relationships[index].display"
                                                  v-bind:keys="okapiTypesFields[form.relationships[index].to]">
                                    </BreezeSelect>
                                </template>

                                <br/>
                                <BreezeButton class="bg-red-900 ml-4 mt-2" @click.prevent="removeRelationship(index)">
                                    Remove relationship
                                </BreezeButton>
                                <BreezeInputError
                                    :message="form.errors[`relationships.${index}.name`]"></BreezeInputError>
                                <BreezeInputError
                                    :message="form.errors[`relationships.${index}.type`]"></BreezeInputError>
                                <BreezeInputError
                                    :message="form.errors[`relationships.${index}.to`]"></BreezeInputError>
                                <BreezeInputError
                                    :message="form.errors[`relationships.${index}.display`]"></BreezeInputError>
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

import vSelect from 'vue-select';
import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeSelect from '@/Components/Breeze/Select.vue';
import BreezeCheckbox from '@/Components/Breeze/Checkbox.vue';
import BreezeButton from '@/Components/Breeze/Button.vue';
import OkapiRuleSwitch from '@/Components/Okapi/Rules/Switch.vue';

import slugify from '@/utils/slugify';

import "vue-select/dist/vue-select.css";

export default {
    name: 'OkapiTypeForm',
    components: {
        vSelect,
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
            default: true,
        },
        fieldTypes: {
            type: Object,
            required: true,
        },
        relationshipTypes: {
            type: Object,
            required: true,
        },
        okapiTypes: {
            type: Object,
            required: true,
        },
        okapiTypesFields: {
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
                        properties: field.properties,
                        rules: field.rules.reduce(function (acc, obj) {
                            acc[obj.name] = obj.value;
                            return acc;
                        }, {}),
                    }
                )),
                relationships: props.type.relationships?.map((relationship) => ({
                    id: relationship.id,
                    name: relationship.name,
                    reverse_name: relationship.reverse_name,
                    type: relationship.type,
                    to: relationship.okapi_type_to_id,
                    display: relationship.okapi_field_display_id,
                }))
            });
        } else {
            form = useForm({
                name: '',
                slug: '',
                is_collection: false,
                fields: [{
                    name: '',
                    type: '',
                    properties: {},
                    rules: {},
                }],
                relationships: [],
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
                properties: {},
                rules: {},
            });
        };

        const removeField = (index) => {
            form.fields.splice(index, 1);
        };

        const addRelationship = () => {
            form.relationships.push({
                name: '',
                reverse_name: '',
                type: '',
                to: '',
                display: '',
            });
        };

        const removeRelationship = (index) => {
            form.relationships.splice(index, 1);
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
            addRelationship,
            changeFieldType,
            handleSlug,
            removeField,
            removeRelationship,
        }
    }
}
</script>
