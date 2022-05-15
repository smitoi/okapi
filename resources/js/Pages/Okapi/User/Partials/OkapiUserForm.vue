<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div>
                            <BreezeLabel for="name" value="Name"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.name"
                                         required autocomplete="name"/>
                            <BreezeInputError :message="form.errors.name"></BreezeInputError>
                        </div>
                        <div>
                            <BreezeLabel for="email" value="Email"/>
                            <BreezeInput type="text" class="mt-1 block w-full" v-model="form.email"
                                         required autocomplete="email"/>
                            <BreezeInputError :message="form.errors.email"></BreezeInputError>
                        </div>
                        <div>
                            <BreezeLabel for="password" value="Password"/>
                            <BreezeInput type="password" class="mt-1 block w-full" v-model="form.password"/>
                            <BreezeInputError :message="form.errors.password"></BreezeInputError>
                        </div>
                        <div>
                            <BreezeLabel for="password_confirmation" value="Password confirmation"/>
                            <BreezeInput type="password" class="mt-1 block w-full" v-model="form.password_confirmation"/>
                            <BreezeInputError :message="form.errors.password_confirmation"></BreezeInputError>
                        </div>
                        <div class="grid grid-cols-6">
                            <label class="flex items-center mt-4 mb-4" v-for="role in roles"
                                   :key="role.id">
                                <BreezeCheckbox v-model:checked="form.roles"
                                                :value="role.id"/>
                                <span class="ml-2 text-sm text-gray-600">{{ role.name }}</span>
                            </label>
                        </div>
                        <BreezeInputError :message="form.errors.roles"></BreezeInputError>
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
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import BreezeLabel from '@/Components/Breeze/Label.vue';
import BreezeInput from '@/Components/Breeze/Input.vue';
import BreezeInputError from '@/Components/Breeze/InputError.vue';
import BreezeCheckbox from '@/Components/Breeze/Checkbox.vue';
import BreezeButton from '@/Components/Breeze/Button.vue';
import ButtonLink from '@/Components/Misc/ButtonLink.vue';
import {ref} from "vue";
import slugify from "@/utils/slugify";


export default {
    name: 'OkapiUserForm',
    components: {
        BreezeInput,
        BreezeCheckbox,
        BreezeLabel,
        BreezeInputError,
        BreezeButton,
        ButtonLink,
    },
    props: {
        createForm: {
            type: Boolean,
            default: true,
        },
        roles: {
            type: Array,
            required: true,
        },
        user: {
            type: Object,
            required: false,
        },
    },
    setup(props) {
        let form = null;

        if (props.user) {
            form = useForm({
                name: props.user.name,
                email: props.user.email,
                password: '',
                password_confirmation: '',
                roles: props.user.roles.map(item => item.id),
            });
        } else {
            form = useForm({
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                roles: [],
            });
        }

        const submit = () => {
            if (props.createForm) {
                form.post(route('okapi-users.store'));
            } else {
                form.put(route('okapi-users.update', props.user.id));
            }
        };

        return {
            form,
            submit,
        }
    }
}
</script>
