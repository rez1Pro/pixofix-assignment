<script setup lang="ts">
import RoleForm from '@/Components/App/RoleComponents/RoleForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    role: {
        type: Object as PropType<App.Data.RoleData>,
        required: true
    },
    existingPermissions: {
        type: Array as PropType<{ name: string; permissions: any[] }[]>,
        required: true
    }
});

// Define breadcrumbs for navigation
const breadcrumbs = [
    { name: 'User Management', href: route('users.roles.index') },
    { name: 'Roles', href: route('users.roles.index') },
    { name: 'Edit Role' }
];
</script>

<template>
    <AuthenticatedLayout :header="`Edit Role: ${role.name}`" :description="`Update role details and permissions.`"
        :breadcrumbs="breadcrumbs">

        <Head title="Edit Role" />

        <RoleForm :role="role" :permission-groups="existingPermissions" mode="edit"
            :submit-url="route('users.roles.update', role.id)" :back-url="route('users.roles.index')" />
    </AuthenticatedLayout>
</template>