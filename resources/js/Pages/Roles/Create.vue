<script setup lang="ts">
import RoleForm from '@/Components/App/RoleComponents/RoleForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    permissionGroups: {
        type: Array as PropType<{ name: string; permissions: any[] }[]>,
        required: true
    }
});

// Define breadcrumbs for navigation
const breadcrumbs = [
    { name: 'User Management', href: route('users.roles.index') },
    { name: 'Roles', href: route('users.roles.index') },
    { name: 'Create Role' }
];
</script>

<template>
    <AuthenticatedLayout :header="`Create Role`" :description="`Create a new role to manage user permissions.`"
        :breadcrumbs="breadcrumbs">

        <Head title="Create Role" />

        <RoleForm :permission-groups="permissionGroups" mode="create" :submit-url="route('users.roles.store')"
            :back-url="route('users.roles.index')" />
    </AuthenticatedLayout>
</template>