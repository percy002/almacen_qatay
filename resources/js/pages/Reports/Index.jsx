import React from 'react';
import AppLayout from '@/components/layouts/AppLayout';

export default function Index({ reports }) {
    return (
        <AppLayout title="Reportes">
            <h1 className="text-2xl font-bold mb-6">Reportes</h1>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href={route('reports.stock')} className="block p-6 bg-white rounded shadow hover:bg-blue-50">
                    <h2 className="font-semibold text-lg mb-2">Stock actual</h2>
                    <p>Consulta el stock actual por producto y variante.</p>
                </a>
                <a href={route('reports.movements')} className="block p-6 bg-white rounded shadow hover:bg-blue-50">
                    <h2 className="font-semibold text-lg mb-2">Movimientos</h2>
                    <p>Visualiza todos los movimientos de stock en un rango de fechas.</p>
                </a>
                <a href={route('reports.entries')} className="block p-6 bg-white rounded shadow hover:bg-blue-50">
                    <h2 className="font-semibold text-lg mb-2">Entradas</h2>
                    <p>Reporte detallado de entradas de stock.</p>
                </a>
                <a href={route('reports.exits')} className="block p-6 bg-white rounded shadow hover:bg-blue-50">
                    <h2 className="font-semibold text-lg mb-2">Salidas</h2>
                    <p>Reporte detallado de salidas de stock.</p>
                </a>
                <a href={route('reports.adjustments')} className="block p-6 bg-white rounded shadow hover:bg-blue-50">
                    <h2 className="font-semibold text-lg mb-2">Ajustes</h2>
                    <p>Reporte de ajustes de stock realizados.</p>
                </a>
            </div>
        </AppLayout>
    );
}
