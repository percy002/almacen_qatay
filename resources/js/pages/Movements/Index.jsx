import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';

export default function Index({ movements }) {
    const columns = [
        { key: 'date', label: 'Fecha' },
        { key: 'type', label: 'Tipo' },
        { key: 'product', label: 'Producto' },
        { key: 'variant', label: 'Variante' },
        { key: 'quantity', label: 'Cantidad' },
        { key: 'user', label: 'Usuario' },
        { key: 'actions', label: 'Acciones' },
    ];

    const data = movements.data.map(mov => ({
        ...mov,
        actions: (
            <a
                href={route('movements.show', mov.id)}
                className="text-blue-600 hover:underline"
            >
                Ver
            </a>
        ),
    }));

    return (
        <AppLayout title="Movimientos de Stock">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Movimientos de Stock</h1>
            </div>
            <DataTable columns={columns} data={data} paginator={movements} searchable />
        </AppLayout>
    );
}
