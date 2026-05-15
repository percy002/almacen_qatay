import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import EntryForm from '@/components/forms/EntryForm';

export default function Edit({ products, entry }) {
    return (
        <AppLayout title="Editar Entrada de Almacén">
            <h1 className="text-2xl font-bold mb-4">Editar Entrada de Almacén</h1>
            <EntryForm products={products} entry={entry} />
        </AppLayout>
    );
}
