<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
   public function index(Request $request): JsonResponse
{
    $user = $request->user();
    
    $filters = json_decode($request->query('filters'), true);
    $search = $request->query('search');
    $perPage = $request->query('per_page', 5); // Default 5 tasks per page

    $query = $user->tasks()->whereNull('parent_id');
    
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

    
    $tasks = $query->with('children')->get();
    $tasks = $query->orderBy('created_at', 'desc')->paginate($perPage);;

    return response()->json([
        'status' => 'success',
        'data' => $tasks,
        'current_page' => $tasks->currentPage(),
        'last_page' => $tasks->lastPage(),
        'per_page' => $tasks->perPage(),
        'total' => $tasks->total(),
    ]);
}


    // Store a new task
    public function store(Request $request): JsonResponse
{
    // Validate incoming request data
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'nullable|string',
        'parent_id' => 'nullable|exists:tasks,id',
        'done' => 'boolean', 
    ]);

    if (!empty($validated['parent_id'])) {
        $parentTask = Task::find($validated['parent_id']);
        if ($parentTask->parent_id){
            return response()->json([
                'status' => 'error',
                'message' => 'Subtasks cannot have their own subtasks.'
            ], 400);
        }
    }
    
    // Create the task using the validated data
    $task = $request->user()->tasks()->create($validated);

    // Debug task data after saving it
    ($task->toArray());  // This will dump task data after it’s been saved.

    return response()->json([
        'status' => 'success',
        'data' => $task
    ], 201);
}

    // Show a specific task
    public function show(Request $request, $id): JsonResponse
    {
        $task = Task::where('user_id', $request->user()->id)                    
        ->with('children', 'parent')
        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
    }

    // Update an existing task
    public function update(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'done' => 'boolean',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        $task->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
    }

    // Delete a task
    public function destroy(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully'
        ]);
    }
}
