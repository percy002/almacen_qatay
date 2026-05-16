import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ product }) {
    return (
        <AppLayout title={`Detalle: ${product.name}`}>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">{product.name}</h1>
                {can('update', 'Product') && (
                    <a
                        href={route('products.edit', product.id)}
                        className="rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                    >
                        Gestionar variantes
                    </a>
                )}
            </div>
            <div className="mb-2">Código: {product.internal_code}</div>
            <div className="mb-2">Estado: {product.status}</div>
            <div className="mb-2">Descripción: {product.description}</div>
            <div className="mt-6">
                <h2 className="text-lg font-semibold mb-2">Variantes</h2>
                {product.variants && product.variants.length > 0 ? (
                    <table className="min-w-full border text-sm">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="px-2 py-1">Imagen</th>
                                <th className="px-2 py-1">Nombre</th>
                                <th className="px-2 py-1">SKU</th>
                                <th className="px-2 py-1">Stock</th>
                                <th className="px-2 py-1">Stock Mínimo</th>
                            </tr>
                        </thead>
                        <tbody>
                            {product.variants.map(variant => (
                                <tr key={variant.id}>
                                    <td className="border px-2 py-1">
                                        {variant.image_url ? (
                                            <img
                                                src={variant.image_url}
                                                alt={`Imagen de ${variant.variant_name}`}
                                                className="h-10 w-10 rounded border object-cover"
                                            />
                                        ) : (
                                            <span className="text-xs text-gray-500">Sin imagen</span>
                                        )}
                                    </td>
                                    <td className="border px-2 py-1">{variant.variant_name}</td>
                                    <td className="border px-2 py-1">{variant.sku}</td>
                                    <td className="border px-2 py-1">{variant.current_stock}</td>
                                    <td className="border px-2 py-1">{variant.min_stock}</td>
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
