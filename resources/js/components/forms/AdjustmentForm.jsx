import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';

export default function AdjustmentForm({ products, adjustment = null }) {
    const isEdit = Boolean(adjustment?.id);

    const initialProductId = adjustment?.product_id ? String(adjustment.product_id) : '';
    const initialVariantId = adjustment?.product_variant_id ? String(adjustment.product_variant_id) : '';
    const initialStock = initialProductId && initialVariantId
        ? products
            .find(product => String(product.id) === initialProductId)
            ?.variants.find(variant => String(variant.id) === initialVariantId)?.current_stock ?? 0
        : 0;

    const [selectedProduct, setSelectedProduct] = useState(initialProductId);
    const [selectedVariant, setSelectedVariant] = useState(initialVariantId);
    const [currentStock, setCurrentStock] = useState(initialStock);
    const { data, setData, post, put, processing, errors } = useForm({
        adjustment_date: adjustment?.adjustment_date ?? '',
        adjustment_type: adjustment?.adjustment_type ?? 'incremento',
        quantity: adjustment?.quantity ?? 1,
        reason: adjustment?.reason ?? '',
        product_variant_id: initialVariantId,
    });

    const handleProductChange = e => {
        setSelectedProduct(e.target.value);
        setSelectedVariant('');
        setCurrentStock(0);
        setData('product_variant_id', '');
    };

    const handleVariantChange = e => {
        setSelectedVariant(e.target.value);
        setData('product_variant_id', e.target.value);
        const variant = products.find(p => p.id == selectedProduct)?.variants.find(v => v.id == e.target.value);
        setCurrentStock(variant ? variant.current_stock : 0);
    };

    const handleTypeChange = e => setData('adjustment_type', e.target.value);
    const handleQuantityChange = e => setData('quantity', e.target.value);
    const handleReasonChange = e => setData('reason', e.target.value);
    const handleDateChange = e => setData('adjustment_date', e.target.value);

    const isDecrement = data.adjustment_type === 'decremento';
    const resultStock = isDecrement ? currentStock - Number(data.quantity) : currentStock + Number(data.quantity);
    const reasonLength = data.reason.length;
    const reasonError = reasonLength < 10;
    const quantityError = isDecrement && Number(data.quantity) > currentStock;

    const handleSubmit = e => {
        e.preventDefault();

        if (isEdit) {
            put(route('adjustments.update', adjustment.id));

            return;
        }

        post(route('adjustments.store'));
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4 max-w-lg">
            <div>
                <label className="block font-semibold">Fecha de Ajuste</label>
                <input
                    type="date"
                    className="input"
                    value={data.adjustment_date}
                    onChange={handleDateChange}
                />
                {errors.adjustment_date && <div className="text-red-600 text-sm">{errors.adjustment_date}</div>}
            </div>
            <div>
                <label className="block font-semibold">Producto</label>
                <select className="input" value={selectedProduct} onChange={handleProductChange}>
                    <option value="">Selecciona</option>
                    {products.map(p => (
                        <option key={p.id} value={p.id}>{p.name}</option>
                    ))}
                </select>
            </div>
            <div>
                <label className="block font-semibold">Variante</label>
                <select
                    className="input"
                    value={selectedVariant}
                    onChange={handleVariantChange}
                    disabled={!selectedProduct}
                >
                    <option value="">Selecciona</option>
                    {selectedProduct && products.find(p => p.id == selectedProduct)?.variants.map(v => (
                        <option key={v.id} value={v.id}>{v.variant_name}</option>
                    ))}
                </select>
            </div>
            {selectedVariant && (
                <div className="mb-2 text-sm text-gray-700">Stock actual: <b>{currentStock}</b></div>
            )}
            <div>
                <label className="block font-semibold">Tipo de ajuste</label>
                <select className="input" value={data.adjustment_type} onChange={handleTypeChange}>
                    <option value="incremento">Incremento</option>
                    <option value="decremento">Decremento</option>
                </select>
            </div>
            <div>
                <label className="block font-semibold">Cantidad</label>
                <input
                    type="number"
                    className={`input ${quantityError ? 'border-red-500' : ''}`}
                    min={1}
                    value={data.quantity}
                    onChange={handleQuantityChange}
                />
                {quantityError && <div className="text-red-600 text-xs">No puede superar el stock actual</div>}
                {errors.quantity && <div className="text-red-600 text-sm">{errors.quantity}</div>}
            </div>
            <div>
                <label className="block font-semibold">Motivo</label>
                <textarea
                    className={`input ${reasonError ? 'border-red-500' : ''}`}
                    value={data.reason}
                    onChange={handleReasonChange}
                />
                <div className="text-xs text-gray-500">{reasonLength}/10 mínimo</div>
                {reasonError && <div className="text-red-600 text-xs">El motivo debe tener al menos 10 caracteres</div>}
                {errors.reason && <div className="text-red-600 text-sm">{errors.reason}</div>}
            </div>
            {selectedVariant && (
                <div className="mb-2 text-sm text-gray-700">Stock resultante: <b>{resultStock}</b></div>
            )}
            <button
                type="submit"
                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                disabled={processing || reasonError || quantityError}
            >
                {isEdit ? 'Actualizar Ajuste' : 'Guardar Ajuste'}
            </button>
        </form>
    );
}
