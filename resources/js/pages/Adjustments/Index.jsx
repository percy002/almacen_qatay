import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';

export default function Index({ adjustments }) {
    const columns = [
        { key: 'adjustment_code', label: 'Código' },
        { key: 'adjustment_date', label: 'Fecha' },
        { key: 'variant_name', label: 'Variante' },
        { key: 'adjustment_type', label: 'Tipo' },
        { key: 'quantity', label: 'Cantidad' },
        { key: 'user_name', label: 'Usuario' },
        { key: 'actions', label: 'Acciones' },
    ];

    const data = adjustments.data.map(adj => ({
        ...adj,
        actions: (
            <div className="flex items-center gap-3">
                <a
                    href={route('adjustments.show', adj.id)}
                    className="text-blue-600 hover:underline"
                >
                    Ver
                </a>
                {can('update', 'StockAdjustment') && (
                    <a
                        href={route('adjustments.edit', adj.id)}
                        className="text-amber-700 hover:underline"
                    >
                        Editar
                    </a>
                )}
            </div>
        ),
    }));

    return (
        <AppLayout title="Ajustes de Stock">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Ajustes de Stock</h1>
                {can('create', 'StockAdjustment') && (
                    <a
                        href={route('adjustments.create')}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Nuevo Ajuste
                    </a>
                )}
            </div>
            <DataTable columns={columns} data={data} paginator={adjustments} searchable />
        </AppLayout>
    );
}
