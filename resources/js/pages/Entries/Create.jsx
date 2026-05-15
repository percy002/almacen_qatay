import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import EntryForm from '@/components/forms/EntryForm';

export default function Create({ products }) {
    return (
        <AppLayout title="Nueva Entrada de Almacén">
            <h1 className="text-2xl font-bold mb-4">Nueva Entrada de Almacén</h1>
            <EntryForm products={products} />
        </AppLayout>
    );
}
