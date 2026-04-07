<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    public function index()
    {
        // Fetch only notes for the currently authenticated user, paginated to 10 per page.
        // Thanks to the SoftDeletes trait, this will AUTOMATICALLY exclude "trashed" notes!
        $notes = Auth::user()->notes()->latest()->paginate(10);

        return Inertia::render('Notes/Index', [
            'notes' => $notes
        ]);
    }

    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();
        Auth::user()->notes()->create($validated);

        return redirect()->back();
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $validated = $request->validated();
        $note->update($validated);

        return redirect()->back();
    }

    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);

        // Because of the SoftDeletes trait on the Note model, this doesn't
        // actually run a DELETE SQL statement anymore. It runs an UPDATE
        // statement setting the `deleted_at` timestamp.
        $note->delete();

        return redirect()->back();
    }
}
