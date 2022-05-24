<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div v-for="field of type.fields" :key="field.id">
                            <OkapiFieldSwitch :field="field"
                                              v-model="form[field.slug]"
                                              :readonly="readonly">
                            </OkapiFieldSwitch>
                            <BreezeInputError :message="form.errors[field.slug]"></BreezeInputError>
                        </div>
                        <div v-for="relationship of relationships">
                            <OkapiRelationshipSwitch :type="type"
                                                     :relationship-reverses="relationshipReverses"
                                                     :relationship="relationship"
                                                     :instances="relationship.options"
                                                     :readonly="readonly"
                                                     v-model="form[relationship.okapi_type_from_id === type.id ?
                                                      relationship.slug : relationship.reverse_slug]">
                            </OkapiRelationshipSwitch>
                            <BreezeInputError :message="form.errors[relationship.slug]"></BreezeInputError>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <template v-if="!readonly">
                                <BreezeButton class="ml-4" :class="{ 'opacity-25': form.processing }"
                                              :disabled="form.processing">
                                    {{ createForm ? 'Create' : 'Update' }}
                                </BreezeButton>
                            </template>
                            <template v-else>
                                <ButtonInertiaLink :href="route('okapi-instances.edit', [type.slug, instance.id])">
                                    Edit
                                </ButtonInertiaLink>
                            </template>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BreezeButton from '@/Components/Breeze/Button.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeSelect from '@/Components/Breeze/Select.vue';
import OkapiFieldSwitch from '@/Components/Okapi/Fields/Switch.vue';
import OkapiRelationshipSwitch from '@/Components/Okapi/Relationship/Switch.vue';
import {useForm} from "@inertiajs/inertia-vue3";
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink';

export default {
    name: 'OkapiInstanceForm',
    components: {
        BreezeInput,
        BreezeLabel,
        BreezeInputError,
        BreezeButton,
        BreezeSelect,
        OkapiFieldSwitch,
        OkapiRelationshipSwitch,
        ButtonInertiaLink,
    },
    props: {
        createForm: {
            type: Boolean,
            default: true,
        },
        type: {
            type: Object,
            required: true,
        },
        relationships: {
            type: Object,
            required: true,
        },
        instance: {
            type: Object,
            required: false,
        },
        relationshipReverses: {
            type: Object,
            required: true,
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        let form = null;
        const formObject = {};

        const getFieldValueFromInstance = (instance, field) => {
            return instance.values.find((fieldValue) => {
                return fieldValue.okapi_field_id === field.id;
            });
        };

        const getRelationshipValueFromInstance = (instance, relationship) => {
            return instance.related.filter(instance =>
                instance.pivot.okapi_relationship_id === relationship.id).map(item => item?.id);
        };

        const getReverseRelationshipValueFromInstance = (instance, relationship) => {
            return instance.reverse_related.filter(instance =>
                instance.pivot.okapi_relationship_id === relationship.id).map(item => item?.id);
        };

        if (props.instance) {
            props.type.fields.forEach(field => {
                if (field.type !== 'file') {
                    const value = getFieldValueFromInstance(props.instance, field)?.value;
                    formObject[field.slug] = field.type === 'boolean' ? Boolean(Number(value)) : value;
                }
            });

            props.type.relationships.forEach(relationship => {
                formObject[relationship.slug] = getRelationshipValueFromInstance(props.instance, relationship);
            });

            props.type.reverse_relationships.forEach(relationship => {
                formObject[relationship.reverse_slug] = getReverseRelationshipValueFromInstance(props.instance, relationship);
            });
        } else {
            props.type.fields.forEach(field => formObject[field.slug] = field.type === 'boolean' ? false : '');
            props.type.relationships.forEach(relationship => formObject[relationship.slug] = null);
            props.type.reverse_relationships.forEach(relationship => formObject[relationship.reverse_slug] = null);
        }

        form = useForm(formObject);

        const submit = () => {
            if (!props.readonly) {
                if (props.createForm) {
                    form.post(route('okapi-instances.store', props.type.slug));
                } else {
                    form.put(route('okapi-instances.update', [props.type.slug, props.instance.id]));
                }
            }
        };

        return {
            form,
            submit,
        }
    }
}
</script>
