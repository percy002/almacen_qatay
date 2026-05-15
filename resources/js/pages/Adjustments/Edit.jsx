import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import AdjustmentForm from '@/components/forms/AdjustmentForm';

export default function Edit({ products, adjustment }) {
    return (
        <AppLayout title="Editar Ajuste de Stock">
            <h1 className="text-2xl font-bold mb-4">Editar Ajuste de Stock</h1>
            <AdjustmentForm products={products} adjustment={adjustment} />
        </AppLayout>
    );
}
