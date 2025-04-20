<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Fetch all notes for the authenticated user
        $notes = $request->user()->notes()->get();
        return response()->json($notes);
    }

    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
    ]);

        // Create a new note for the authenticated user
        $note = Note::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'user_id' => Auth::id(),
        ]);

        return response()->json($note, 201);
    }

    public function show($id): JsonResponse
    {
        // Fetch a single note for the authenticated user
        $note = Note::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($note);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',

        ]);

        // Find the note and update it
        $note = Note::where('user_id', Auth::id())->findOrFail($id);
        $note->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json($note);
    }

    public function destroy($id): JsonResponse
    {
        // Find the note and delete it
        $note = Note::where('user_id', Auth::id())->findOrFail($id);
        $note->delete();

        return response()->json(null, 204);
    }
}

