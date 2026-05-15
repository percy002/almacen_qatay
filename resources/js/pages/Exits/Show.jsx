import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ exit }) {
    return (
        <AppLayout title={`Salida: ${exit.exit_code}`}>
            <div className="mb-4 flex items-center justify-between">
                <h1 className="text-2xl font-bold">Salida {exit.exit_code}</h1>
                {can('update', 'WarehouseExit') && (
                    <a
                        href={route('exits.edit', exit.id)}
                        className="bg-amber-600 text-white px-3 py-2 rounded hover:bg-amber-700"
                    >
                        Editar
                    </a>
                )}
            </div>
            <div className="mb-2">Fecha: {exit.exit_date}</div>
            <div className="mb-2">Usuario: {exit.user_name}</div>
            <div className="mb-2">Notas: {exit.notes || '—'}</div>
            <div className="mt-6">
                <h2 className="text-lg font-semibold mb-2">Productos salidos</h2>
                <table className="min-w-full border text-sm">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="px-2 py-1">Producto</th>
                            <th className="px-2 py-1">Variante</th>
                            <th className="px-2 py-1">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        {exit.items.map(item => (
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
