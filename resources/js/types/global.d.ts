import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { AxiosInstance } from 'axios';
import { Component } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';
import { PageProps as AppPageProps } from './';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    /* eslint-disable no-var */
    var route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps { }
}

interface Navigation {
    name: string;
    href: string;
    icon: Component;
    current: boolean;
    permissions: (keyof typeof App.Enums.Permissions[keyof typeof App.Enums.Permissions])[];
    submenu?: SubNavigation[];
}


interface SubNavigation {
    name: string;
    href: string;
    icon: Component;
    current: boolean;
    permission: string;
}