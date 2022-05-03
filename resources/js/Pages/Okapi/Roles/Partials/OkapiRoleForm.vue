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
                        <div>
                            <BreezeLabel for="slug" value="Slug"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.slug"
                                         required autocomplete="slug"
                                         @input="customSlug = true"/>
                            <BreezeInputError :message="form.errors.slug"></BreezeInputError>
                        </div>
                        <label class="flex items-center mt-4 mb-4">
                            <BreezeCheckbox name="api_login" v-model:checked="form.api_login"/>
                            <span class="ml-2 text-sm text-gray-600">Can login using API</span>
                        </label>
                        <label class="flex items-center mt-4 mb-4">
                            <BreezeCheckbox name="api_register" v-model:checked="form.api_register"/>
                            <span class="ml-2 text-sm text-gray-600">Can register using API</span>
                        </label>
                        <label class="flex items-center mt-4 mb-4" v-for="permission in permissions"
                               :key="permission.id">
                            <BreezeCheckbox v-model:checked="form.permissions"
                                            :value="permission.id"/>
                            <span class="ml-2 text-sm text-gray-600">{{ getLabelForPermission(permission) }}</span>
                        </label>
                        <BreezeInputError :message="form.errors.permissions"></BreezeInputError>
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
import {useForm} from "@inertiajs/inertia-vue3";

import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeCheckbox from '@/Components/Breeze/Checkbox.vue';
import BreezeButton from '@/Components/Breeze/Button.vue';
import {ref} from "vue";
import slugify from "@/utils/slugify";


export default {
    name: 'OkapiTypeForm',
    components: {
        BreezeInput,
        BreezeCheckbox,
        BreezeLabel,
        BreezeInputError,
        BreezeButton,
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
        role: {
            type: Object,
            required: false,
        },
    },
    setup(props) {
        let form = null;

        if (props.role) {
            form = useForm({
                name: props.role.name,
                slug: props.role.slug,
                permissions: props.role.permissions.map(item => item.id),
                api_register: props.role.api_register,
                api_login: props.role.api_login,
            });
        } else {
            form = useForm({
                name: '',
                slug: '',
                permissions: [],
                api_register: false,
                api_login: false,
            });
        }

        const submit = () => {
            if (props.createForm) {
                form.post(route('okapi-roles.store'));
            } else {
                form.put(route('okapi-roles.update', props.role.id));
            }
        };

        const getLabelForPermission = (permission) => {
            const type = props.types.find((item) => item.id === permission.okapi_type_id);
            const action = permission.name.split('.')[1];
            return action.charAt(0).toUpperCase() + action.toLowerCase().slice(1) + ' ' + type.name;
        }

        const customSlug = ref(Boolean(props.role));
        const handleSlug = () => {
            if (!customSlug.value) {
                form.slug = slugify(form.name);
            }
        };

        return {
            customSlug,
            form,
            getLabelForPermission,
            handleSlug,
            submit,
        }
    }
}
</script>
