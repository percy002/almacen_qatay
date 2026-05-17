import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
type ReactPageModule = {
    default: ComponentType;
};

const pages = import.meta.glob<ReactPageModule>('./pages/**/*.{tsx,jsx}');

type PermissionsMap = Record<string, Record<string, boolean>>;

type SharedPageProps = {
    auth?: {
        permissions?: PermissionsMap;
    };
};

declare global {
    var can: (action: string, resource: string) => boolean;
    var route: (name: string, params?: string | number | Record<string, string | number> | null) => string;
}

globalThis.can = globalThis.can ?? (() => false);

const namedRoutes: Record<string, string> = {
    'dashboard': '/tablero',
    'products.index': '/productos',
    'products.create': '/productos/crear',
    'products.store': '/productos',
    'products.show': '/productos/{product}',
    'products.edit': '/productos/{product}/editar',
    'products.update': '/productos/{product}',
    'products.destroy': '/productos/{product}',
    'products.variants.store': '/productos/{product}/variantes',
    'variants.update': '/variantes/{variant}',
    'variants.destroy': '/variantes/{variant}',
    'users.index': '/usuarios',
    'users.create': '/usuarios/crear',
    'users.store': '/usuarios',
    'users.show': '/usuarios/{user}',
    'users.edit': '/usuarios/{user}/editar',
    'users.update': '/usuarios/{user}',
    'users.destroy': '/usuarios/{user}',
    'reports.index': '/reportes',
    'reports.stock': '/reportes/inventario',
    'reports.movements': '/reportes/movimientos',
    'reports.entries': '/reportes/entradas',
    'reports.exits': '/reportes/salidas',
    'reports.adjustments': '/reportes/ajustes',
    'movements.index': '/movimientos',
    'movements.show': '/movimientos/{movement}',
    'entries.index': '/entradas',
    'entries.create': '/entradas/crear',
    'entries.store': '/entradas',
    'entries.show': '/entradas/{entry}',
    'entries.edit': '/entradas/{entry}/editar',
    'entries.update': '/entradas/{entry}',
    'exits.index': '/salidas',
    'exits.create': '/salidas/crear',
    'exits.store': '/salidas',
    'exits.show': '/salidas/{exit}',
    'exits.edit': '/salidas/{exit}/editar',
    'exits.update': '/salidas/{exit}',
    'exits.destroy': '/salidas/{exit}',
    'adjustments.index': '/ajustes',
    'adjustments.create': '/ajustes/crear',
    'adjustments.store': '/ajustes',
    'adjustments.show': '/ajustes/{adjustment}',
    'adjustments.edit': '/ajustes/{adjustment}/editar',
    'adjustments.update': '/ajustes/{adjustment}',
};

function fillRouteTemplate(
    template: string,
    params?: string | number | Record<string, string | number> | null,
): string {
    if (params === null || params === undefined) {
        return template;
    }

    if (typeof params === 'string' || typeof params === 'number') {
        return template.replace(/\{[^}]+\}/, encodeURIComponent(String(params)));
    }

    return template.replace(/\{([^}]+)\}/g, (_, key: string) => {
        const value = params[key];

        return value === undefined ? `{${key}}` : encodeURIComponent(String(value));
    });
}

globalThis.route = globalThis.route ?? ((name, params) => {
    const template = namedRoutes[name];

    if (!template) {
        throw new Error(`Unknown named route: ${name}`);
    }

    return fillRouteTemplate(template, params);
});

function setPermissionsFromPageProps(props?: SharedPageProps) {
    const auth = props?.auth;

    globalThis.can = (action: string, resource: string) => Boolean(auth?.permissions?.[resource]?.[action]);
}

function bootstrapPermissions() {
    const serializedPage = document.getElementById('app')?.dataset.page;

    if (serializedPage) {
        try {
            const page = JSON.parse(serializedPage) as { props?: SharedPageProps };
            setPermissionsFromPageProps(page.props);
        } catch {
            globalThis.can = () => false;
        }
    }

    document.addEventListener('inertia:navigate', (event: Event) => {
        const navigateEvent = event as CustomEvent<{ page?: { props?: SharedPageProps } }>;
        setPermissionsFromPageProps(navigateEvent.detail?.page?.props);
    });
}

bootstrapPermissions();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`] ?? pages[`./pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        const module = await page();

        return module.default;
    },
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
