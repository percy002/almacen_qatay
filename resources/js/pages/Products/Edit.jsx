import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

import ProductForm from '@/components/forms/ProductForm';
import VariantForm from '@/components/forms/VariantForm';
import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';


export default function Edit({ product }) {
    const [isVariantModalOpen, setIsVariantModalOpen] = useState(false);
    const [editingVariant, setEditingVariant] = useState(null);

    const openCreateVariantModal = () => {
        setEditingVariant(null);
        setIsVariantModalOpen(true);
    };

    const openEditVariantModal = (variant) => {
        setEditingVariant(variant);
        setIsVariantModalOpen(true);
    };

    const closeVariantModal = () => {
        setEditingVariant(null);
        setIsVariantModalOpen(false);
    };

    return (
        <AppLayout title={`Editar: ${product.name}`}>
            <h1 className="text-2xl font-bold mb-4">Editar Producto</h1>
            <ProductForm product={product} />

            <div className="mt-8">
                <div className="mb-2 flex items-center justify-between gap-2">
                    <h2 className="text-xl font-semibold">Variantes</h2>
                    <button
                        type="button"
                        onClick={openCreateVariantModal}
                        className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        Agregar variante
                    </button>
                </div>
                <div className="mt-4">
                    <table className="min-w-full border text-sm">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="px-2 py-1">Imagen</th>
                                <th className="px-2 py-1">Nombre</th>
                                <th className="px-2 py-1">SKU</th>
                                <th className="px-2 py-1">Stock</th>
                                <th className="px-2 py-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {product.variants?.map(variant => (
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
                                    <td className="border px-2 py-1 space-x-2">
                                        <button
                                            className="text-blue-600 hover:underline"
                                            onClick={() => openEditVariantModal(variant)}
                                        >Editar</button>
                                        <form
                                            className="inline"
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

            <Dialog
                open={isVariantModalOpen}
                onOpenChange={open => {
                    if (!open) {
                        closeVariantModal();
                    }
                }}
            >
                <DialogContent className="sm:max-w-6xl max-h-[85dvh] overflow-hidden p-0">
                    <div className="flex max-h-[85dvh] flex-col overflow-hidden">
                        <div className="border-b border-slate-200 px-6 py-5">
                            <DialogHeader>
                                <DialogTitle>{editingVariant ? 'Editar variante' : 'Agregar variante'}</DialogTitle>
                            </DialogHeader>
                        </div>
                        <div className="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                            <VariantForm
                                productId={product.id}
                                variant={editingVariant}
                                onSaved={closeVariantModal}
                                onCancel={closeVariantModal}
                            />
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
