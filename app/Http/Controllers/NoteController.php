<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $filters = json_decode($request->query('filters'), true);
        $search = $request->query('search');
        $perPage = $request->query('per_page', 5); // Default 10 notes per page
        
        // $query = $user->notes()->whereNull('parent_id');
        $query = $user->notes();
        
        if (isset($filters['name']) && !empty($filters['name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['name'] . '%')
                  ->orWhere('content', 'like', '%' . $filters['name'] . '%');
            });
        }
        
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }
       
        
        $notes = $query->orderBy('created_at', 'desc')->paginate($perPage);;

        return response()->json([
            'status' => 'success',
            'data' => $notes,
            'current_page' => $notes->currentPage(),
            'last_page' => $notes->lastPage(),
            'per_page' => $notes->perPage(),
            'total' => $notes->total(),
            
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
