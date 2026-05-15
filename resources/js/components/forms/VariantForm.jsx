import React from 'react';
import { useForm } from '@inertiajs/react';

export default function VariantForm({ productId, variant = {}, onSaved }) {
    const isEdit = !!variant.id;
    const { data, setData, post, put, processing, errors, reset } = useForm({
        variant_name: variant.variant_name || '',
        sku: variant.sku || '',
        current_stock: variant.current_stock ?? 0,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('variants.update', variant.id), {
                onSuccess: () => onSaved && onSaved(),
            });
        } else {
            post(route('products.variants.store', productId), {
                onSuccess: () => {
                    reset();
                    onSaved && onSaved();
                },
            });
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <label className="block font-semibold">Nombre de Variante</label>
                <input
                    type="text"
                    className="input"
                    value={data.variant_name}
                    onChange={e => setData('variant_name', e.target.value)}
                />
                {errors.variant_name && <div className="text-red-600 text-sm">{errors.variant_name}</div>}
            </div>
            <div>
                <label className="block font-semibold">SKU</label>
                <input
                    type="text"
                    className="input"
                    value={data.sku}
                    onChange={e => setData('sku', e.target.value)}
                />
                {errors.sku && <div className="text-red-600 text-sm">{errors.sku}</div>}
            </div>
            <div>
                <label className="block font-semibold">Stock Actual</label>
                <input
                    type="number"
                    className="input"
                    value={data.current_stock}
                    min={0}
                    onChange={e => setData('current_stock', e.target.value)}
                />
                {errors.current_stock && <div className="text-red-600 text-sm">{errors.current_stock}</div>}
            </div>
            <button
                type="submit"
                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                disabled={processing}
            >
                {isEdit ? 'Actualizar' : 'Agregar Variante'}
            </button>
        </form>
    );
}
