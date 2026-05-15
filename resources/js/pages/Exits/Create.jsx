import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import ExitForm from '@/components/forms/ExitForm';

export default function Create({ products }) {
    return (
        <AppLayout title="Nueva Salida de Almacén">
            <h1 className="text-2xl font-bold mb-4">Nueva Salida de Almacén</h1>
            <ExitForm products={products} />
        </AppLayout>
    );
}
