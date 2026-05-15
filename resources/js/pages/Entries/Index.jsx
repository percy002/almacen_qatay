import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';

export default function Index({ entries }) {
    const columns = [
        { key: 'entry_code', label: 'Código' },
        { key: 'entry_date', label: 'Fecha' },
        { key: 'user_name', label: 'Usuario' },
        { key: 'actions', label: 'Acciones' },
    ];

    const data = entries.data.map(entry => ({
        ...entry,
        actions: (
            <div className="flex items-center gap-3">
                <a
                    href={route('entries.show', entry.id)}
                    className="text-blue-600 hover:underline"
                >
                    Ver
                </a>
                {can('update', 'WarehouseEntry') && (
                    <a
                        href={route('entries.edit', entry.id)}
                        className="text-amber-700 hover:underline"
                    >
                        Editar
                    </a>
                )}
            </div>
        ),
    }));

    return (
        <AppLayout title="Entradas de Almacén">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Entradas de Almacén</h1>
                {can('create', 'WarehouseEntry') && (
                    <a
                        href={route('entries.create')}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Nueva Entrada
                    </a>
                )}
            </div>
            <DataTable columns={columns} data={data} paginator={entries} searchable />
        </AppLayout>
    );
}
