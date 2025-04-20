<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notes = $request->user()->notes()->with('children')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notes
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'parent_id' => 'nullable|exists:notes,id',
        ]);

        $note = $request->user()->notes()->create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $note
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $note = Note::where('user_id', $request->user()->id)
            ->with('children', 'parent')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $note
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $note = Note::findOrFail($id);

        if ($note->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'parent_id' => 'nullable|exists:notes,id',
        ]);

        $note->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $note
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $note = Note::findOrFail($id);

        if ($note->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Note deleted successfully'
        ]);
    }
}
