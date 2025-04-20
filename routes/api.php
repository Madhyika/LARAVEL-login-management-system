<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\Auth\LoginController;

// Public Routes
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string',
    ]);

    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    return response()->json(['token' => $user->createToken('API Token')->plainTextToken]);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'login' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->login)
    ->orWhere('name', $request->login) 
    ->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    return response()->json([
        'message' => 'Login successful',
        'user' => $user->only(['id', 'name', 'email']),
        'token' => $user->createToken('API Token')->plainTextToken
    ]);});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Logged out successfully']);
});

// Protected Routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    // Task-related routes
    Route::middleware('auth:sanctum')->group(function () {

    Route::get('/tasks/get', [TaskController::class, 'index']); // Get all tasks for the authenticated user
    Route::post('/tasks/create', [TaskController::class, 'store']); // Store a new task
    Route::put('/tasks/update/{id}', [TaskController::class, 'update']); // Update a task
    Route::delete('/tasks/delete/{id}', [TaskController::class, 'destroy']); // Delete a task
    });

    // Note Routes
    Route::get('/notes/get', [NoteController::class, 'index']);
    Route::post('/notes/create', [NoteController::class, 'store']);
    Route::get('/notes/{id}', [NoteController::class, 'show']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);