import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';
import ReportFilters from '@/components/reports/ReportFilters';

export default function Entries({ entries, filters = {} }) {
    const columns = [
        { key: 'entry_code', label: 'Código' },
        { key: 'entry_date', label: 'Fecha' },
        { key: 'supplier_name', label: 'Proveedor' },
        { key: 'total_items', label: 'Ítems' },
        { key: 'user_name', label: 'Usuario' },
    ];

    return (
        <AppLayout title="Reporte de Entradas">
            <div className="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 className="text-2xl font-bold">Reporte de Entradas</h1>
                    <p className="text-sm text-gray-600">Detalle de ingresos de stock por periodo.</p>
                </div>
                <div className="text-sm text-gray-600">
                    <span>Desde: {filters.from ?? 'N/D'}</span>
                    <span className="mx-2">|</span>
                    <span>Hasta: {filters.to ?? 'N/D'}</span>
                </div>
            </div>
            <ReportFilters routeName="reports.entries" filters={filters} />
            <DataTable columns={columns} data={entries?.data ?? []} paginator={entries} searchable />
        </AppLayout>
    );
}
