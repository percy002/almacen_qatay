import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';
import ReportFilters from '@/components/reports/ReportFilters';

export default function Adjustments({ adjustments, filters = {} }) {
    const columns = [
        { key: 'adjustment_code', label: 'Código' },
        { key: 'adjustment_date', label: 'Fecha' },
        { key: 'variant_name', label: 'Variante' },
        { key: 'adjustment_type', label: 'Tipo' },
        { key: 'quantity', label: 'Cantidad' },
        { key: 'user_name', label: 'Usuario' },
    ];

    return (
        <AppLayout title="Reporte de Ajustes">
            <div className="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 className="text-2xl font-bold">Reporte de Ajustes</h1>
                    <p className="text-sm text-gray-600">Historial de correcciones manuales de inventario.</p>
                </div>
                <div className="text-sm text-gray-600">
                    <span>Desde: {filters.from ?? 'N/D'}</span>
                    <span className="mx-2">|</span>
                    <span>Hasta: {filters.to ?? 'N/D'}</span>
                </div>
            </div>
            <ReportFilters routeName="reports.adjustments" filters={filters} />
            <DataTable columns={columns} data={adjustments?.data ?? []} paginator={adjustments} searchable />
        </AppLayout>
    );
}
