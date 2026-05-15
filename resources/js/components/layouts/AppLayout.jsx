import React from 'react';
import { Head } from '@inertiajs/react';

export default function AppLayout({ title, children }) {
    return (
        <>
            {title ? <Head title={title} /> : null}
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {children}
            </div>
        </>
    );
}
