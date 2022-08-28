<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <ButtonInertiaLink
                        :href="type.is_collection ? route('okapi-instances.index', type.slug) : route('okapi-types.index')"
                        class="mb-2 mr-2">
                        Go back
                    </ButtonInertiaLink>
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
                                                     :relationship="relationship"
                                                     :instances="relationship.options"
                                                     :readonly="readonly"
                                                     v-model="form[relationship.key]">
                            </OkapiRelationshipSwitch>
                            <BreezeInputError :message="form.errors[relationship.slug]"></BreezeInputError>
                        </div>
                        <div v-for="relationship of reverseRelationships">
                            <OkapiRelationshipSwitch :reverse="true"
                                                     :type="type"
                                                     :relationship="relationship"
                                                     :instances="relationship.options"
                                                     :readonly="readonly"
                                                     v-model="form[relationship.key]">
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
        reverseRelationships: {
            type: Object,
            required: true,
        },
        instance: {
            type: Object,
            required: false,
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        let form = null;
        const formObject = {};

        if (props.instance) {
            props.type.fields.forEach(field => {
                if (field.type !== 'file' && field.type !== 'password') {
                    const value = formObject[field.slug] = props.instance[field.slug];
                    formObject[field.slug] = field.type === 'boolean' ? Boolean(Number(value)) : value;
                }
            });

            props.relationships.forEach(relationship => formObject[relationship.key] = props.instance[relationship.key]);
            props.reverseRelationships.forEach(relationship => formObject[relationship.key] = props.instance[relationship.key]);
        } else {
            props.type.fields.forEach(field => formObject[field.slug] = field.type === 'boolean' ? false : '');
            props.relationships.forEach(relationship => formObject[relationship.key] = null);
            props.reverseRelationships.forEach(relationship => formObject[relationship.key] = null);
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
