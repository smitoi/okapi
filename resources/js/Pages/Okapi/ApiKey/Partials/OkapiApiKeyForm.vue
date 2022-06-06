<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div>
                            <BreezeLabel for="name" value="Name"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.name"
                                         required autocomplete="name" :readonly="readonly"/>
                            <BreezeInputError :message="form.errors.name"></BreezeInputError>
                        </div>
                        <div v-if="apiKey && apiKey['plaintext-token']">
                            <BreezeLabel for="api-key" value="API Key"/>
                            <BreezeInput type="text" class="mt-1 block w-full" :value="apiKey['plaintext-token']"
                                         :readonly="true"/>
                            <BreezeInputError
                                message="This value will only be displayed once and cannot be recovered - save it!"></BreezeInputError>
                        </div>
                        <div class="grid grid-cols-6">
                            <label class="flex items-center mt-4 mb-4" v-for="permission in permissions"
                                   :key="permission.id">
                                <BreezeCheckbox v-model:checked="form.permissions"
                                                :value="permission.id" :readonly="readonly"
                                                :disabled="readonly"/>
                                <span class="ml-2 text-sm text-gray-600">{{ getLabelForPermission(permission) }}</span>
                            </label>
                        </div>

                        <BreezeInputError :message="form.errors.permissions"></BreezeInputError>
                        <div class="flex items-center justify-end mt-4">
                            <template v-if="!readonly">
                                <BreezeButton class="ml-4" :class="{ 'opacity-25': form.processing }"
                                              :disabled="form.processing">
                                    {{ createForm ? 'Create' : 'Update' }}
                                </BreezeButton>
                            </template>
                            <template v-else>
                                <ButtonInertiaLink :href="route('okapi-api-keys.edit', apiKey.id)">
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
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeCheckbox from '@/Components/Breeze/Checkbox.vue';
import BreezeButton from '@/Components/Breeze/Button.vue';
import ButtonInertiaLink from '@/Components/Misc/ButtonInertiaLink.vue';
import {ref} from "vue";
import slugify from "@/utils/slugify";


export default {
    name: 'OkapiApiKeyForm',
    components: {
        BreezeInput,
        BreezeCheckbox,
        BreezeLabel,
        BreezeInputError,
        BreezeButton,
        ButtonInertiaLink,
    },
    props: {
        createForm: {
            type: Boolean,
            default: true,
        },
        permissions: {
            type: Array,
            required: true,
        },
        types: {
            type: Array,
            required: true,
        },
        apiKey: {
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

        if (props.apiKey) {
            form = useForm({
                name: props.apiKey.name,
                permissions: props.apiKey.permissions.map(item => item.id),
            });
        } else {
            form = useForm({
                name: '',
                permissions: [],
            });
        }

        const submit = () => {
            if (!props.readonly) {
                if (props.createForm) {
                    form.post(route('okapi-api-keys.store'));
                } else {
                    form.put(route('okapi-api-keys.update', props.apiKey.id));
                }
            }
        };

        const getLabelForPermission = (permission) => {
            const type = props.types.find((item) => item.id === permission.target_id);
            const action = permission.name.split('.')[1];
            return action.charAt(0).toUpperCase() + action.toLowerCase().slice(1) + ' ' + type.name;
        }

        return {
            form,
            getLabelForPermission,
            submit,
        }
    }
}
</script>
