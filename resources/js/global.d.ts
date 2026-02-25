export {}; 

declare global {
    function route(): { current: (name?: string) => boolean };
    function route(name: string, params?: any, absolute?: boolean): string;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: {
            (): { current: (name?: string) => boolean };
            (name: string, params?: any, absolute?: boolean): string;
        };
    }
}