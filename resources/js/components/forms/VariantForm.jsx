import React, { useEffect } from 'react';
import { useForm } from '@inertiajs/react';

export default function VariantForm({ productId, variant = {}, onSaved }) {
    const safeVariant = variant ?? {};
    const isEdit = !!safeVariant.id;
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        variant_name: safeVariant.variant_name || '',
        sku: safeVariant.sku || '',
    });

    useEffect(() => {
        if (isEdit) {
            setData('variant_name', safeVariant.variant_name || '');
            setData('sku', safeVariant.sku || '');
            clearErrors();

            return;
        }

        reset();
        clearErrors();
    }, [isEdit, safeVariant.id]);

    const cancelEdit = () => {
        reset();
        clearErrors();
        onSaved && onSaved();
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('variants.update', safeVariant.id), {
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
            {isEdit && (
                <div className="rounded-md bg-blue-50 px-3 py-2 text-xs text-blue-800">
                    Editando variante: {safeVariant.variant_name}
                </div>
            )}
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
                <label className="block font-semibold">SKU (opcional)</label>
                <input
                    type="text"
                    className="input"
                    value={data.sku}
                    placeholder="Se genera automáticamente si lo dejas vacío"
                    onChange={e => setData('sku', e.target.value)}
                />
                {!errors.sku && <div className="mt-1 text-xs text-gray-600">Si no ingresas SKU, el sistema lo crea automáticamente.</div>}
                {errors.sku && <div className="text-red-600 text-sm">{errors.sku}</div>}
            </div>
            <div className="rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">
                El stock inicial de la variante es 0. Para ingresar stock usa Recepción.
            </div>
            <button
                type="submit"
                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                disabled={processing}
            >
                {isEdit ? 'Actualizar' : 'Agregar Variante'}
            </button>
            {isEdit && (
                <button
                    type="button"
                    onClick={cancelEdit}
                    className="ml-2 bg-gray-200 px-4 py-2 rounded text-gray-800 hover:bg-gray-300"
                >
                    Cancelar
                </button>
            )}
        </form>
    );
}
