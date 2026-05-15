import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import UserForm from '@/components/forms/UserForm';

export default function Edit({ user, roles }) {
    return (
        <AppLayout title={`Editar: ${user.name}`}>
            <h1 className="text-2xl font-bold mb-4">Editar Usuario</h1>
            <UserForm roles={roles} user={user} />
        </AppLayout>
    );
}
