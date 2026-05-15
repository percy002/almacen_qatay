import React, { useState } from 'react';
import { router } from '@inertiajs/react';

export default function ReportFilters({ routeName, filters = {}, showDateRange = true }) {
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');
    const [q, setQ] = useState(filters.q ?? '');

    const queryParams = {
        ...(showDateRange && from ? { from } : {}),
        ...(showDateRange && to ? { to } : {}),
        ...(q ? { q } : {}),
    };

    const applyFilters = event => {
        event.preventDefault();

        router.get(route(routeName), queryParams, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setFrom('');
        setTo('');
        setQ('');

        router.get(route(routeName), {}, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <form onSubmit={applyFilters} className="mb-4 rounded border p-3">
            <div className="grid gap-3 md:grid-cols-4">
                {showDateRange && (
                    <div>
                        <label className="mb-1 block text-sm font-medium">Desde</label>
                        <input
                            type="date"
                            className="input"
                            value={from}
                            onChange={event => setFrom(event.target.value)}
                        />
                    </div>
                )}

                {showDateRange && (
                    <div>
                        <label className="mb-1 block text-sm font-medium">Hasta</label>
                        <input
                            type="date"
                            className="input"
                            value={to}
                            onChange={event => setTo(event.target.value)}
                        />
                    </div>
                )}

                <div className={showDateRange ? '' : 'md:col-span-2'}>
                    <label className="mb-1 block text-sm font-medium">Buscar</label>
                    <input
                        type="text"
                        className="input"
                        placeholder="Código, producto, usuario..."
                        value={q}
                        onChange={event => setQ(event.target.value)}
                    />
                </div>

                <div className="flex items-end gap-2">
                    <button type="submit" className="rounded bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                        Filtrar
                    </button>
                    <button
                        type="button"
                        onClick={clearFilters}
                        className="rounded bg-gray-200 px-3 py-2 text-gray-900 hover:bg-gray-300"
                    >
                        Limpiar
                    </button>
                </div>
            </div>
        </form>
    );
}
