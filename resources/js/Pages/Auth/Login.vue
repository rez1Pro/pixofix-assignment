<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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

        <!-- Header -->
        <div class="text-center">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Welcome back 👋
            </h2>
            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                Don't have an account?
                <Link :href="route('register')"
                    class="font-medium text-emerald-600 hover:text-emerald-500 transition-colors">
                Sign up for free
                </Link>
            </p>
        </div>

        <!-- Status Message -->
        <div v-if="status" class="mt-6 rounded-lg bg-emerald-50 p-4 dark:bg-emerald-900/30">
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ status }}</p>
        </div>

        <!-- Social Login Buttons -->
        <div class="mt-6 grid grid-cols-2 gap-3">
            <button type="button"
                class="group relative flex w-full items-center justify-center gap-2 rounded-lg bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700/70 transition-all">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.164 6.839 9.49.5.09.682-.218.682-.486 0-.236-.009-.866-.014-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12c0-5.523-4.477-10-10-10z" />
                </svg>
                GitHub
            </button>
            <button type="button"
                class="group relative flex w-full items-center justify-center gap-2 rounded-lg bg-[#4285F4] px-3 py-2.5 text-sm font-medium text-white hover:bg-[#3367D6] transition-colors">
                <svg class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="currentColor"
                        d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z" />
                </svg>
                Google
            </button>
        </div>

        <div class="relative mt-8">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-4 text-gray-500 dark:bg-gray-800 dark:text-gray-400">or continue with
                    email</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-6">
            <!-- Email Input -->
            <div class="space-y-2">
                <InputLabel for="email" value="Email address" class="text-gray-700 dark:text-gray-300" />
                <TextInput id="email" type="email" v-model="form.email"
                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder-gray-500 dark:focus:ring-emerald-500 transition-shadow"
                    placeholder="name@company.com" required autofocus autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" class="text-gray-700 dark:text-gray-300" />
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-500 transition-colors">
                    Forgot password?
                    </Link>
                </div>
                <TextInput id="password" type="password" v-model="form.password"
                    class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder-gray-500 dark:focus:ring-emerald-500 transition-shadow"
                    placeholder="••••••••" required autocomplete="current-password" />
                <InputError :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <Checkbox id="remember" v-model:checked="form.remember" name="remember"
                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:ring-offset-gray-800" />
                <label for="remember" class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                    Stay signed in for a week
                </label>
            </div>

            <!-- Submit Button -->
            <PrimaryButton
                class="relative w-full justify-center rounded-lg bg-emerald-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition-all duration-200"
                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                <svg v-if="form.processing" class="-ml-1 mr-3 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                {{ form.processing ? 'Signing in...' : 'Sign in to your account' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
