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
            ->with('tags')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhereHas('tags', function ($tagQuery) use ($search) {
                          $tagQuery->where('name', 'like', "%{$search}%");
                      });
                });
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

        return redirect()->back()->with('message', 'Note created successfully!');
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $validated = $request->validated();
        $note->update($validated);
        $this->syncTags($request, $note);

        return redirect()->back()->with('message', 'Note updated successfully!');
    }

    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);
        $note->delete();
        return redirect()->back()->with('message', 'Note moved to trash!');
    }

    public function trash(Request $request)
    {
        $trashedNotes = Auth::user()->notes()
            ->onlyTrashed()
            ->with('tags')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhereHas('tags', function ($tagQuery) use ($search) {
                          $tagQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Notes/Trash', [
            'notes' => $trashedNotes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function restore($id)
    {
        $note = Note::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $note);
        $note->restore();

        return redirect()->route('notes.trash')->with('message', 'Note restored successfully!');
    }

    public function forceDelete($id)
    {
        $note = Note::withTrashed()->findOrFail($id);
        Gate::authorize('forceDelete', $note);
        $note->forceDelete();

        return redirect()->route('notes.trash')->with('message', 'Note permanently deleted!');
    }

    private function syncTags(Request $request, Note $note): void
    {
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = explode(',', $request->input('tags'));
            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
        }
        $note->tags()->sync($tagIds);
    }
}
