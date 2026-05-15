import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';

export default function EntryForm({ products, entry = null }) {
    const isEdit = Boolean(entry?.id);
    const [rows, setRows] = useState(() => {
        if (entry?.items?.length) {
            return entry.items.map(item => ({
                product_id: item.product_id ? String(item.product_id) : '',
                variant_id: item.variant_id ? String(item.variant_id) : '',
                quantity: Number(item.quantity) || 1,
            }));
        }

        return [{ product_id: '', variant_id: '', quantity: 1 }];
    });
    const { data, setData, post, put, transform, processing, errors } = useForm({
        entry_date: entry?.entry_date ?? '',
        notes: entry?.notes ?? '',
        items: [],
    });

    const handleRowChange = (idx, field, value) => {
        const updated = [...rows];
        updated[idx][field] = value;
        if (field === 'product_id') {
            updated[idx]['variant_id'] = '';
        }
        setRows(updated);
    };

    const addRow = () => setRows([...rows, { product_id: '', variant_id: '', quantity: 1 }]);
    const removeRow = idx => setRows(rows.filter((_, i) => i !== idx));

    const normalizeItems = () => rows.map(row => ({
        variant_id: row.variant_id,
        quantity: Number(row.quantity) || 0,
    }));

    const handleSubmit = e => {
        e.preventDefault();
        const items = normalizeItems();

        transform(formData => ({
            ...formData,
            items,
        }));

        if (isEdit) {
            put(route('entries.update', entry.id));

            return;
        }

        post(route('entries.store'));
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <label className="block font-semibold">Fecha de Entrada</label>
                <input
                    type="date"
                    className="input"
                    value={data.entry_date}
                    onChange={e => setData('entry_date', e.target.value)}
                />
                {errors.entry_date && <div className="text-red-600 text-sm">{errors.entry_date}</div>}
            </div>
            <div>
                <label className="block font-semibold">Notas</label>
                <textarea
                    className="input"
                    value={data.notes}
                    onChange={e => setData('notes', e.target.value)}
                />
                {errors.notes && <div className="text-red-600 text-sm">{errors.notes}</div>}
            </div>
            <div>
                <label className="block font-semibold mb-2">Productos</label>
                <table className="min-w-full border text-sm mb-2">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="px-2 py-1">Producto</th>
                            <th className="px-2 py-1">Variante</th>
                            <th className="px-2 py-1">Cantidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row, idx) => (
                            <tr key={idx}>
                                <td className="border px-2 py-1">
                                    <select
                                        className="input"
                                        value={row.product_id}
                                        onChange={e => handleRowChange(idx, 'product_id', e.target.value)}
                                    >
                                        <option value="">Selecciona</option>
                                        {products.map(p => (
                                            <option key={p.id} value={p.id}>{p.name}</option>
                                        ))}
                                    </select>
                                </td>
                                <td className="border px-2 py-1">
                                    <select
                                        className="input"
                                        value={row.variant_id}
                                        onChange={e => handleRowChange(idx, 'variant_id', e.target.value)}
                                        disabled={!row.product_id}
                                    >
                                        <option value="">Selecciona</option>
                                        {row.product_id && products.find(p => p.id == row.product_id)?.variants.map(v => (
                                            <option key={v.id} value={v.id}>{v.variant_name}</option>
                                        ))}
                                    </select>
                                </td>
                                <td className="border px-2 py-1">
                                    <input
                                        type="number"
                                        className="input"
                                        min={1}
                                        value={row.quantity}
                                        onChange={e => handleRowChange(idx, 'quantity', e.target.value)}
                                    />
                                </td>
                                <td className="border px-2 py-1">
                                    {rows.length > 1 && (
                                        <button type="button" onClick={() => removeRow(idx)} className="text-red-600">Eliminar</button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <button type="button" onClick={addRow} className="bg-gray-200 px-2 py-1 rounded">Agregar fila</button>
            </div>
            <button
                type="submit"
                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                disabled={processing}
            >
                {isEdit ? 'Actualizar Entrada' : 'Guardar Entrada'}
            </button>
        </form>
    );
}
