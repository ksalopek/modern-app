<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = Auth::user()->notes()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Appends query strings like ?search=... to pagination links

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
            'filters' => $request->only(['search']), // Pass the search filter back to the view
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

        $note->delete();

        return redirect()->back();
    }
}
