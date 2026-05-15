import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';
import ReportFilters from '@/components/reports/ReportFilters';

export default function Stock({ stocks, filters = {} }) {
    const columns = [
        { key: 'product_name', label: 'Producto' },
        { key: 'variant_name', label: 'Variante' },
        { key: 'sku', label: 'SKU' },
        { key: 'current_stock', label: 'Stock Actual' },
        { key: 'min_stock', label: 'Stock Mínimo' },
        { key: 'status', label: 'Estado' },
    ];

    return (
        <AppLayout title="Reporte de Stock">
            <div className="mb-6">
                <h1 className="text-2xl font-bold">Reporte de Stock Actual</h1>
                <p className="text-sm text-gray-600">Resumen de existencias por producto y variante.</p>
            </div>
            <ReportFilters routeName="reports.stock" filters={filters} showDateRange={false} />
            <DataTable columns={columns} data={stocks?.data ?? []} paginator={stocks} searchable />
        </AppLayout>
    );
}
