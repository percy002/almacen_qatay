import { Link } from '@inertiajs/react';
import { ArrowDownCircle, ArrowUpCircle, BarChart3, Boxes, LayoutGrid, Move3D, Package, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Productos',
        href: '/products',
        icon: Package,
    },
    {
        title: 'Recepción',
        href: '/entries',
        icon: ArrowDownCircle,
    },
    {
        title: 'Despacho',
        href: '/exits',
        icon: ArrowUpCircle,
    },
    {
        title: 'Ajustes',
        href: '/adjustments',
        icon: Boxes,
    },
    {
        title: 'Movimientos',
        href: '/movements',
        icon: Move3D,
    },
    {
        title: 'Reportes',
        href: '/reports',
        icon: BarChart3,
    },
    {
        title: 'Usuarios',
        href: '/users',
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset" className="md:pt-4 md:pl-4">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                {footerNavItems.length > 0 ? (
                    <NavFooter items={footerNavItems} className="mt-auto" />
                ) : null}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
