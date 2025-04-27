<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: 'admin@example.com',
    password: 'password',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold">Login</h2>
            <p class="mt-2 text-sm text-gray-600">
                Don't have an account?
                <Link :href="route('register')" class="text-emerald-600">
                Register
                </Link>
            </p>
        </div>

        <div v-if="status" class="mb-4 bg-emerald-50 p-4 rounded">
            <p class="text-emerald-800">{{ status }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Input -->
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" required autofocus />
                <InputError :message="form.errors.email" />
            </div>

            <!-- Password Input -->
            <div>
                <div class="flex justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-emerald-600">
                    Forgot password?
                    </Link>
                </div>
                <TextInput id="password" type="password" v-model="form.password" class="mt-1 block w-full" required />
                <InputError :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <Checkbox id="remember" v-model:checked="form.remember" name="remember" />
                <label for="remember" class="ml-2 text-sm text-gray-600">
                    Remember me
                </label>
            </div>

            <!-- Submit Button -->
            <PrimaryButton class="w-full justify-center" :disabled="form.processing">
                {{ form.processing ? 'Logging in...' : 'Login' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
