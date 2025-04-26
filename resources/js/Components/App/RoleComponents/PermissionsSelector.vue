<script setup lang="ts">
import { KeyIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';
import { PropType, computed, onMounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array as PropType<string[]>,
        required: true
    },
    permissionGroups: {
        type: Array as PropType<{ name: string; permissions: { id: number; name: string }[] }[]>,
        required: true
    },
    preselectedPermissions: {
        type: Array as PropType<{ id: number; name: string }[]>,
        required: false
    },
    role: {
        type: Object as PropType<App.Data.RoleData>,
        required: false
    }
});

const emit = defineEmits(['update:modelValue', 'validate']);

const selectAllInGroup = (groupPermissions: any[]) => {
    const newPermissions = [...props.modelValue];
    groupPermissions.forEach(permission => {
        if (!newPermissions.includes(permission.id)) {
            newPermissions.push(permission.id);
        }
    });
    emit('update:modelValue', newPermissions);
    emit('validate', 'permissions', newPermissions);
};

const computedGroupPermissions = computed(() => props.permissionGroups.filter(group => props.role?.id == 1 ? group.name != 'Role' : true))


const deselectAllInGroup = (groupPermissions: any[]) => {
    const newPermissions = props.modelValue.filter(p =>
        !groupPermissions.some(gp => gp.id === p)
    );
    emit('update:modelValue', newPermissions);
    emit('validate', 'permissions', newPermissions);
};

const selectedPermissions = computed({
    get: () => props.modelValue,
    set: (value: string[]) => {
        emit('update:modelValue', value);
        emit('validate', 'permissions', value);
    }
});


onMounted(() => {
    if (props.preselectedPermissions?.length) {
        const preselectedIds = props.preselectedPermissions.flatMap(permission =>
            permission.name
        );

        selectedPermissions.value = preselectedIds;

        emit('update:modelValue', preselectedIds);
        emit('validate', 'permissions', preselectedIds);
    }
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <!-- Permissions Header -->
        <div
            class="bg-gradient-to-r from-gray-500/10 via-gray-500/5 to-transparent dark:from-gray-900/30 dark:via-gray-900/10 dark:to-transparent px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-gray-500/10 dark:bg-gray-400/10 flex items-center justify-center">
                    <KeyIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Permissions</h2>
            </div>
        </div>

        <!-- Permissions Content -->
        <div class="p-8">
            <div class="space-y-6">
                <div v-for="group in computedGroupPermissions" :key="group.name" class="space-y-5">
                    <!-- Group Header -->
                    <div
                        class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="h-8 w-8 rounded-lg bg-gray-500/10 flex items-center justify-center">
                                <ShieldCheckIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ group.name }}</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ group.permissions.length }} permissions available
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="selectAllInGroup(group.permissions)"
                                class="text-xs font-medium text-gray-600 hover:text-gray-500 dark:text-gray-400">
                                Select All
                            </button>
                            <button @click="deselectAllInGroup(group.permissions)"
                                class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <!-- Permissions Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="permission in group.permissions" :key="permission.id"
                            class="group relative flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-500 dark:hover:border-gray-500 transition-all hover:shadow-sm">
                            <div
                                class="absolute inset-0 rounded-lg bg-gradient-to-r from-gray-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity dark:from-gray-900/20">
                            </div>
                            <div class="relative flex items-center w-full">
                                <input :id="permission.id.toString()" v-model="selectedPermissions"
                                    :value="permission.id.toString()" type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-gray-600 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:checked:bg-gray-500"
                                    :checked="selectedPermissions.includes(permission.id.toString())">
                                <label :for="permission.id.toString()" class="ml-3 flex-1 cursor-pointer">
                                    <span class="block font-medium text-gray-900 dark:text-white">{{ permission.name
                                        }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Allow user to {{ permission.name.toLowerCase() }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>