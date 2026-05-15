import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';

export default function Index({ users }) {
    const columns = [
        { key: 'name', label: 'Nombre' },
        { key: 'email', label: 'Email' },
        { key: 'role', label: 'Rol' },
        { key: 'actions', label: 'Acciones' },
    ];

    const data = users.data.map(user => ({
        ...user,
        actions: (
            <div className="flex items-center gap-3">
                <a
                    href={route('users.show', user.id)}
                    className="text-blue-600 hover:underline"
                >
                    Ver
                </a>
                <a
                    href={route('users.edit', user.id)}
                    className="text-amber-600 hover:underline"
                >
                    Editar
                </a>
                <form
                    method="POST"
                    action={route('users.destroy', user.id)}
                    onSubmit={event => {
                        if (!confirm('¿Eliminar usuario?')) {
                            event.preventDefault();
                        }
                    }}
                >
                    <input type="hidden" name="_method" value="DELETE" />
                    <button type="submit" className="text-red-600 hover:underline">
                        Eliminar
                    </button>
                </form>
            </div>
        ),
    }));

    return (
        <AppLayout title="Usuarios">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Usuarios</h1>
                {can('create', 'User') && (
                    <a
                        href={route('users.create')}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Nuevo Usuario
                    </a>
                )}
            </div>
            <DataTable columns={columns} data={data} paginator={users} searchable />
        </AppLayout>
    );
}
