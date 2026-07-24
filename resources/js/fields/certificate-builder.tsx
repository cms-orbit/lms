import type { FieldComponentProps } from '@cms-orbit/core';
import { useRef, useState } from 'react';

interface Element {
    id: string;
    text: string;
    x: number;
    y: number;
    font_size: number;
    color: string;
    align: 'left' | 'center' | 'right';
    bold: boolean;
}

const CANVAS_WIDTH = 760;

function toElements(value: unknown): Element[] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.map((raw, index) => {
        const item = (raw ?? {}) as Record<string, unknown>;

        return {
            id: typeof item.id === 'string' ? item.id : `el-${index}`,
            text: typeof item.text === 'string' ? item.text : '',
            x: Number(item.x ?? 0),
            y: Number(item.y ?? 0),
            font_size: Number(item.font_size ?? 24),
            color: typeof item.color === 'string' ? item.color : '#111827',
            align: (item.align === 'left' || item.align === 'right' ? item.align : 'center') as Element['align'],
            bold: Boolean(item.bold),
        };
    });
}

function modelValue(data: Record<string, unknown>, key: string): unknown {
    const model = (data.model ?? {}) as Record<string, unknown>;

    return model[key];
}

/**
 * In-admin certificate template builder. Positions {{placeholder}} text over a
 * background; the value (element list) is written straight back into the Orbit
 * screen form via `onChange`.
 */
export default function CertificateBuilderField({ value, data, attributes, node, onChange }: FieldComponentProps) {
    const [elements, setElements] = useState<Element[]>(() => toElements(value));
    const [selectedId, setSelectedId] = useState<string | null>(null);
    const drag = useRef<{ id: string; dx: number; dy: number } | null>(null);

    const props = (node?.props ?? {}) as { placeholders?: string[] };
    const placeholders = props.placeholders ?? ['student_name', 'course_title', 'instructor_name', 'issued_date', 'serial'];
    const width = Number(modelValue(data, 'width') ?? 1123) || 1123;
    const height = Number(modelValue(data, 'height') ?? 794) || 794;
    const background = (modelValue(data, 'background') as string | null) ?? null;
    const scale = CANVAS_WIDTH / width;
    const selected = elements.find((element) => element.id === selectedId) ?? null;

    const commit = (next: Element[]) => {
        setElements(next);
        onChange?.(next);
    };

    const update = (id: string, patch: Partial<Element>) => {
        commit(elements.map((element) => (element.id === id ? { ...element, ...patch } : element)));
    };

    const onPointerDown = (event: React.PointerEvent, element: Element) => {
        event.preventDefault();
        setSelectedId(element.id);
        drag.current = { id: element.id, dx: event.clientX - element.x * scale, dy: event.clientY - element.y * scale };
    };

    const onPointerMove = (event: React.PointerEvent) => {
        if (!drag.current) {
            return;
        }
        const x = Math.round((event.clientX - drag.current.dx) / scale);
        const y = Math.round((event.clientY - drag.current.dy) / scale);
        update(drag.current.id, { x: Math.max(0, Math.min(width, x)), y: Math.max(0, Math.min(height, y)) });
    };

    const addElement = () => {
        const id = `el-${elements.length}-${Math.round(scale * 1000)}`;
        const next = [...elements, { id, text: 'New text', x: Math.round(width / 2), y: Math.round(height / 2), font_size: 28, color: '#111827', align: 'center' as const, bold: false }];
        commit(next);
        setSelectedId(id);
    };

    const removeSelected = () => {
        if (selectedId) {
            commit(elements.filter((element) => element.id !== selectedId));
            setSelectedId(null);
        }
    };

    const insertPlaceholder = (placeholder: string) => {
        if (selected) {
            update(selected.id, { text: `${selected.text} {{${placeholder}}}`.trim() });
        }
    };

    return (
        <div className="space-y-2">
            {attributes?.title ? <label className="block text-sm font-medium text-gray-700">{String(attributes.title)}</label> : null}

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div className="lg:col-span-3">
                    <div
                        onPointerMove={onPointerMove}
                        onPointerUp={() => (drag.current = null)}
                        onPointerLeave={() => (drag.current = null)}
                        className="relative mx-auto overflow-hidden rounded-lg border border-gray-300 bg-white"
                        style={{
                            width: CANVAS_WIDTH,
                            height: height * scale,
                            backgroundImage: background ? `url(${background})` : undefined,
                            backgroundSize: 'cover',
                            backgroundPosition: 'center',
                        }}
                    >
                        {elements.map((element) => (
                            <div
                                key={element.id}
                                onPointerDown={(event) => onPointerDown(event, element)}
                                className={`absolute cursor-move select-none whitespace-nowrap ${element.id === selectedId ? 'outline outline-2 outline-indigo-400' : ''}`}
                                style={{
                                    left: element.x * scale,
                                    top: element.y * scale,
                                    transform: element.align === 'center' ? 'translate(-50%, -50%)' : element.align === 'right' ? 'translate(-100%, -50%)' : 'translate(0, -50%)',
                                    fontSize: element.font_size * scale,
                                    color: element.color,
                                    fontWeight: element.bold ? 700 : 400,
                                }}
                            >
                                {element.text || '(empty)'}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="space-y-3">
                    <div className="flex gap-2">
                        <button type="button" onClick={addElement} className="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">
                            + Text
                        </button>
                        <button type="button" onClick={removeSelected} disabled={!selected} className="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 disabled:opacity-40">
                            Delete
                        </button>
                    </div>

                    {selected ? (
                        <div className="space-y-3 rounded-lg border border-gray-200 p-3">
                            <textarea
                                value={selected.text}
                                onChange={(event) => update(selected.id, { text: event.target.value })}
                                rows={2}
                                className="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm"
                            />
                            <div className="flex flex-wrap gap-1">
                                {placeholders.map((placeholder) => (
                                    <button
                                        key={placeholder}
                                        type="button"
                                        onClick={() => insertPlaceholder(placeholder)}
                                        className="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-200"
                                    >
                                        {`{{${placeholder}}}`}
                                    </button>
                                ))}
                            </div>
                            <div className="grid grid-cols-2 gap-2">
                                <label className="text-xs text-gray-500">
                                    Size
                                    <input type="number" value={selected.font_size} onChange={(event) => update(selected.id, { font_size: Number(event.target.value) })} className="mt-1 w-full rounded border border-gray-300 px-2 py-1 text-sm" />
                                </label>
                                <label className="text-xs text-gray-500">
                                    Color
                                    <input type="color" value={selected.color} onChange={(event) => update(selected.id, { color: event.target.value })} className="mt-1 h-8 w-full rounded border border-gray-300" />
                                </label>
                            </div>
                            <div className="flex items-center gap-2">
                                <select value={selected.align} onChange={(event) => update(selected.id, { align: event.target.value as Element['align'] })} className="rounded border border-gray-300 px-2 py-1 text-sm">
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                </select>
                                <label className="flex items-center gap-1 text-sm text-gray-600">
                                    <input type="checkbox" checked={selected.bold} onChange={(event) => update(selected.id, { bold: event.target.checked })} />
                                    Bold
                                </label>
                            </div>
                        </div>
                    ) : (
                        <p className="text-sm text-gray-400">Add a text box or select one to edit. Set size/background in the fields above.</p>
                    )}
                </div>
            </div>
        </div>
    );
}
