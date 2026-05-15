import { Head } from '@inertiajs/react';
import { ArrowDownCircle, ArrowUpCircle, BarChart3, Boxes, Move3D, Package, Users } from 'lucide-react';
import { dashboard } from '@/routes';

export default function Dashboard() {
    const cards = [
        { title: 'Productos', description: 'Catálogo, variantes y stock mínimo', href: '/products', icon: Package },
        { title: 'Entradas', description: 'Registro de ingresos de inventario', href: '/entries', icon: ArrowDownCircle },
        { title: 'Salidas', description: 'Despachos y egresos de almacén', href: '/exits', icon: ArrowUpCircle },
        { title: 'Ajustes', description: 'Correcciones de stock con trazabilidad', href: '/adjustments', icon: Boxes },
        { title: 'Movimientos', description: 'Historial consolidado por variante', href: '/movements', icon: Move3D },
        { title: 'Reportes', description: 'Vistas filtrables por fecha y búsqueda', href: '/reports', icon: BarChart3 },
        { title: 'Usuarios', description: 'Administración de acceso y roles', href: '/users', icon: Users },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">Panel de Almacén</h1>
                    <p className="text-sm text-muted-foreground">Accede a los módulos principales del sistema.</p>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {cards.map(card => (
                        <a
                            key={card.title}
                            href={card.href}
                            className="group rounded-xl border border-sidebar-border/70 bg-background p-4 transition hover:border-primary/50 hover:shadow-sm"
                        >
                            <div className="mb-3 inline-flex rounded-lg border p-2">
                                <card.icon className="size-5" />
                            </div>
                            <h2 className="font-semibold group-hover:text-primary">{card.title}</h2>
                            <p className="mt-1 text-sm text-muted-foreground">{card.description}</p>
                        </a>
                    ))}
                </div>
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
