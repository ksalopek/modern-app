<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = Auth::user()->notes()
            // Eager load the tags for each note to prevent N+1 query issues
            ->with('tags')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();
        $note = Auth::user()->notes()->create($validated);

        $this->syncTags($request, $note);

        return redirect()->back();
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $validated = $request->validated();
        $note->update($validated);

        $this->syncTags($request, $note);

        return redirect()->back();
    }

    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);
        $note->delete();
        return redirect()->back();
    }

    /**
     * Sync the tags for the given note.
     */
    private function syncTags(Request $request, Note $note): void
    {
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = explode(',', $request->input('tags'));
            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName) {
                    // Find or create the tag and get its ID
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
        }

        // Sync the tags with the note
        $note->tags()->sync($tagIds);
    }
}
