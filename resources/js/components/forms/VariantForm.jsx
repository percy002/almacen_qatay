import React, { useEffect, useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, ImageIcon, Trash2, Upload } from 'lucide-react';

const MAX_IMAGE_SLOTS = 3;

function buildInitialSlots(variant) {
    const galleryPaths = variant.gallery_paths || (variant.image_path ? [variant.image_path] : []);
    const galleryUrls = variant.image_urls || (variant.image_url ? [variant.image_url] : []);

    return Array.from({ length: MAX_IMAGE_SLOTS }, (_, index) => ({
        originalPath: galleryPaths[index] || null,
        currentPath: galleryPaths[index] || null,
        previewUrl: galleryUrls[index] || null,
        file: null,
    }));
}

function getInitialActiveIndex(slots) {
    const imageIndex = slots.findIndex(slot => slot.previewUrl || slot.currentPath || slot.file);

    return imageIndex >= 0 ? imageIndex : 0;
}

export default function VariantForm({ productId, variant = {}, onSaved, onCancel }) {
    const safeVariant = variant ?? {};
    const isEdit = Boolean(safeVariant.id);
    const fileInputRefs = useRef([]);
    const [slots, setSlots] = useState(() => buildInitialSlots(safeVariant));
    const [activeSlot, setActiveSlot] = useState(() => getInitialActiveIndex(buildInitialSlots(safeVariant)));
    const { data, setData, post, transform, processing, progress, errors, reset, clearErrors } = useForm({
        variant_name: safeVariant.variant_name || '',
        sku: safeVariant.sku || '',
        min_stock: safeVariant.min_stock ?? 0,
    });

    useEffect(() => {
        const nextSlots = buildInitialSlots(safeVariant);

        setSlots(nextSlots);
        setActiveSlot(getInitialActiveIndex(nextSlots));
        setData('variant_name', safeVariant.variant_name || '');
        setData('sku', safeVariant.sku || '');
        setData('min_stock', safeVariant.min_stock ?? 0);
        clearErrors();
    }, [isEdit, safeVariant.id]);

    const resetImageSlots = () => {
        const nextSlots = buildInitialSlots(safeVariant);

        setSlots(nextSlots);
        setActiveSlot(getInitialActiveIndex(nextSlots));
    };

    const cancelForm = () => {
        reset();
        clearErrors();
        resetImageSlots();
        onCancel && onCancel();
    };

    const updateSlot = (index, updater) => {
        setSlots(previousSlots => previousSlots.map((slot, slotIndex) => {
            if (slotIndex !== index) {
                return slot;
            }

            const nextSlot = typeof updater === 'function' ? updater(slot) : updater;

            if (slot.previewUrl && slot.previewUrl.startsWith('blob:') && slot.previewUrl !== nextSlot.previewUrl) {
                URL.revokeObjectURL(slot.previewUrl);
            }

            return nextSlot;
        }));
    };

    const handleFileChange = (index, file) => {
        updateSlot(index, currentSlot => {
            if (currentSlot.previewUrl && currentSlot.previewUrl.startsWith('blob:')) {
                URL.revokeObjectURL(currentSlot.previewUrl);
            }

            return {
                ...currentSlot,
                file,
                previewUrl: file ? URL.createObjectURL(file) : (currentSlot.originalPath ? currentSlot.previewUrl : null),
            };
        });

        setActiveSlot(index);
    };

    const handleRemoveImage = index => {
        updateSlot(index, currentSlot => {
            if (currentSlot.previewUrl && currentSlot.previewUrl.startsWith('blob:')) {
                URL.revokeObjectURL(currentSlot.previewUrl);
            }

            return {
                ...currentSlot,
                currentPath: null,
                previewUrl: null,
                file: null,
            };
        });

        setActiveSlot(index);
    };

    const submitForm = e => {
        e.preventDefault();

        const originalImages = slots.map(slot => slot.originalPath);
        const currentImages = slots.map(slot => slot.currentPath);
        const images = slots.map(slot => slot.file);

        transform(formData => ({
            ...formData,
            original_images: originalImages,
            current_images: currentImages,
            images,
            ...(isEdit ? { _method: 'put' } : {}),
        }));

        post(isEdit ? route('variants.update', safeVariant.id) : route('products.variants.store', productId), {
            forceFormData: true,
            onSuccess: () => {
                reset();
                resetImageSlots();
                onSaved && onSaved();
            },
        });
    };

    const activeImage = slots[activeSlot] || slots[0];
    const hasActiveImage = Boolean(activeImage?.previewUrl || activeImage?.currentPath || activeImage?.file);
    const filledSlotCount = slots.filter(slot => slot.previewUrl || slot.file || slot.currentPath).length;

    return (
        <form onSubmit={submitForm} className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px]">
            <div className="space-y-4">
                {isEdit && (
                    <div className="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Editando variante: {safeVariant.variant_name}
                    </div>
                )}
                <div>
                    <label className="block font-semibold">Nombre de Variante</label>
                    <input
                        type="text"
                        className="input"
                        value={data.variant_name}
                        onChange={e => setData('variant_name', e.target.value)}
                    />
                    {errors.variant_name && <div className="text-sm text-red-600">{errors.variant_name}</div>}
                </div>
                <div>
                    <label className="block font-semibold">SKU (opcional)</label>
                    <input
                        type="text"
                        className="input"
                        value={data.sku}
                        placeholder="Se genera automáticamente si lo dejas vacío"
                        onChange={e => setData('sku', e.target.value)}
                    />
                    {!errors.sku && <div className="mt-1 text-xs text-gray-600">Si no ingresas SKU, el sistema lo crea automáticamente.</div>}
                    {errors.sku && <div className="text-sm text-red-600">{errors.sku}</div>}
                </div>
                <div>
                    <label className="block font-semibold">Stock Mínimo</label>
                    <input
                        type="number"
                        className="input"
                        value={data.min_stock}
                        min={0}
                        onChange={e => setData('min_stock', Number(e.target.value))}
                    />
                    {errors.min_stock && <div className="text-sm text-red-600">{errors.min_stock}</div>}
                </div>
                <div className="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                    Puedes cargar hasta 3 imágenes. Usa las flechas para navegar entre ellas.
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700">
                    El stock inicial de la variante es 0. Para ingresar stock usa Recepción.
                </div>

                <div className="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        onClick={cancelForm}
                        className="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        className="rounded-full bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        disabled={processing}
                    >
                        {isEdit ? 'Actualizar variante' : 'Agregar variante'}
                    </button>
                </div>
            </div>

            <aside className="rounded-[28px] border border-slate-800/20 bg-slate-950 p-2 text-white shadow-2xl shadow-slate-900/20">
                <div className="flex items-start justify-between gap-4">
                    <div className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/70">
                        {filledSlotCount}/{MAX_IMAGE_SLOTS}
                    </div>
                </div>

                <div className="mt-4 overflow-hidden rounded-[28px] border border-white/10 bg-white/5">
                    <div className="relative flex flex-col bg-slate-900/80">
                        <div className="relative h-[22rem] overflow-hidden sm:h-[24rem]">
                            {hasActiveImage ? (
                                <img
                                    src={activeImage.previewUrl}
                                    alt="Vista previa de imagen de variante"
                                    className="h-full w-full object-contain"
                                />
                            ) : (
                                <div className="flex h-full flex-col items-center justify-center gap-3 px-6 text-center text-white/55">
                                    <ImageIcon className="h-12 w-12" />
                                    <div>
                                        <p className="text-base font-medium text-white/80">Slot vacío</p>
                                        <p className="text-sm">Usa el botón Subir imagen para cargar una imagen.</p>
                                    </div>
                                </div>
                            )}

                            <button
                                type="button"
                                onClick={() => setActiveSlot(current => (current - 1 + slots.length) % slots.length)}
                                className="absolute left-3 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-black/60 p-3 text-white transition hover:bg-black/80"
                                aria-label="Imagen anterior"
                                disabled={slots.length <= 1}
                            >
                                <ChevronLeft className="h-5 w-5" />
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveSlot(current => (current + 1) % slots.length)}
                                className="absolute right-3 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-black/60 p-3 text-white transition hover:bg-black/80"
                                aria-label="Imagen siguiente"
                                disabled={slots.length <= 1}
                            >
                                <ChevronRight className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="border-t border-white/10 bg-slate-950/95 p-4">
                            <div className="flex items-center justify-between gap-3 text-xs text-white/70">
                                <span className="uppercase tracking-[0.25em]">Slot {activeSlot + 1}</span>
                                <span>{activeImage?.file ? 'Nuevo archivo' : activeImage?.currentPath ? 'Guardado' : 'Vacío'}</span>
                            </div>

                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    className="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/20"
                                    onClick={() => fileInputRefs.current[activeSlot]?.click()}
                                >
                                    <Upload className="h-4 w-4" />
                                    {hasActiveImage ? 'Cambiar imagen' : 'Subir imagen'}
                                </button>
                                <button
                                    type="button"
                                    className="inline-flex items-center gap-2 rounded-full bg-rose-500/15 px-4 py-2 text-sm font-medium text-rose-200 transition hover:bg-rose-500/25 disabled:cursor-not-allowed disabled:opacity-40"
                                    onClick={() => handleRemoveImage(activeSlot)}
                                    disabled={!hasActiveImage}
                                >
                                    <Trash2 className="h-4 w-4" />
                                    Borrar imagen
                                </button>
                                <input
                                    ref={element => {
                                        fileInputRefs.current[activeSlot] = element;
                                    }}
                                    type="file"
                                    accept="image/*"
                                    className="hidden"
                                    onChange={event => handleFileChange(activeSlot, event.target.files?.[0] || null)}
                                />
                            </div>

                            <div className="mt-4 flex items-center gap-2">
                                {slots.map((slot, index) => {
                                    const hasImage = Boolean(slot.previewUrl || slot.currentPath || slot.file);

                                    return (
                                        <button
                                            key={index}
                                            type="button"
                                            onClick={() => setActiveSlot(index)}
                                            className={`h-2.5 w-8 rounded-full transition ${activeSlot === index ? 'bg-cyan-300' : hasImage ? 'bg-white/45 hover:bg-white/60' : 'bg-white/10 hover:bg-white/20'}`}
                                            aria-label={`Ir al slot ${index + 1}`}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>

                {progress && (
                    <div className="mt-4 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-xs text-white/70">
                        Subiendo imágenes: {progress.percentage}%
                    </div>
                )}

                {errors.images && <div className="mt-3 text-sm text-rose-300">{errors.images}</div>}
            </aside>
        </form>
    );
}
