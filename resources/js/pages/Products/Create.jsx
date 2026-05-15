import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import ProductForm from '@/components/forms/ProductForm';

export default function Create() {
    return (
        <AppLayout title="Nuevo Producto">
            <h1 className="text-2xl font-bold mb-4">Nuevo Producto</h1>
            <ProductForm />
        </AppLayout>
    );
}
