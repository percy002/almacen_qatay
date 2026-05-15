import React from 'react';
import { useForm } from '@inertiajs/react';

export default function UserForm({ roles, user = null }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name || '',
        email: user?.email || '',
        role: user?.role || '',
        password: '',
        password_confirmation: '',
    });

    const handleChange = e => setData(e.target.name, e.target.value);
    const handleSubmit = e => {
        e.preventDefault();
        if (user) {
            put(route('users.update', user.id));
        } else {
            post(route('users.store'));
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4 max-w-lg">
            <div>
                <label className="block font-semibold">Nombre</label>
                <input
                    type="text"
                    name="name"
                    className="input"
                    value={data.name}
                    onChange={handleChange}
                />
                {errors.name && <div className="text-red-600 text-sm">{errors.name}</div>}
            </div>
            <div>
                <label className="block font-semibold">Email</label>
                <input
                    type="email"
                    name="email"
                    className="input"
                    value={data.email}
                    onChange={handleChange}
                />
                {errors.email && <div className="text-red-600 text-sm">{errors.email}</div>}
            </div>
            <div>
                <label className="block font-semibold">Rol</label>
                <select
                    name="role"
                    className="input"
                    value={data.role}
                    onChange={handleChange}
                >
                    <option value="">Selecciona</option>
                    {roles.map(role => (
                        <option key={role} value={role}>{role}</option>
                    ))}
                </select>
                {errors.role && <div className="text-red-600 text-sm">{errors.role}</div>}
            </div>
            <div>
                <label className="block font-semibold">Contraseña {user && <span className="text-xs text-gray-500">(dejar en blanco para no cambiar)</span>}</label>
                <input
                    type="password"
                    name="password"
                    className="input"
                    value={data.password}
                    onChange={handleChange}
                />
                {errors.password && <div className="text-red-600 text-sm">{errors.password}</div>}
            </div>
            <div>
                <label className="block font-semibold">Confirmar contraseña</label>
                <input
                    type="password"
                    name="password_confirmation"
                    className="input"
                    value={data.password_confirmation}
                    onChange={handleChange}
                />
                {errors.password_confirmation && <div className="text-red-600 text-sm">{errors.password_confirmation}</div>}
            </div>
            <button
                type="submit"
                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                disabled={processing}
            >
                {user ? 'Actualizar' : 'Crear'} Usuario
            </button>
        </form>
    );
}
