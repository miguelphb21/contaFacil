import type { Auth } from '@/types/auth';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}

type ZiggyRouteParam = string | number | boolean | null | undefined;

type ZiggyRouteParams =
    | ZiggyRouteParam
    | Array<ZiggyRouteParam>
    | Record<string, ZiggyRouteParam>;

type ZiggyRoute = (
    name?: string,
    params?: ZiggyRouteParams,
    absolute?: boolean,
    config?: unknown,
) => string;

declare global {
    // eslint-disable-next-line no-var
    var route: ZiggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
        route: ZiggyRoute;
    }
}
