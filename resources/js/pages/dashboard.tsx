import { Head } from '@inertiajs/react';
import { AlertTriangle, DollarSign, Network, PencilLine, Plus } from 'lucide-react';
import DataTable from '@/components/ui/DataTable';
import { dashboard } from '@/routes';

type StockSnapshotItem = {
    id: number;
    product_name: string;
    variant_name: string;
    sku: string;
    current_stock: number;
    min_stock: number;
    status: string;
};

export default function Dashboard({ stockSnapshot = [], dashboardMetrics = {} }) {
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

    const stockColumns = [
        { key: 'product_name', label: 'Producto' },
        { key: 'variant_name', label: 'Variante' },
        { key: 'sku', label: 'SKU' },
        { key: 'current_stock', label: 'Stock Actual' },
        { key: 'min_stock', label: 'Stock Mínimo' },
        { key: 'status', label: 'Estado' },
    ];

    const stockData = stockSnapshot.map(item => {
        const isLow = item.status === 'Bajo mínimo';

        return {
            ...item,
            status: (
                <span
                    className={`inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium ${isLow ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'}`}
                >
                    <span
                        className={`inline-block size-2 rounded-full ${isLow ? 'bg-red-500' : 'bg-emerald-500'}`}
                    />
                    {item.status}
                </span>
            ),
        };
    });

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto px-6 pb-8 md:px-8">
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
                        <DataTable columns={stockColumns} data={stockData} />
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
