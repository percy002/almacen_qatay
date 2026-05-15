import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ product }) {
    return (
        <AppLayout title={`Detalle: ${product.name}`}>
            <h1 className="text-2xl font-bold mb-4">{product.name}</h1>
            <div className="mb-2">Código: {product.internal_code}</div>
            <div className="mb-2">Estado: {product.status}</div>
            <div className="mb-2">Stock mínimo: {product.min_stock}</div>
            <div className="mb-2">Descripción: {product.description}</div>
            <div className="mt-6">
                <h2 className="text-lg font-semibold mb-2">Variantes</h2>
                {product.variants && product.variants.length > 0 ? (
                    <table className="min-w-full border text-sm">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="px-2 py-1">Nombre</th>
                                <th className="px-2 py-1">SKU</th>
                                <th className="px-2 py-1">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            {product.variants.map(variant => (
                                <tr key={variant.id}>
                                    <td className="border px-2 py-1">{variant.variant_name}</td>
                                    <td className="border px-2 py-1">{variant.sku}</td>
                                    <td className="border px-2 py-1">{variant.current_stock}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                ) : (
                    <div className="text-gray-500">Sin variantes registradas.</div>
                )}
            </div>
        </AppLayout>
    );
}
