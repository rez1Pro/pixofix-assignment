<script setup lang="ts">
import UserForm from '@/Components/App/UserComponents/UserForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    user: {
        type: Object as PropType<App.Data.UserData>,
        required: true
    },
    roles: {
        type: Array as PropType<App.Data.RoleData[]>,
        required: true
    }
});

// Define breadcrumbs for navigation
const breadcrumbs = [
    { name: 'User Management', href: route('users.index') },
    { name: 'Edit User' }
];
</script>

<template>
    <AuthenticatedLayout :header="`Edit User: ${user.name}`" description="Update user details and role."
        :breadcrumbs="breadcrumbs">

        <Head title="Edit User" />

        <UserForm :user="user" :roles="roles" mode="edit" :submit-url="route('users.update', user.id)"
            :back-url="route('users.index')" />
    </AuthenticatedLayout>
</template>