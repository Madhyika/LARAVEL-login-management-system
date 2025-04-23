<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    // Get all task for the authenticated user
    public function index(Request $request): JsonResponse
    {
        // $tasks = $request->user()->tasks()->with('children')->get(); // Optional: eager load children
        $tasks = $request->user()->tasks()->whereNull('parent_id')->with('children')->get();
        // ->orderBy('id', 'desc')
        return response()->json([
            'status' => 'success',
            'data' => $tasks
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
        // if ($parentTask && $parentTask->parent_id !== null) {
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
