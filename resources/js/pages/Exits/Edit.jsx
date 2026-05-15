import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import ExitForm from '@/components/forms/ExitForm';

export default function Edit({ products, exit }) {
    return (
        <AppLayout title="Editar Salida de Almacén">
            <h1 className="text-2xl font-bold mb-4">Editar Salida de Almacén</h1>
            <ExitForm products={products} exit={exit} />
        </AppLayout>
    );
}
