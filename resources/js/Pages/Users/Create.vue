<script setup lang="ts">
import UserForm from '@/Components/App/UserComponents/UserForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    roles: {
        type: Array as PropType<App.Data.RoleData[]>,
        required: true
    }
});

// Define breadcrumbs for navigation
const breadcrumbs = [
    { name: 'User Management', href: route('users.index') },
    { name: 'Create User' }
];
</script>

<template>
    <AuthenticatedLayout header="Create User"
        description="Create a new user account with specific role and permissions." :breadcrumbs="breadcrumbs">

        <Head title="Create User" />

        <UserForm :roles="roles" mode="create" :submit-url="route('users.store')" :back-url="route('users.index')" />
    </AuthenticatedLayout>
</template>