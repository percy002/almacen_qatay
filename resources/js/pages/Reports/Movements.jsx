import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';
import ReportFilters from '@/components/reports/ReportFilters';

export default function Movements({ movements, filters = {} }) {
    const columns = [
        { key: 'date', label: 'Fecha' },
        { key: 'type', label: 'Tipo' },
        { key: 'product_name', label: 'Producto' },
        { key: 'variant_name', label: 'Variante' },
        { key: 'quantity', label: 'Cantidad' },
        { key: 'user_name', label: 'Usuario' },
        { key: 'reference', label: 'Referencia' },
    ];

    return (
        <AppLayout title="Reporte de Movimientos">
            <div className="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 className="text-2xl font-bold">Reporte de Movimientos</h1>
                    <p className="text-sm text-gray-600">Historial consolidado de entradas, salidas y ajustes.</p>
                </div>
                <div className="text-sm text-gray-600">
                    <span>Desde: {filters.from ?? 'N/D'}</span>
                    <span className="mx-2">|</span>
                    <span>Hasta: {filters.to ?? 'N/D'}</span>
                </div>
            </div>
            <ReportFilters routeName="reports.movements" filters={filters} />
            <DataTable columns={columns} data={movements?.data ?? []} paginator={movements} searchable />
        </AppLayout>
    );
}
