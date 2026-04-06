<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Inertia\Inertia;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        // Fetch all notes from the database
        $notes = Note::latest()->get();

        // Pass them to a React component called 'Notes/Index'
        return Inertia::render('Notes/Index', [
            'notes' => $notes
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notes' => 'string|max:255',
        ]);

        // 2. Create the note in the database
        Note::create($validated);

        // 3. Redirect back to the index page.
        // Inertia will automatically intercept this and update the React state without a page reload!
        return redirect()->back();
    }

    // Add this to update an existing note
    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notes' => 'string|max:255',
        ]);

        $note->update($validated);

        return redirect()->back();
    }

    // Add this to delete a note
    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()->back();
    }
}
