import React, { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';

function cellToText(value) {
    if (value === null || value === undefined) {
        return '';
    }

    if (typeof value === 'string' || typeof value === 'number') {
        return String(value);
    }

    return '';
}

export default function DataTable({ columns = [], data = [], paginator = null, searchable = false }) {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredData = useMemo(() => {
        if (!searchable || !searchTerm.trim()) {
            return data;
        }

        const normalizedSearch = searchTerm.trim().toLowerCase();

        return data.filter(row =>
            columns.some(column => cellToText(row[column.key]).toLowerCase().includes(normalizedSearch)),
        );
    }, [columns, data, searchTerm, searchable]);

    const links = paginator?.links ?? [];

    return (
        <div className="space-y-3">
            {searchable ? (
                <div>
                    <input
                        type="text"
                        value={searchTerm}
                        onChange={event => setSearchTerm(event.target.value)}
                        placeholder="Buscar en la tabla..."
                        className="input max-w-sm"
                    />
                </div>
            ) : null}

            <div className="overflow-x-auto rounded border">
                <table className="min-w-full text-sm">
                    <thead>
                        <tr className="bg-gray-100 text-left">
                            {columns.map(column => (
                                <th key={column.key} className="px-3 py-2 font-semibold">
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {filteredData.length > 0 ? (
                            filteredData.map((row, index) => (
                                <tr key={row.id ?? index} className="border-t">
                                    {columns.map(column => (
                                        <td key={`${row.id ?? index}-${column.key}`} className="px-3 py-2 align-top">
                                            {row[column.key] ?? '—'}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={columns.length || 1} className="px-3 py-8 text-center text-gray-500">
                                    No hay registros para mostrar.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {links.length > 0 ? (
                <div className="flex flex-wrap gap-2">
                    {links.map((link, index) => {
                        if (!link.url) {
                            return (
                                <span
                                    key={`${index}-${link.label}`}
                                    className="rounded border px-2 py-1 text-xs text-gray-400"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            );
                        }

                        return (
                            <Link
                                key={`${index}-${link.label}`}
                                href={link.url}
                                className={`rounded border px-2 py-1 text-xs ${link.active ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 hover:bg-gray-100'}`}
                                preserveState
                                preserveScroll
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        );
                    })}
                </div>
            ) : null}
        </div>
    );
}
