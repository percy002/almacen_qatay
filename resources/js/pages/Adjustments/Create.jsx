import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import AdjustmentForm from '@/components/forms/AdjustmentForm';

export default function Create({ products }) {
    return (
        <AppLayout title="Nuevo Ajuste de Stock">
            <h1 className="text-2xl font-bold mb-4">Nuevo Ajuste de Stock</h1>
            <AdjustmentForm products={products} />
        </AppLayout>
    );
}
