<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects for the authenticated user's organisation.
     * Includes tasks with an is_assigned flag for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Ensure user has an organisation
        if (!$user->organisation_id) {
            return response()->json(['error' => 'User not associated with any organisation'], 403);
        }

        // Fetch projects for the user's organisation
        $projects = Project::where('organisation_id', $user->organisation_id)
            ->with(['tasks' => function ($query) use ($user) {
                $query->select('id', 'title', 'description', 'status', 'project_id', 'user_id')
                    ->with(['user' => function ($query) {
                        $query->select('id', 'first_name', 'last_name');
                    }]);
            }])
            ->get()
            ->map(function ($project) use ($user) {
                $project->tasks = $project->tasks->map(function ($task) use ($user) {
                    $task->is_assigned = $task->user_id === $user->id;
                    return $task;
                });
                return $project;
            });

        return response()->json($projects, 200);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check if user is admin
        if ($user->role !== UserRole::ADMIN->value) {
            return response()->json(['error' => 'Unauthorized: Only admins can create projects'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        // Check if user belongs to the specified organisation
        if ($user->organisation_id !== $validated['organisation_id']) {
            return response()->json(['error' => 'Unauthorized: You do not belong to this organisation'], 403);
        }

        // Create project
        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'organisation_id' => $validated['organisation_id'],
        ]);

        return response()->json($project, 201);
    }

    /**
     * Display the specified project by ID, including tasks with is_assigned flag.
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();

        // Fetch project by ID with tasks
        $project = Project::where('id', $id)
            ->where('organisation_id', $user->organisation_id)
            ->with(['tasks' => function ($query) use ($user) {
                $query->select('id', 'title', 'description', 'status', 'project_id', 'user_id')
                    ->with(['user' => function ($query) {
                        $query->select('id', 'first_name', 'last_name');
                    }]);
            }])
            ->firstOrFail();

        // Add is_assigned flag to tasks
        $project->tasks = $project->tasks->map(function ($task) use ($user) {
            $task->is_assigned = $task->user_id === $user->id;
            return $task;
        });

        return response()->json($project, 200);
    }

    /**
     * Update the specified project by ID.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        // Check if user is admin
        if ($user->role !== UserRole::ADMIN->value) {
            return response()->json(['error' => 'Unauthorized: Only admins can update projects'], 403);
        }

        // Fetch project by ID
        $project = Project::findOrFail($id);

        // Check if project belongs to user's organisation
        if ($project->organisation_id !== $user->organisation_id) {
            return response()->json(['error' => 'Unauthorized: Project does not belong to your organisation'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update project
        $project->update($validated);

        return response()->json($project, 200);
    }

    /**
     * Remove the specified project by ID.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        // Check if user is admin
        if ($user->role !== UserRole::ADMIN->value) {
            return response()->json(['error' => 'Unauthorized: Only admins can delete projects'], 403);
        }

        // Fetch project by ID
        $project = Project::findOrFail($id);

        // Check if project belongs to user's organisation
        if ($project->organisation_id !== $user->organisation_id) {
            return response()->json(['error' => 'Unauthorized: Project does not belong to your organisation'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
}
