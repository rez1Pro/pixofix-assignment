<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { ArrowLeftIcon, EnvelopeIcon, KeyIcon, PhoneIcon, UserGroupIcon, UserIcon } from '@heroicons/vue/24/outline';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, PropType } from 'vue';

const props = defineProps({
    roles: {
        type: Array as PropType<App.Data.RoleData[]>,
        required: true
    },
    user: {
        type: Object as PropType<App.Data.UserData>,
        default: () => ({
            name: '',
            email: '',
            phone: '',
            role: null
        })
    },
    mode: {
        type: String as PropType<'create' | 'edit'>,
        default: 'create'
    },
    submitUrl: {
        type: String,
        required: true
    },
    backUrl: {
        type: String,
        required: true
    }
});

// Form and validation setup
const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '',
    role_id: props.user.role?.id || '',
    password: '',
    password_confirmation: ''
});

const title = computed(() => props.mode === 'create' ? 'Create User' : 'Edit User');
const buttonText = computed(() => props.mode === 'create' ? 'Create User' : 'Update User');

const submit = () => {
    const submitMethod = props.mode === 'create' ? 'post' : 'put';

    form[submitMethod](props.submitUrl, {
        preserveScroll: true,
        onSuccess: () => {
            if (props.mode === 'create') {
                form.reset();
            }
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h2>
            <Link :href="backUrl"
                class="flex items-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
            <ArrowLeftIcon class="w-4 h-4 mr-1" />
            Back to Users
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Name" />
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <UserIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput id="name" type="text" class="pl-10 block w-full" v-model="form.name" required
                            autofocus placeholder="Enter user's name" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email" />
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput id="email" type="email" class="pl-10 block w-full" v-model="form.email" required
                            placeholder="Enter user's email" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Phone -->
                <div>
                    <InputLabel for="phone" value="Phone" />
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <PhoneIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput id="phone" type="text" class="pl-10 block w-full" v-model="form.phone"
                            placeholder="Enter user's phone number (optional)" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <!-- Role -->
                <div>
                    <InputLabel for="role_id" value="Role" />
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <UserGroupIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <SelectInput id="role_id" class="pl-10 block w-full" v-model="form.role_id" required>
                            <option value="" disabled>Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
                        </SelectInput>
                    </div>
                    <InputError class="mt-2" :message="form.errors.role_id" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password -->
                <div>
                    <InputLabel for="password"
                        :value="mode === 'create' ? 'Password' : 'New Password (leave blank to keep current)'" />
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <KeyIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput id="password" type="password" class="pl-10 block w-full" v-model="form.password"
                            :required="mode === 'create'" autocomplete="new-password" placeholder="Enter password" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <KeyIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput id="password_confirmation" type="password" class="pl-10 block w-full"
                            v-model="form.password_confirmation" :required="mode === 'create'"
                            autocomplete="new-password" placeholder="Confirm password" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <Link :href="backUrl"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                Cancel
                </Link>
                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ buttonText }}
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>