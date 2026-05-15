import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';
import UserForm from '@/components/forms/UserForm';

export default function Create({ roles }) {
    return (
        <AppLayout title="Nuevo Usuario">
            <h1 className="text-2xl font-bold mb-4">Nuevo Usuario</h1>
            <UserForm roles={roles} />
        </AppLayout>
    );
}
