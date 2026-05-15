import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

import ProductForm from '@/components/forms/ProductForm';
import VariantForm from '@/components/forms/VariantForm';
import { useState } from 'react';


export default function Edit({ product }) {
    const [editingVariant, setEditingVariant] = useState(null);

    return (
        <AppLayout title={`Editar: ${product.name}`}>
            <h1 className="text-2xl font-bold mb-4">Editar Producto</h1>
            <ProductForm product={product} />

            <div className="mt-8">
                <h2 className="text-xl font-semibold mb-2">Variantes</h2>
                <VariantForm
                    productId={product.id}
                    variant={editingVariant}
                    onSaved={() => setEditingVariant(null)}
                />
                <div className="mt-4">
                    <table className="min-w-full border text-sm">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="px-2 py-1">Nombre</th>
                                <th className="px-2 py-1">SKU</th>
                                <th className="px-2 py-1">Stock</th>
                                <th className="px-2 py-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {product.variants?.map(variant => (
                                <tr key={variant.id}>
                                    <td className="border px-2 py-1">{variant.variant_name}</td>
                                    <td className="border px-2 py-1">{variant.sku}</td>
                                    <td className="border px-2 py-1">{variant.current_stock}</td>
                                    <td className="border px-2 py-1 space-x-2">
                                        <button
                                            className="text-blue-600 hover:underline"
                                            onClick={() => setEditingVariant(variant)}
                                        >Editar</button>
                                        <form
                                            method="POST"
                                            action={route('variants.destroy', variant.id)}
                                            onSubmit={e => {
                                                if (!confirm('¿Eliminar variante?')) e.preventDefault();
                                            }}
                                        >
                                            <input type="hidden" name="_method" value="DELETE" />
                                            <button type="submit" className="text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
