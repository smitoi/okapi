<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div v-for="field of type.fields">
                            <OkapiFieldSwitch :field="field"
                                              v-model="form[field.slug]">
                            </OkapiFieldSwitch>
                            <BreezeInputError :message="form.errors[type.slug]"></BreezeInputError>
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
import BreezeButton from '@/Components/Breeze/Button.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeSelect from '@/Components/Breeze/Select.vue';
import OkapiFieldSwitch from '@/Components/Okapi/Fields/Switch.vue';
import {useForm} from "@inertiajs/inertia-vue3";

export default {
    name: 'OkapiInstanceForm',
    components: {
        BreezeInput,
        BreezeLabel,
        BreezeInputError,
        BreezeButton,
        BreezeSelect,
        OkapiFieldSwitch,
    },
    props: {
        createForm: {
            type: Boolean,
            required: true,
        },
        type: {
            type: Object,
            required: true,
        },
        instance: {
            type: Object,
            required: false,
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

        if (props.instance) {
            props.type.fields.forEach(field => {
                const value = getFieldValueFromInstance(props.instance, field)?.value;
                formObject[field.slug] = field.type === 'boolean' ? Boolean(Number(value)) : value
            });
        } else {
            props.type.fields.forEach(field => formObject[field.slug] = null);
        }

        form = useForm(formObject);

        const submit = () => {
            if (props.createForm) {
                form.post(route('.store', props.type.slug));
            } else {
                form.put(route('okapi-instances.update', [props.type.slug, props.instance.id]));
            }
        };

        return {
            form,
            submit,
        }
    }
}
</script>