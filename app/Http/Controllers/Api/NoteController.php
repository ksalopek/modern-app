<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    /**
     * Display a listing of the user's notes.
     */
    public function index(Request $request)
    {
        // Return the authenticated user's notes as a JSON collection.
        return $request->user()->notes()->latest()->paginate(10);
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $note = $request->user()->notes()->create($validated);

        // Return the newly created note as JSON with a 201 "Created" status code.
        return response()->json($note, 201);
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note)
    {
        // Authorize that the user can view this specific note.
        Gate::authorize('view', $note);

        // Return the note as a JSON object.
        return response()->json($note);
    }

    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note)
    {
        // Authorize that the user can update this note.
        Gate::authorize('update', $note);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $note->update($validated);

        // Return the updated note as JSON.
        return response()->json($note);
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note)
    {
        // Authorize that the user can delete this note.
        Gate::authorize('delete', $note);

        $note->delete();

        // Return an empty response with a 204 "No Content" status code.
        return response()->noContent();
    }
}
