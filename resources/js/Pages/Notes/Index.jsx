import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';

export default function Index({ notes }) {
    // 1. State for the "Create" form
    const { data, setData, post, processing, reset, errors } = useForm({
        title: '',
        content: '',
        notes: '',
    });

    // 2. React State to track which note we are currently editing
    const [editingNoteId, setEditingNoteId] = useState(null);
    const [editForm, setEditForm] = useState({ title: '', content: '', notes: '' });

    // --- Action Handlers ---

    const submitCreate = (e) => {
        e.preventDefault();
        post('/notes', { onSuccess: () => reset() });
    };

    const deleteNote = (id) => {
        if (confirm('Are you sure you want to delete this note?')) {
            router.delete(`/notes/${id}`);
        }
    };

    const startEditing = (note) => {
        setEditingNoteId(note.id);
        setEditForm({ title: note.title, content: note.content, notes: note.notes });
    };

    const submitUpdate = (e, id) => {
        e.preventDefault();
        router.put(`/notes/${id}`, editForm, {
            onSuccess: () => setEditingNoteId(null) // Close the edit form on success
        });
    };

    return (
        <div className="max-w-2xl mx-auto p-8">
            <Head title="My Notes" />
            <h1 className="text-3xl font-bold mb-6 text-gray-800">My Notes</h1>

            {/* --- CREATE FORM --- */}
            <form onSubmit={submitCreate} className="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        type="text"
                        value={data.title}
                        onChange={e => setData('title', e.target.value)}
                        className="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Note title..."
                    />
                    {errors.title && <div className="text-red-500 text-sm mt-1">{errors.title}</div>}
                </div>
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea
                        value={data.content}
                        onChange={e => setData('content', e.target.value)}
                        className="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="3"
                        placeholder="What's on your mind?"
                    ></textarea>
                    {errors.content && <div className="text-red-500 text-sm mt-1">{errors.content}</div>}
                </div>
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input
                        type="text"
                        value={data.notes}
                        onChange={e => setData('notes', e.target.value)}
                        className="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Add any notes desired..."
                    />
                    {errors.notes && <div className="text-red-500 text-sm mt-1">{errors.notes}</div>}
                </div>
                <button type="submit" disabled={processing} className="w-full bg-emerald-600 text-white font-bold text-lg px-6 py-3 rounded-lg shadow-lg hover:bg-emerald-700 transition-all">
                    {processing ? 'Saving...' : 'Save Note'}
                </button>
            </form>

            {/* --- NOTES LIST --- */}
            <div className="space-y-4">
                {notes.map((note) => (
                    <div key={note.id} className="p-6 bg-white rounded-lg shadow-md border border-gray-200 relative group">

                        {/* If this note is being edited, show the edit form */}
                        {editingNoteId === note.id ? (
                            <form onSubmit={(e) => submitUpdate(e, note.id)}>
                                <input
                                    type="text"
                                    value={editForm.title}
                                    onChange={e => setEditForm({...editForm, title: e.target.value})}
                                    className="w-full mb-2 border-gray-300 rounded-md shadow-sm"
                                />
                                <textarea
                                    value={editForm.content}
                                    onChange={e => setEditForm({...editForm, content: e.target.value})}
                                    className="w-full mb-4 border-gray-300 rounded-md shadow-sm"
                                    rows="3"
                                ></textarea>
                                <textarea
                                    value={editForm.notes}
                                    onChange={e => setEditForm({...editForm, notes: e.target.value})}
                                    className="w-full mb-4 border-gray-300 rounded-md shadow-sm"
                                    rows="3"
                                ></textarea>
                                <div className="flex gap-2">
                                    <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Save Changes</button>
                                    <button type="button" onClick={() => setEditingNoteId(null)} className="bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm hover:bg-gray-400">Cancel</button>
                                </div>
                            </form>
                        ) : (
                            /* Otherwise, show the normal note with action buttons */
                            <>
                                <h2 className="text-xl font-semibold text-gray-900">{note.title}</h2>
                                <p className="mt-2 text-gray-600">{note.content}</p>
                                <p className="mt-2 text-gray-600">{note.notes}</p>

                                <div className="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onClick={() => startEditing(note)} className="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                                        Edit
                                    </button>
                                    <button onClick={() => deleteNote(note.id)} className="text-sm text-red-600 hover:text-red-800 font-semibold">
                                        Delete
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
