import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ user }) {
    return (
        <AppLayout title={`Usuario: ${user.name}`}>
            <div className="mb-4 flex items-center justify-between gap-4">
                <h1 className="text-2xl font-bold">{user.name}</h1>
                <div className="flex items-center gap-3">
                    <a
                        href={route('users.edit', user.id)}
                        className="bg-amber-500 px-4 py-2 text-white rounded hover:bg-amber-600"
                    >
                        Editar Usuario
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
                        <button
                            type="submit"
                            className="bg-red-600 px-4 py-2 text-white rounded hover:bg-red-700"
                        >
                            Eliminar Usuario
                        </button>
                    </form>
                </div>
            </div>
            <div className="mb-2">Email: {user.email}</div>
            <div className="mb-2">Rol: {user.role}</div>
            <div className="mb-2">Creado: {user.created_at}</div>
            <div className="mb-2">Actualizado: {user.updated_at}</div>
        </AppLayout>
    );
}
