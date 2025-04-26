<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import { Navigation } from '@/types/global';
// @ts-ignore
import { hasPermission } from '@/utils/permissions.ts';
import {
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    BellIcon,
    ChevronDownIcon,
    ExclamationCircleIcon,
    HomeIcon,
    MagnifyingGlassIcon, ShieldCheckIcon,
    UserGroupIcon,
    UserIcon,
    UsersIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, PropType, reactive, ref } from 'vue';
import { route } from 'ziggy-js';

// sidebar navigation
const navigation = reactive<Navigation[]>([
    {
        name: 'Dashboard',
        href: route('dashboard'),
        icon: HomeIcon,
        current: route().current('dashboard'),
        permissions: ['any']
    },
    {
        name: 'Order & File Management',
        href: route('order-management'),
        icon: ExclamationCircleIcon,
        current: route().current('order-management') || route().current('orders.*') || route().current('files.*') || route().current('claims.*'),
        permissions: ['view:orders', 'view:files', 'view:claims']
    },
    {
        name: 'Users',
        icon: UsersIcon,
        current: route().current('users.*'),
        permissions: ['user:view', 'role:view'],
        href: "#",
        submenu: [
            {
                name: 'All Users',
                href: route('users.index'),
                icon: UserGroupIcon,
                current: route().current('users.index') || route().current('users.create') || route().current('users.edit') || route().current('users.show'),
                permission: 'user:view'
            },
            {
                name: 'Roles & Permissions',
                href: route('users.roles.index'),
                icon: ShieldCheckIcon,
                current: route().current('users.roles.*'),
                permission: 'role:view'
            }
        ]
    }
]);


const props = defineProps({
    header: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    breadcrumbs: {
        type: Array as PropType<Array<{ name: string; href?: string }>>,
        default: () => []
    }
})

const showingSidebar = ref(window.innerWidth >= 1024);
const showingUserMenu = ref(false);

const page = usePage();
const user = page.props.auth.user;

// Track expanded menu sections
const expandedMenus = ref<string[]>([]);


const toggleMenu = (menuName: string) => {
    const index = expandedMenus.value.indexOf(menuName);
    if (index === -1) {
        expandedMenus.value.push(menuName);
    } else {
        expandedMenus.value.splice(index, 1);
    }
};

const isMenuExpanded = (menuName: string) => expandedMenus.value.includes(menuName);

const toggleSidebar = () => {
    showingSidebar.value = !showingSidebar.value;
};

const closeSidebar = () => {
    if (window.innerWidth < 1024) {
        showingSidebar.value = false;
    }
};

// Add this computed property for active menu highlighting

// Add this for menu hover effect
const hoveredMenu = ref<string | null>(null);

// Add these methods
const setHoveredMenu = (menuName: string | null) => {
    hoveredMenu.value = menuName;
};

const isActiveMenu = (item: any) => {
    if (item.submenu) {
        return item.submenu.some((subitem: any) => subitem.current);
    }
    return item.current;
};

// Add these new refs and functions
const showingNotifications = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

// Close dropdowns when clicking outside
const closeDropdowns = (event: MouseEvent) => {
    const target = event.target as HTMLElement;

    if (!target.closest('.user-menu-button')) {
        showingUserMenu.value = false;
    }

    if (!target.closest('.notifications-button')) {
        showingNotifications.value = false;
    }
};

// Handle search
const handleSearch = () => {
    if (searchQuery.value.length > 2) {
        isSearching.value = true;
        // Implement your search logic here
        // For example:
        // searchResults.value = await performSearch(searchQuery.value);
        isSearching.value = false;
    }
};

// Handle user menu actions
const handleProfileClick = () => {
    showingUserMenu.value = false;
};

const handleLogout = () => {
    router.post(route('logout'));
};



// Add this computed property
const expandedByDefault = computed(() => {
    return navigation.map(item => {
        if (item.submenu && item.submenu.some(subitem => subitem.current)) {
            return item.name;
        }
        return null;
    }).filter(Boolean);
});


// Add event listeners
onMounted(() => {
    document.addEventListener('click', closeDropdowns);
    expandedMenus.value = expandedByDefault.value as string[];
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

// Update the menuItemClasses computed property for more modern styling
const menuItemClasses = computed(() => ({
    base: `group relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-all duration-300`,
    active: `bg-gray-800/90 dark:bg-gray-800/90 
             text-white dark:text-white
             before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 
             before:h-8 before:w-1 before:rounded-r-lg 
             before:bg-gray-300 dark:before:bg-gray-500`,
    inactive: `text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 
               hover:text-gray-900 dark:hover:text-white`,
    activeSubmenu: `text-gray-900 font-semibold tracking-wide bg-gray-200/90 dark:bg-gray-800/90
              hover:bg-gray-200/90 dark:hover:bg-gray-700/90 transition-all duration-300 ease-in-out
               border-gray-400 dark:border-gray-500 pl-2.5
              dark:text-gray-200 hover:text-gray-900 dark:hover:text-white
               dark:shadow-gray-800/20  before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 
             before:h-8 before:w-1 before:rounded-r-lg 
             before:bg-gray-400 dark:before:bg-gray-500`,
    icon: {
        wrapper: `flex h-6 w-8 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-110`,
        active: `text-white dark:text-blue-400`,
        activeSubmenu: `hover:bg-gray-500/40 dark:hover:bg-gray-800 dark:hover:text-gray-900`,
        inactive: `text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-white`
    }
}));

</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <FlashMessage />

        <!-- Sidebar Backdrop - improved mobile overlay -->
        <div v-if="showingSidebar"
            class="fixed inset-0 z-[49] bg-gray-900/70 backdrop-blur-sm transition-opacity lg:hidden"
            @click="closeSidebar">
        </div>

        <!-- Sidebar - improved mobile positioning -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-50 w-64 transform overflow-y-auto overscroll-contain',
            'transition-all duration-300 ease-in-out lg:translate-x-0 lg:w-72',
            showingSidebar ? 'translate-x-0' : '-translate-x-full'
        ]">
            <div
                class="flex h-full flex-col bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-r border-gray-200/30 dark:border-gray-700/30">
                <!-- Logo Section - further reduced height -->
                <div class="flex h-14 items-center px-4 border-b border-gray-200/30 dark:border-gray-700/20">
                    <Link href="/" class="flex items-center gap-2 transition-opacity hover:opacity-80">
                    <ApplicationLogo class="h-7 w-7 text-gray-700 dark:text-gray-300" />
                    <span
                        class="text-sm font-semibold bg-gradient-to-r from-gray-700 to-white bg-clip-text text-transparent">
                        {{ page.props.app_name }}
                    </span>
                    </Link>
                </div>

                <!-- Navigation - reduced padding -->
                <nav class="flex-1 space-y-1 px-2 py-2">
                    <!-- Section spacing -->
                    <div v-for="(item, index) in navigation" :key="item.name" class="space-y-1">
                        <Link
                            v-if="!item.submenu && (!item.permissions || item.permissions.some(p => hasPermission(p as string)))"
                            :href="item.href" :class="[
                                menuItemClasses.base,
                                'px-3 py-2', // Adjusted padding
                                item.current ? menuItemClasses.active : menuItemClasses.inactive
                            ]">
                        <div :class="[
                            menuItemClasses.icon.wrapper,
                            item.current ? menuItemClasses.icon.active : menuItemClasses.icon.inactive
                        ]">
                            <component :is="item.icon" class="h-5 w-5" />
                        </div>
                        <span>{{ item.name }}</span>
                        </Link>

                        <!-- Submenu items with reduced spacing -->
                        <template v-if="item.submenu">
                            <button @click="toggleMenu(item.name)" class="w-full" :class="[
                                menuItemClasses.base,
                                'px-3 py-2 my-1',
                                isActiveMenu(item) ? menuItemClasses.active : menuItemClasses.inactive
                            ]">
                                <div :class="[
                                    menuItemClasses.icon.wrapper,
                                    isActiveMenu(item) ? menuItemClasses.icon.active : menuItemClasses.icon.inactive
                                ]">
                                    <component :is="item.icon" class="h-5 w-5" />
                                </div>
                                <span>{{ item.name }}</span>
                                <ChevronDownIcon class="ml-auto h-5 w-5 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': isMenuExpanded(item.name) }" />
                            </button>

                            <!-- Submenu items -->
                            <div v-show="isMenuExpanded(item.name)" class="mt-1 space-y-1 pl-4">
                                <div v-for="subitem in item.submenu" :key="subitem.name"
                                    v-show="!subitem.permission || hasPermission(subitem.permission as string)">
                                    <Link :href="subitem.href" :class="[
                                        menuItemClasses.base,
                                        'px-3 py-2',
                                        subitem.current ? menuItemClasses.activeSubmenu : menuItemClasses.inactive
                                    ]">
                                    <div :class="[
                                        menuItemClasses.icon.wrapper,
                                        subitem.current ? menuItemClasses.icon.activeSubmenu : menuItemClasses.icon.inactive
                                    ]">
                                        <component :is="subitem.icon" class="h-5 w-5" />
                                    </div>
                                    <span>{{ subitem.name }}</span>
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content - improved responsive layout -->
        <div :class="[
            'min-h-screen transition-all duration-300',
            showingSidebar ? 'lg:pl-72' : 'lg:pl-0'
        ]">
            <!-- Header - improved responsive design -->
            <header
                class="sticky top-0 z-40 bg-white/70 dark:bg-gray-900/70 backdrop-blur-2xl border-b border-gray-200/50 dark:border-gray-700/30">
                <div class="flex h-16 items-center justify-between gap-2 px-3 sm:gap-4 sm:px-6 lg:px-8">
                    <!-- Left Side -->
                    <div class="flex items-center gap-2 sm:gap-4">
                        <button @click="toggleSidebar"
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 lg:hidden">
                            <Bars3Icon v-if="!showingSidebar" class="h-5 w-5" />
                            <XMarkIcon v-else class="h-5 w-5" />
                        </button>

                        <!-- Breadcrumb - improved responsive visibility -->
                        <nav class="hidden sm:flex items-center gap-2">
                            <Link href="/dashboard"
                                class="text-sm font-medium text-gray-900 dark:text-white hover:text-gray-900 dark:hover:text-gray-100">
                            Dashboard
                            </Link>
                            <span v-if="route().current() !== 'dashboard'"
                                class="text-gray-400 dark:text-gray-600">/</span>
                            <span v-if="route().current() !== 'dashboard'"
                                class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{
                                    route().current()
                                        // @ts-ignore
                                        ? route().current().split('.')[0].charAt(0).toUpperCase() +
                                        // @ts-ignore
                                        route().current().split('.')[0].slice(1)
                                        : '' }}
                            </span>
                        </nav>
                    </div>

                    <!-- Right Side - improved spacing and mobile layout -->
                    <div class="flex items-center gap-1.5 sm:gap-3">
                        <!-- Search - responsive visibility -->
                        <div class="relative hidden md:block">
                            <div class="relative w-48 lg:w-64">
                                <input v-model="searchQuery" type="text" placeholder="Search..." @input="handleSearch"
                                    class="w-full pl-9 pr-4 py-2.5 text-sm bg-gray-50/50 dark:bg-gray-800/50 
                                           border border-gray-200/50 dark:border-gray-700/30 rounded-xl 
                                           focus:outline-none focus:ring-2 focus:ring-blue-500/20 
                                           dark:text-gray-300 dark:placeholder-gray-500 transition-all">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-5 w-5"
                                        :class="isSearching ? 'text-gray-500' : 'text-gray-400 dark:text-gray-500'" />
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons - improved mobile spacing -->
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <ThemeSwitcher />

                            <!-- Notifications -->
                            <div class="relative">
                                <button type="button" @click="showingNotifications = !showingNotifications" class="notifications-button relative inline-flex h-9 w-9 sm:h-10 sm:w-10 
                                           items-center justify-center rounded-xl bg-gray-50/50">
                                    <BellIcon
                                        class="h-5 w-5 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors" />
                                    <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-gray-500"></span>
                                </button>
                            </div>

                            <!-- User Menu - improved mobile design -->
                            <div class="relative">
                                <button @click="showingUserMenu = !showingUserMenu" class="user-menu-button flex items-center gap-2 px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl 
                                           bg-gray-50/50 dark:bg-gray-800/50">
                                    <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=000000&color=fff`"
                                        :alt="user.name" class="h-7 w-7 rounded-full">
                                    <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ user.name }}
                                    </span>
                                    <ChevronDownIcon class="h-5 w-5 text-gray-400 hidden sm:block"
                                        :class="{ 'rotate-180': showingUserMenu }" />
                                </button>

                                <!-- Profile dropdown -->
                                <div v-show="showingUserMenu"
                                    class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <Link :href="route('profile.edit')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        @click="handleProfileClick">
                                    <UserIcon class="h-5 w-5" />
                                    Profile
                                    </Link>
                                    <button @click="handleLogout"
                                        class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <ArrowRightOnRectangleIcon class="h-5 w-5" />
                                        Sign out
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main content area - improved responsive padding -->
            <main class="py-6 px-4 sm:px-6 lg:px-8 mx-auto">
                <slot name="header">
                    <div class="mb-6">
                        <!-- Header Content -->
                        <div class="flex flex-col gap-2">
                            <h1
                                class="text-2xl font-bold bg-gradient-to-r from-gray-900 via-gray-700 to-gray-500 dark:from-white dark:via-gray-300 dark:to-gray-500 bg-clip-text text-transparent tracking-tight">
                                {{ header }}
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-3xl">
                                {{ description }}
                            </p>
                        </div>
                    </div>
                </slot>
                <div>
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
