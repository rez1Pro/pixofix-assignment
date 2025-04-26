<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import { PropType } from 'vue';
// @ts-ignore
import type { PaginatedResponse } from 'vue3-lara-table';

const props = defineProps({
    roles: Object as PropType<PaginatedResponse<App.Data.RoleData>>,
});

// Define breadcrumbs for navigation
const breadcrumbs = [
    { name: 'User Management', href: route('users.roles.index') },
    { name: 'Roles' }
];

const columns = [
    { label: 'Name', key: 'name' },
    { label: 'Description', key: 'description' },
    { label: 'Permissions', key: 'permissions_count' },
    { label: 'Users', key: 'users_count' },
    { label: 'Created At', key: 'created_at' },
    { label: 'Actions', key: 'actions' },
];
</script>

<template>

    <Head title="Role Management" />

    <AuthenticatedLayout header="Role Management" description="Manage user roles and their permissions">
        <div class="space-y-6">
            <!-- Roles List -->
            <div class="border rounded-lg p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <LaraTable :columns="columns" :items="props.roles" search-key="name" enableAddItem :classes="{
                    paginationActiveButton: 'bg-blue-200 text-blue-900 dark:bg-gray-600 dark:text-white',
                }">
                    <template #add-item>
                        <div class="flex justify-end items-center">
                            <Link :href="route('users.roles.create')"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800 transition-colors duration-200">
                            <PlusIcon class="w-5 h-5" />
                            Create Role
                            </Link>
                        </div>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex gap-2">
                            <Link
                                class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800"
                                :href="route('users.roles.edit', item.id)">
                            Edit
                            </Link>
                            <Link
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500"
                                :href="route('users.roles.destroy', item.id)">
                            Delete
                            </Link>
                        </div>
                    </template>
                </LaraTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
