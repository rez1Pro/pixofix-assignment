<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const appName = computed(() => {
    return window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
});
</script>

<template>
    <div class="min-h-screen bg-[#FCFCFC] dark:bg-gray-900">
        <!-- Decorative Background -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] [mask-image:radial-gradient(ellipse_50%_50%_at_50%_50%,#000_70%,transparent_100%)] dark:bg-[radial-gradient(#1f2937_1px,transparent_1px)]"></div>
        </div>

        <div class="flex min-h-screen">
            <!-- Left Section -->
            <div class="hidden lg:block lg:w-[480px] xl:w-[580px] relative bg-emerald-600 dark:bg-emerald-700">
                <div class="absolute inset-0 bg-[url('/img/auth-bg-pattern.svg')] opacity-10"></div>
                <div class="relative h-full flex flex-col p-12 xl:p-16">
                    <Link href="/" class="flex items-center gap-2">
                        <ApplicationLogo class="h-10 w-10 text-white" />
                        <span class="text-xl font-bold text-white">{{ appName }}</span>
                    </Link>

                    <div class="mt-24 pr-8">
                        <h2 class="text-3xl xl:text-4xl font-bold text-white leading-tight">
                            Transform your workflow with our powerful tools
                        </h2>
                        <p class="mt-4 text-lg text-emerald-100">
                            Join thousands of users who trust our platform for their business needs.
                        </p>

                        <!-- Testimonial -->
                        <blockquote class="mt-16">
                            <p class="text-lg font-medium text-emerald-100">
                                "This platform has completely transformed how we manage our business operations. The efficiency gains are remarkable."
                            </p>
                            <footer class="mt-4">
                                <div class="flex items-center">
                                    <img
                                        src="https://ui-avatars.com/api/?name=Sarah+Wilson&background=10B981&color=fff"
                                        alt="Sarah Wilson"
                                        class="h-10 w-10 rounded-full border-2 border-emerald-400"
                                    >
                                    <div class="ml-3">
                                        <p class="text-base font-semibold text-white">Sarah Wilson</p>
                                        <p class="text-sm text-emerald-200">CEO at TechCorp</p>
                                    </div>
                                </div>
                            </footer>
                        </blockquote>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex-1 flex flex-col">
                <div class="flex-1 flex flex-col items-center justify-center px-6 lg:px-8 xl:px-12">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-8">
                        <Link href="/" class="flex items-center gap-2">
                            <ApplicationLogo class="h-10 w-10 text-emerald-600" />
                            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ appName }}</span>
                        </Link>
                    </div>

                    <!-- Main Content Card -->
                    <div class="w-full max-w-[420px]">
                        <div class="rounded-2xl bg-white p-8 shadow-[0_2px_8px_rgba(0,0,0,0.08)] dark:bg-gray-800/90 dark:shadow-2xl dark:shadow-gray-900/50">
                            <slot />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <p>© {{ new Date().getFullYear() }} {{ appName }}. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
