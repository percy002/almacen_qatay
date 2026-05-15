import { Head } from '@inertiajs/react';
import { AlertTriangle, DollarSign, Network, Plus } from 'lucide-react';
import { dashboard } from '@/routes';

type StockSnapshotItem = {
    id: number;
    product_name: string;
    variant_name: string;
    sku: string;
    current_stock: number;
    min_stock: number;
    status: string;
    image_url: string | null;
};

type DashboardMetrics = {
    alertCount: number;
    entriesCount: number;
    exitsCount: number;
    movementsCount: number;
};

export default function Dashboard({ stockSnapshot = [], dashboardMetrics = {} as DashboardMetrics }) {
    const highlights = [
        {
            title: 'Alertas',
            hint: 'Productos bajo mínimo',
            icon: AlertTriangle,
            value: dashboardMetrics.alertCount ?? 0,
        },
        {
            title: 'Entradas',
            hint: 'Recepciones hoy',
            icon: Plus,
            value: dashboardMetrics.entriesCount ?? 0,
        },
        {
            title: 'Salidas',
            hint: 'Salidas hoy',
            icon: DollarSign,
            value: dashboardMetrics.exitsCount ?? 0,
        },
        {
            title: 'Movimientos',
            hint: 'Entradas + Salidas hoy',
            icon: Network,
            value: dashboardMetrics.movementsCount ?? 0,
        },
    ];

    const statusConfig = (status: string, current_stock: number, min_stock: number) => {
        if (status === 'Bajo mínimo') {
            if (current_stock === 0) {
                return { dot: 'bg-red-500', badge: 'bg-red-600 text-white', label: 'SIN STOCK' };
            }
            return { dot: 'bg-orange-400', badge: 'bg-orange-500 text-white', label: 'BAJO STOCK' };
        }
        return { dot: 'bg-emerald-500', badge: 'bg-emerald-600 text-white', label: 'EN STOCK' };
    };

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 px-6 pb-8 md:px-8">
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {highlights.map(card => (
                        <section
                            key={card.title}
                            className="flex min-h-26 flex-col justify-between rounded-lg border border-border/70 bg-card px-4 py-3"
                        >
                            <div className="flex justify-between items-center mb-2">
                                <span className="text-2xl font-bold text-foreground">{card.value}</span>
                                <card.icon className="size-5 text-foreground/70" />
                            </div>
                            <div>
                                <h2 className="text-sm font-semibold">{card.title}</h2>
                                <p className="mt-1 text-xs text-muted-foreground">{card.hint}</p>
                            </div>
                        </section>
                    ))}
                </div>

                <section className="overflow-hidden rounded-sm border border-border/70 bg-card">
                    <div className="flex flex-wrap items-center gap-3 border-b border-border/70 bg-[#a9b8af] p-5">
                        <div>
                            <h2 className="text-base font-semibold text-foreground">Stock actual</h2>
                            <p className="text-xs text-foreground/80">Resumen rápido de variantes con menor stock.</p>
                        </div>
                        <a
                            href={route('reports.stock')}
                            className="ml-auto rounded-md bg-card px-3 py-2 text-xs font-medium text-foreground hover:bg-accent"
                        >
                            Ver reporte completo
                        </a>
                    </div>

                    <div className="bg-[#f0f0ef] p-4">
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            {(stockSnapshot as StockSnapshotItem[]).map(item => {
                                const cfg = statusConfig(item.status, item.current_stock, item.min_stock);
                                return (
                                    <div
                                        key={item.id}
                                        className="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-black/5"
                                    >
                                        {/* Image area — portrait 3:4 */}
                                        <div className="relative aspect-[3/4] w-full shrink-0 bg-muted">
                                            {item.image_url ? (
                                                <img
                                                    src={item.image_url}
                                                    alt={item.variant_name}
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center">
                                                    <svg
                                                        className="size-10 text-muted-foreground/30"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={1}
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                        />
                                                    </svg>
                                                </div>
                                            )}

                                            {/* Status dot — top right */}
                                            <span className={`absolute right-2 top-2 size-3 rounded-full ring-2 ring-white ${cfg.dot}`} />

                                            {/* Status badge — bottom left */}
                                            <span
                                                className={`absolute bottom-2 left-2 rounded px-1.5 py-0.5 text-[10px] font-bold tracking-wide ${cfg.badge}`}
                                            >
                                                {cfg.label}
                                            </span>
                                        </div>

                                        {/* Card content */}
                                        <div className="flex flex-1 flex-col gap-1 p-2.5">
                                            <p className="truncate text-xs font-semibold text-foreground leading-tight">
                                                {item.product_name}
                                            </p>
                                            <p className="truncate text-[11px] text-muted-foreground">
                                                {item.variant_name}
                                            </p>
                                            <div className="mt-1.5 border-t border-border/60 pt-1.5">
                                                <p className="text-[9px] uppercase tracking-widest text-muted-foreground/70">
                                                    Stock total
                                                </p>
                                                <p className="text-sm font-bold text-foreground">
                                                    {item.current_stock}
                                                    <span className="ml-1 text-[10px] font-normal text-muted-foreground">
                                                        / mín {item.min_stock}
                                                    </span>
                                                </p>
                                            </div>
                                            <p className="mt-0.5 truncate text-[9px] text-muted-foreground/60 font-mono">
                                                {item.sku}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
