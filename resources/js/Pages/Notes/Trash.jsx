import React, { useState, useEffect, useCallback } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { debounce } from 'lodash';

export default function Trash({ notes, filters }) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');

    const restoreNote = (id) => {
        if (confirm('Are you sure you want to restore this note?')) {
            router.put(route('notes.restore', id));
        }
    };

    const forceDeleteNote = (id) => {
        if (confirm('Are you sure you want to PERMANENTLY delete this note? This action cannot be undone.')) {
            router.delete(route('notes.forceDelete', id));
        }
    };

    const debouncedSearch = useCallback(
        debounce((nextValue) => {
            router.get(route('notes.trash'), { search: nextValue }, {
                preserveState: true,
                replace: true,
            });
        }, 300),
        []
    );

    useEffect(() => {
        if (searchTerm !== filters.search) {
            debouncedSearch(searchTerm);
        }
    }, [searchTerm, filters.search, debouncedSearch]);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Trash Can</h2>
                    <Link href={route('notes.index')} className="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                        &larr; Back to Notes
                    </Link>
                </div>
            }
        >
            <Head title="Trash" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    {/* --- SEARCH BAR --- */}
                    <div className="mb-8">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            placeholder="Search trashed notes..."
                            className="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        />
                    </div>

                    {/* --- NOTES LIST --- */}
                    <div className="space-y-4">
                        {notes.data.length === 0 ? (
                            <div className="p-6 bg-white rounded-lg shadow-md border border-gray-200 text-center text-gray-500">
                                Your trash is empty.
                            </div>
                        ) : (
                            notes.data.map((note) => (
                                <div key={note.id} className="p-6 bg-white rounded-lg shadow-md border border-gray-200 relative group opacity-75 hover:opacity-100 transition-opacity">
                                    <div className="flex justify-between items-start">
                                        <h2 className="text-xl font-semibold text-gray-900 line-through">{note.title}</h2>
                                        <div className="flex gap-4">
                                            <button onClick={() => restoreNote(note.id)} className="text-sm text-emerald-600 hover:text-emerald-800 font-bold">
                                                Restore
                                            </button>
                                            <button onClick={() => forceDeleteNote(note.id)} className="text-sm text-red-600 hover:text-red-800 font-bold">
                                                Delete Forever
                                            </button>
                                        </div>
                                    </div>
                                    <p className="mt-2 text-gray-600">{note.content}</p>
                                    <p className="mt-2 text-gray-600">{note.notes}</p>
                                    <p className="mt-4 text-xs text-red-500 font-semibold">
                                        Deleted: {new Date(note.deleted_at).toLocaleString()}
                                    </p>
                                </div>
                            ))
                        )}
                    </div>

                    {/* --- PAGINATION LINKS --- */}
                    {notes.links && notes.links.length > 3 && (
                        <div className="mt-8 flex justify-center gap-1">
                            {notes.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url || '#'}
                                    className={`px-4 py-2 border rounded-md text-sm ${
                                        link.active
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
