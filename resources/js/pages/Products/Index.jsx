import React from 'react';
import { usePage } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import DataTable from '@/components/ui/DataTable';

export default function Index() {
    const { products } = usePage().props;

    const columns = [
        { key: 'internal_code', label: 'Código Interno' },
        { key: 'name', label: 'Nombre' },
        { key: 'status', label: 'Estado' },
        { key: 'min_stock', label: 'Stock Mínimo' },
        { key: 'actions', label: 'Acciones' },
    ];

    const data = products.data.map(product => ({
        ...product,
        actions: (
            <a
                href={route('products.show', product.id)}
                className="text-blue-600 hover:underline"
            >
                Ver
            </a>
        ),
    }));

    return (
        <AppLayout title="Productos">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Productos</h1>
                {can('create', 'Product') && (
                    <a
                        href={route('products.create')}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Nuevo Producto
                    </a>
                )}
            </div>
            <DataTable columns={columns} data={data} paginator={products} searchable />
        </AppLayout>
    );
}
