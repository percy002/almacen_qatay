import { Bell, CircleUserRound, Search } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs: _breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex h-24 shrink-0 items-center gap-3 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-20 md:px-8">
            <div className="flex items-center">
                <SidebarTrigger className="h-9 w-9 rounded-full border border-border/50 bg-card/40" />
            </div>

            <div className="flex flex-1 justify-center">
                <label className="relative w-full max-w-xl">
                    <Search className="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-foreground/70" />
                    <input
                        type="search"
                        placeholder="Buscar"
                        className="h-12 w-full rounded-full border border-border/80 bg-accent/70 px-5 pr-10 text-sm outline-none placeholder:text-foreground/60 focus-visible:ring-2 focus-visible:ring-ring"
                    />
                </label>
            </div>

            <div className="flex items-center gap-2">
                <Button variant="ghost" size="icon" className="h-10 w-10 rounded-full text-foreground/80">
                    <Bell className="size-5" />
                </Button>
                <Button variant="ghost" size="icon" className="h-10 w-10 rounded-full text-foreground/80">
                    <CircleUserRound className="size-6" />
                </Button>
            </div>
        </header>
    );
}
