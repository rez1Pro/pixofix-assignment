<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, CalendarIcon, EnvelopeIcon, PhoneIcon, UserGroupIcon, UserIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    user: {
        type: Object as PropType<App.Data.UserData>,
        required: true
    }
});

// Define breadcrumbs for navigation
const breadcrumbs = [
    { name: 'User Management', href: route('users.index') },
    { name: 'User Details' }
];
</script>

<template>
    <AuthenticatedLayout :header="`User Details: ${user.name}`" description="View detailed information about this user."
        :breadcrumbs="breadcrumbs">

        <Head :title="`User: ${user.name}`" />

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">User Profile</h2>
                <Link :href="route('users.index')"
                    class="flex items-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                <ArrowLeftIcon class="w-4 h-4 mr-1" />
                Back to Users
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-6">
                    <div>
                        <div class="flex items-center">
                            <UserIcon class="h-5 w-5 text-gray-400 mr-2" />
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</span>
                        </div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <EnvelopeIcon class="h-5 w-5 text-gray-400 mr-2" />
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                        </div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ user.email }}</div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <PhoneIcon class="h-5 w-5 text-gray-400 mr-2" />
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</span>
                        </div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ user.phone || 'Not provided' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <div class="flex items-center">
                            <UserGroupIcon class="h-5 w-5 text-gray-400 mr-2" />
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</span>
                        </div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ user.role.name }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <CalendarIcon class="h-5 w-5 text-gray-400 mr-2" />
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified</span>
                        </div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ user.email_verified_at ? 'Verified' : 'Not verified' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                <Link :href="route('users.edit', user.id)"
                    class="inline-flex items-center px-4 py-2 bg-gray-900 dark:bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-700 focus:bg-gray-700 dark:focus:bg-gray-700 active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Edit User
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>