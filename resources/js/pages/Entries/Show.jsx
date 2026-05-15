import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ entry }) {
    return (
        <AppLayout title={`Entrada: ${entry.entry_code}`}>
            <div className="mb-4 flex items-center justify-between">
                <h1 className="text-2xl font-bold">Entrada {entry.entry_code}</h1>
                {can('update', 'WarehouseEntry') && (
                    <a
                        href={route('entries.edit', entry.id)}
                        className="bg-amber-600 text-white px-3 py-2 rounded hover:bg-amber-700"
                    >
                        Editar
                    </a>
                )}
            </div>
            <div className="mb-2">Fecha: {entry.entry_date}</div>
            <div className="mb-2">Usuario: {entry.user_name}</div>
            <div className="mb-2">Notas: {entry.notes || '—'}</div>
            <div className="mt-6">
                <h2 className="text-lg font-semibold mb-2">Productos ingresados</h2>
                <table className="min-w-full border text-sm">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="px-2 py-1">Producto</th>
                            <th className="px-2 py-1">Variante</th>
                            <th className="px-2 py-1">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        {entry.items.map(item => (
                            <tr key={item.id}>
                                <td className="border px-2 py-1">{item.product_name}</td>
                                <td className="border px-2 py-1">{item.variant_name}</td>
                                <td className="border px-2 py-1">{item.quantity}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
