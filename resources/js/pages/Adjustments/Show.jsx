import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ adjustment }) {
    return (
        <AppLayout title={`Ajuste: ${adjustment.adjustment_code}`}>
            <div className="mb-4 flex items-center justify-between">
                <h1 className="text-2xl font-bold">Ajuste {adjustment.adjustment_code}</h1>
                {can('update', 'StockAdjustment') && (
                    <a
                        href={route('adjustments.edit', adjustment.id)}
                        className="bg-amber-600 text-white px-3 py-2 rounded hover:bg-amber-700"
                    >
                        Editar
                    </a>
                )}
            </div>
            <div className="mb-2">Fecha: {adjustment.adjustment_date}</div>
            <div className="mb-2">Variante: {adjustment.variant_name}</div>
            <div className="mb-2">Tipo: {adjustment.adjustment_type}</div>
            <div className="mb-2">Cantidad: {adjustment.quantity}</div>
            <div className="mb-2">Stock antes: {adjustment.stock_before}</div>
            <div className="mb-2">Stock después: {adjustment.stock_after}</div>
            <div className="mb-2">Motivo: {adjustment.reason}</div>
            <div className="mb-2">Usuario: {adjustment.user_name}</div>
        </AppLayout>
    );
}
