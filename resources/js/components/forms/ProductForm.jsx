import React from 'react';
import { useForm } from '@inertiajs/react';

export default function ProductForm({ product = {} }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: product.name || '',
        internal_code: product.internal_code || '',
        description: product.description || '',
        min_stock: product.min_stock || 0,
        status: product.status || 'activo',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (product.id) {
            put(route('products.update', product.id));
        } else {
            post(route('products.store'));
        }
    };

    return (
        <form onSubmit={handleSubmit} className="max-w-lg space-y-4">
            <div>
                <label className="block font-semibold">Nombre</label>
                <input
                    type="text"
                    className="input"
                    value={data.name}
                    onChange={e => setData('name', e.target.value)}
                />
                {errors.name && <div className="text-red-600 text-sm">{errors.name}</div>}
            </div>
            <div>
                <label className="block font-semibold">Código Interno</label>
                <input
                    type="text"
                    className="input"
                    value={data.internal_code}
                    onChange={e => setData('internal_code', e.target.value)}
                />
                {errors.internal_code && <div className="text-red-600 text-sm">{errors.internal_code}</div>}
            </div>
            <div>
                <label className="block font-semibold">Descripción</label>
                <textarea
                    className="input"
                    value={data.description}
                    onChange={e => setData('description', e.target.value)}
                />
                {errors.description && <div className="text-red-600 text-sm">{errors.description}</div>}
            </div>
            <div>
                <label className="block font-semibold">Stock Mínimo</label>
                <input
                    type="number"
                    className="input"
                    value={data.min_stock}
                    min={0}
                    onChange={e => setData('min_stock', e.target.value)}
                />
                {errors.min_stock && <div className="text-red-600 text-sm">{errors.min_stock}</div>}
            </div>
            <div>
                <label className="block font-semibold">Estado</label>
                <select
                    className="input"
                    value={data.status}
                    onChange={e => setData('status', e.target.value)}
                >
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                {errors.status && <div className="text-red-600 text-sm">{errors.status}</div>}
            </div>
            <button
                type="submit"
                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                disabled={processing}
            >
                {product.id ? 'Actualizar' : 'Crear'}
            </button>
        </form>
    );
}
