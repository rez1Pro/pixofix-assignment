<script setup lang="ts">
import { ArrowLeftIcon, CheckCircleIcon, CheckIcon, KeyIcon, ShieldCheckIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, PropType, ref, watch } from 'vue';
import PermissionsSelector from './PermissionsSelector.vue';
import { onMounted } from 'vue';

const props = defineProps({
    permissionGroups: {
        type: Array as PropType<{ name: string; permissions: any[] }[]>,
        required: true
    },
    role: {
        type: Object as PropType<App.Data.RoleData>,
        default: () => ({
            name: '',
            description: '',
            permissions: [] as { id: number, name: string }[],
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
    name: props.role.name || '',
    description: props.role.description || '',
    permissions: [] as string[]
});

onMounted(() => {
    form.permissions = props.role.permissions?.map((permission) => permission.name) || [];
});

const submit = () => {
    const submitMethod = props.mode === 'create' ? 'post' : 'put';

    form[submitMethod](props.submitUrl, {
        preserveScroll: true,
        onSuccess: () => {
            //
        },
        onError: () => {
            //
        },
    });
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Role Information -->
        <div class="col-span-1 lg:col-span-5 xl:col-span-4 mb-10">
            <div class="space-y-6 lg:sticky lg:top-20">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <!-- Card Header -->
                    <div
                        class="bg-gradient-to-r from-gray-500/10 via-gray-500/5 to-transparent dark:from-gray-900/30 dark:via-gray-900/10 dark:to-transparent px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 rounded-lg bg-gray-500/10 dark:bg-gray-400/10 flex items-center justify-center">
                                <ShieldCheckIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Role Information</h2>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    Role Name
                                </label>
                                <div class="relative">
                                    <input v-model="form.name" type="text"
                                        class="block w-full rounded-lg border-gray-200 pl-4 pr-10 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 focus:border-gray-500 focus:ring-gray-500 dark:text-gray-300"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        placeholder="e.g., Admin, Editor" />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <ShieldCheckIcon class="h-5 w-5"
                                            :class="form.errors.name ? 'text-red-500' : 'text-gray-400'" />
                                    </div>
                                </div>
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{
                                    form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    Description
                                </label>
                                <textarea v-model="form.description" rows="4"
                                    class="block w-full rounded-lg border-gray-200 text-sm dark:border-gray-700 dark:bg-gray-900 focus:border-gray-500 focus:ring-gray-500 dark:text-gray-300"
                                    :class="{ 'border-red-500': form.errors.description }"
                                    placeholder="Describe the role's responsibilities and access levels..."></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{
                                    form.errors.description }}</p>
                            </div>
                        </div>

                        <!-- Role Preview Card -->
                        <div
                            class="space-y-4 mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Role Preview</h3>

                            <!-- Role Badge -->
                            <div class="flex items-center gap-2">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-lg bg-gray-500/10 flex items-center justify-center">
                                        <ShieldCheckIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                    </div>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                        {{ form.name || 'Role Name' }}
                                    </span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">
                                        {{ form.description || 'Role description will appear here' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Role Statistics -->
                        <div class="mt-4 sm:mt-6 grid grid-cols-2 gap-3 sm:gap-4">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-gray-500/10 flex items-center justify-center">
                                        <UserGroupIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                    </div>
                                    <div>
                                        <span
                                            class="block text-2xl font-semibold text-gray-900 dark:text-white">0</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Users</span>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-gray-500/10 flex items-center justify-center">
                                        <KeyIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                    </div>
                                    <div>
                                        <span class="block text-2xl font-semibold text-gray-900 dark:text-white">
                                            {{ form.permissions.length }}
                                        </span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Permissions</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Guidelines -->
                        <div class="mt-4 sm:mt-6 space-y-4">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Role Guidelines
                            </h3>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div
                                            class="h-5 w-5 rounded-full bg-gray-500/10 flex items-center justify-center">
                                            <CheckIcon class="h-3 w-3 text-gray-600 dark:text-gray-400" />
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Use descriptive names that reflect the role's responsibilities
                                    </p>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div
                                            class="h-5 w-5 rounded-full bg-gray-500/10 flex items-center justify-center">
                                            <CheckIcon class="h-3 w-3 text-gray-600 dark:text-gray-400" />
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Assign minimum required permissions following the principle of least
                                        privilege
                                    </p>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div
                                            class="h-5 w-5 rounded-full bg-gray-500/10 flex items-center justify-center">
                                            <CheckIcon class="h-3 w-3 text-gray-600 dark:text-gray-400" />
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Document the role's purpose and scope in the description
                                    </p>
                                </li>
                            </ul>
                        </div>

                        <!-- Quick Tips -->
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Quick Tips</h3>
                            <ul class="space-y-2">
                                <li class="flex gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <div class="flex-shrink-0 mt-1">•</div>
                                    Use clear, descriptive names for roles
                                </li>
                                <li class="flex gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <div class="flex-shrink-0 mt-1">•</div>
                                    Include the role's scope in the description
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Updated PermissionsSelector wrapper -->
        <div class="col-span-1 lg:col-span-7 xl:col-span-8 mb-10">

            <div v-if="(Object.keys(form.errors)).length > 0"
                class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <div class="text-sm text-red-600 dark:text-red-400">
                    <p v-for="(error, index) in form.errors" :key="index">{{ error }}</p>
                </div>
            </div>
            <PermissionsSelector v-model="form.permissions" :role="role"
                :preselected-permissions="role.permissions as Array<{ id: number; name: string }>"
                :permission-groups="permissionGroups" />
        </div>

        <!-- Updated footer for better mobile responsiveness -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-3 px-4 sm:py-4 sm:px-6 lg:px-8">
            <div class="max-w-[1600px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-gray-500/10 flex items-center justify-center">
                        <KeyIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ form.permissions.length }} permissions selected
                    </span>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <Link :href="backUrl"
                        class="w-full sm:w-auto text-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancel
                    </Link>
                    <button @click="submit"
                        class="w-full sm:w-auto relative inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all disabled:opacity-50"
                        :disabled="form.processing">
                        <div v-if="form.processing"
                            class="absolute inset-0 flex items-center justify-center bg-gray-600 rounded-lg">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>

                            {{ mode === 'create' ? 'Creating Role...' : 'Updating Role...' }}
                        </div>
                        <span :class="{ 'invisible': form.processing }">
                            <CheckCircleIcon class="h-5 w-5" />
                        </span>
                        <span :class="{ 'invisible': form.processing }">
                            {{ mode === 'create' ? 'Create Role' : 'Update Role' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>