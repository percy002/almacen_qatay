import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';

export default function Index({ exits }) {
    const columns = [
        { key: 'exit_code', label: 'Código' },
        { key: 'exit_date', label: 'Fecha' },
        { key: 'user_name', label: 'Usuario' },
        { key: 'actions', label: 'Acciones' },
    ];

    const data = exits.data.map(exit => ({
        ...exit,
        actions: (
            <div className="flex items-center gap-3">
                <a
                    href={route('exits.show', exit.id)}
                    className="text-blue-600 hover:underline"
                >
                    Ver
                </a>
                {can('update', 'WarehouseExit') && (
                    <a
                        href={route('exits.edit', exit.id)}
                        className="text-amber-700 hover:underline"
                    >
                        Editar
                    </a>
                )}
            </div>
        ),
    }));

    return (
        <AppLayout title="Salidas de Almacén">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Salidas de Almacén</h1>
                {can('create', 'WarehouseExit') && (
                    <a
                        href={route('exits.create')}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Nueva Salida
                    </a>
                )}
            </div>
            <DataTable columns={columns} data={data} paginator={exits} searchable />
        </AppLayout>
    );
}
