<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Chatroom;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        return Task::where('project_id', $projectId)
            ->with(['user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }])
            ->get()
            ->map(function ($task) {
                $task->tags = json_decode($task->tags, true) ?? [];
                return $task;
            });
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*.name' => 'string',
            'tags.*.color' => 'string',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'project_id' => $request->project_id,
            'user_id' => $request->user_id,
            'priority' => $request->priority,
            'due_date' => Carbon::create($request->due_date),
            'tags' => json_encode($request->tags ?? []),
        ]);

        $this->notifyChatroom($task, 'created');

        return response()->json(['data' => $task->load('user')], 201);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*.name' => 'string',
            'tags.*.color' => 'string',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => $request->user_id ?? $task->user_id,
            'priority' => $request->priority ?? $task->priority,
            'due_date' => Carbon::create($request->due_date) ?? $task->due_date,
            'tags' => json_encode($request->tags ?? json_decode($task->tags, true) ?? []),
        ]);

        $this->notifyChatroom($task, 'updated');

        return response()->json(['data' => $task->load('user')]);
    }

    public function destroy(Task $task)
    {
        $this->notifyChatroom($task, 'deleted');
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    protected function notifyChatroom(Task $task, string $action)
    {
        $chatroom = Chatroom::where('project_id', $task->project_id)
            ->where('type', 'project')
            ->first();

        if ($chatroom && $task->user) {
            $messageContent = match ($action) {
                'created' => "Tâche '{$task->title}' a été créée par {$task->user->first_name} {$task->user->last_name}.",
                'updated' => "Tâche '{$task->title}' a été mise à jour par {$task->user->first_name} {$task->user->last_name}.",
                'deleted' => "Tâche '{$task->title}' a été supprimée par {$task->user->first_name} {$task->user->last_name}.",
            };

            $message = Message::create([
                'content' => $messageContent,
                'user_id' => 1, // Bot user ID (ensure this user exists in your DB)
                'chatroom_id' => $chatroom->id,
            ]);

            broadcast(new MessageSent($message))->toOthers();
        }
    }
    public function statistics(Request $request)
    {
        $projectId = $request->query('project_id');

        if (!$projectId) {
            return response()->json(['error' => 'Project ID is required'], 400);
        }

        $tasks = Task::where('project_id', $projectId)
            ->with(['user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }])
            ->get()
            ->map(function ($task) {
                $task->tags = json_decode($task->tags, true) ?? [];
                return $task;
            });

        // Aggregate tasks by status
        $byStatus = [
            'todo' => $tasks->where('status', 'todo')->count(),
            'in-progress' => $tasks->where('status', 'in-progress')->count(),
            'review' => $tasks->where('status', 'review')->count(),
            'done' => $tasks->where('status', 'done')->count(),
        ];

        // Aggregate tasks by priority
        $byPriority = [
            'low' => $tasks->where('priority', 'low')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
            'urgent' => $tasks->where('priority', 'urgent')->count(),
        ];

        return response()->json([
            'data' => [
                'tasks' => $tasks,
                'byStatus' => $byStatus,
                'byPriority' => $byPriority,
            ]
        ]);
    }
}
