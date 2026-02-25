import { PageProps as InertiaPageProps } from '@inertiajs/core';

export {}; 

declare global {
    function route(name?: string, params?: any, absolute?: boolean): string;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: (name?: string, params?: any, absolute?: boolean) => string;
    }
}

declare module '@inertiajs/core' {
    export interface PageProps extends InertiaPageProps, Record<string, unknown> {
        auth: {
            user: {
                id: number;
                name: string;
                email: string;
            } | null;
        };
    }
}