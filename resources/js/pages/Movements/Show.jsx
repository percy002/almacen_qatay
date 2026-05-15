import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Show({ movement }) {
    return (
        <AppLayout title={`Movimiento: ${movement.id}`}>
            <h1 className="text-2xl font-bold mb-4">Detalle de Movimiento</h1>
            <div className="mb-2">Fecha: {movement.date}</div>
            <div className="mb-2">Tipo: {movement.type}</div>
            <div className="mb-2">Producto: {movement.product}</div>
            <div className="mb-2">Variante: {movement.variant}</div>
            <div className="mb-2">Cantidad: {movement.quantity}</div>
            <div className="mb-2">Usuario: {movement.user}</div>
            <div className="mb-2">Referencia: {movement.reference}</div>
        </AppLayout>
    );
}
