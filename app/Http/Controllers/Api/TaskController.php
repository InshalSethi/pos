<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskBoard;
use App\Models\TaskColumn;
use App\Models\User;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Get active task boards for current company.
     * Auto-creates a default board with 4 columns if none exist.
     */
    public function getBoards(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        if (!$companyId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active company selected.',
            ], 400);
        }

        $boards = TaskBoard::with(['columns' => function ($q) {
            $q->orderBy('order', 'asc');
        }])
        ->withCount('tasks')
        ->where('company_id', $companyId)
        ->get();

        // If company has no task boards yet, create default board and columns
        if ($boards->isEmpty()) {
            $defaultBoard = TaskBoard::create([
                'company_id' => $companyId,
                'created_by_id' => $user->id,
                'name' => 'Main Project Board',
                'description' => 'Default Kanban Task Board',
                'color' => '#4F46E5',
                'is_default' => true,
            ]);

            $defaultColumns = [
                ['name' => 'Backlog / To Do', 'color' => 'slate', 'order' => 0],
                ['name' => 'In Progress', 'color' => 'amber', 'order' => 1],
                ['name' => 'In Review', 'color' => 'indigo', 'order' => 2],
                ['name' => 'Completed', 'color' => 'emerald', 'order' => 3],
            ];

            foreach ($defaultColumns as $col) {
                TaskColumn::create([
                    'task_board_id' => $defaultBoard->id,
                    'company_id' => $companyId,
                    'name' => $col['name'],
                    'color' => $col['color'],
                    'order' => $col['order'],
                ]);
            }

            $boards = TaskBoard::with(['columns' => function ($q) {
                $q->orderBy('order', 'asc');
            }])
            ->where('company_id', $companyId)
            ->get();
        }

        return response()->json([
            'status' => 'success',
            'boards' => $boards,
        ]);
    }

    /**
     * Create a new Task Board for the active company.
     */
    public function createBoard(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $companyId = $user->current_company_id;

        $board = TaskBoard::create([
            'company_id' => $companyId,
            'created_by_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?: '#4F46E5',
            'is_default' => false,
        ]);

        // Default 4 Kanban columns
        $defaultColumns = [
            ['name' => 'Backlog / To Do', 'color' => 'slate', 'order' => 0],
            ['name' => 'In Progress', 'color' => 'amber', 'order' => 1],
            ['name' => 'In Review', 'color' => 'indigo', 'order' => 2],
            ['name' => 'Completed', 'color' => 'emerald', 'order' => 3],
        ];

        foreach ($defaultColumns as $col) {
            TaskColumn::create([
                'task_board_id' => $board->id,
                'company_id' => $companyId,
                'name' => $col['name'],
                'color' => $col['color'],
                'order' => $col['order'],
            ]);
        }

        $board->load('columns');

        return response()->json([
            'status' => 'success',
            'message' => 'Task board created successfully.',
            'board' => $board,
        ]);
    }

    /**
     * Delete a task board.
     */
    public function deleteBoard(TaskBoard $taskBoard)
    {
        $user = Auth::user();
        if ($taskBoard->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $taskBoard->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task board deleted successfully.',
        ]);
    }

    /**
     * Get tasks for a specific board.
     * Ticket visibility rule:
     * - Admins/Owners see all tasks for the company board.
     * - Employees/Regular Users see ONLY tickets assigned to them or created by them.
     */
    public function getTasks(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;
        $boardId = $request->query('board_id');

        if (!$boardId) {
            $firstBoard = TaskBoard::where('company_id', $companyId)->first();
            $boardId = $firstBoard ? $firstBoard->id : null;
        }

        if (!$boardId) {
            return response()->json(['status' => 'success', 'tasks' => []]);
        }

        $query = Task::with(['assignedTo', 'assignees', 'attachments', 'createdBy', 'column', 'comments'])
            ->where('company_id', $companyId)
            ->where('task_board_id', $boardId);

        // Check if user has owner or admin privileges
        $userRoles = array_map('strtolower', $user->roles->pluck('name')->toArray());
        $isCompanyOwner = false;
        $company = Company::find($companyId);
        if ($company && $company->user_id === $user->id) {
            $isCompanyOwner = true;
        }

        $isAdminOrOwner = $isCompanyOwner || in_array('admin', $userRoles) || in_array('owner', $userRoles) || in_array('super-admin', $userRoles);

        // If not Admin/Owner, restrict ticket visibility to assigned user or creator
        if (!$isAdminOrOwner) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to_id', $user->id)
                  ->orWhere('created_by_id', $user->id)
                  ->orWhereHas('assignees', function ($aq) use ($user) {
                      $aq->where('users.id', $user->id);
                  });
            });
        }

        $tasks = $query->orderBy('order', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'is_admin_view' => $isAdminOrOwner,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Create a new Task.
     */
    public function createTask(Request $request)
    {
        $request->validate([
            'task_board_id' => 'required|exists:task_boards,id',
            'task_column_id' => 'required|exists:task_columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $companyId = $user->current_company_id;

        // Parse assignee_ids (can be array or json string)
        $assigneeIds = $request->input('assignee_ids');
        if (is_string($assigneeIds)) {
            $assigneeIds = json_decode($assigneeIds, true);
        }
        if (!is_array($assigneeIds)) {
            $assigneeIds = $request->assigned_to_id ? [$request->assigned_to_id] : [];
        }

        // Parse tags
        $tags = $request->input('tags');
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }
        if (!is_array($tags)) {
            $tags = [];
        }

        $primaryAssigneeId = !empty($assigneeIds) ? $assigneeIds[0] : $request->assigned_to_id;

        $maxOrder = Task::where('task_column_id', $request->task_column_id)->max('order') ?? 0;

        $task = Task::create([
            'company_id' => $companyId,
            'task_board_id' => $request->task_board_id,
            'task_column_id' => $request->task_column_id,
            'created_by_id' => $user->id,
            'assigned_to_id' => $primaryAssigneeId,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'tags' => $tags,
            'order' => $maxOrder + 1,
        ]);

        // Sync multiple assignees
        if (!empty($assigneeIds)) {
            $task->assignees()->sync($assigneeIds);
        }

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('task_attachments', 'public');
                TaskAttachment::create([
                    'task_id' => $task->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by_id' => $user->id,
                ]);
            }
        }

        $task->load(['assignedTo', 'assignees', 'attachments', 'createdBy', 'column']);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully.',
            'task' => $task,
        ]);
    }

    /**
     * Update an existing Task.
     */
    public function updateTask(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($task->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'assigned_to_id' => 'nullable|exists:users,id',
            'task_column_id' => 'sometimes|required|exists:task_columns,id',
            'due_date' => 'nullable|date',
        ]);

        // Parse assignee_ids
        if ($request->has('assignee_ids')) {
            $assigneeIds = $request->input('assignee_ids');
            if (is_string($assigneeIds)) {
                $assigneeIds = json_decode($assigneeIds, true);
            }
            if (is_array($assigneeIds)) {
                $task->assignees()->sync($assigneeIds);
                if (!empty($assigneeIds)) {
                    $task->assigned_to_id = $assigneeIds[0];
                }
            }
        }

        // Parse tags
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            if (is_string($tags)) {
                $tags = json_decode($tags, true);
            }
            if (is_array($tags)) {
                $task->tags = $tags;
            }
        }

        $updateData = $request->only([
            'title',
            'description',
            'priority',
            'assigned_to_id',
            'task_column_id',
            'due_date',
        ]);

        $task->update($updateData);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('task_attachments', 'public');
                TaskAttachment::create([
                    'task_id' => $task->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by_id' => $user->id,
                ]);
            }
        }

        // Handle deleted attachments
        if ($request->has('deleted_attachment_ids')) {
            $deletedIds = $request->input('deleted_attachment_ids');
            if (is_string($deletedIds)) {
                $deletedIds = json_decode($deletedIds, true);
            }
            if (is_array($deletedIds) && !empty($deletedIds)) {
                $attachments = TaskAttachment::where('task_id', $task->id)->whereIn('id', $deletedIds)->get();
                foreach ($attachments as $att) {
                    Storage::disk('public')->delete($att->file_path);
                    $att->delete();
                }
            }
        }

        $task->load(['assignedTo', 'assignees', 'attachments', 'createdBy', 'column']);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully.',
            'task' => $task,
        ]);
    }

    /**
     * Move Task (Column & Order update).
     */
    public function moveTask(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($task->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'task_column_id' => 'required|exists:task_columns,id',
            'order' => 'nullable|integer',
        ]);

        $task->task_column_id = $request->task_column_id;
        if ($request->has('order')) {
            $task->order = $request->order;
        }

        $task->save();
        $task->load(['assignedTo', 'assignees', 'attachments', 'createdBy', 'column']);

        return response()->json([
            'status' => 'success',
            'message' => 'Task status moved successfully.',
            'task' => $task,
        ]);
    }

    /**
     * Delete a Task.
     */
    public function deleteTask(Task $task)
    {
        $user = Auth::user();
        if ($task->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        // Delete associated files
        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * Add a column to a task board.
     */
    public function createColumn(Request $request, TaskBoard $taskBoard)
    {
        $user = Auth::user();
        if ($taskBoard->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $maxOrder = TaskColumn::where('task_board_id', $taskBoard->id)->max('order') ?? 0;

        $column = TaskColumn::create([
            'task_board_id' => $taskBoard->id,
            'company_id' => $user->current_company_id,
            'name' => $request->name,
            'color' => $request->color ?: 'indigo',
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Column added successfully.',
            'column' => $column,
        ]);
    }

    /**
     * Get Assignees (Users & Employees belonging to current active company).
     */
    public function getAssignees(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        if (!$companyId) {
            return response()->json(['status' => 'success', 'assignees' => []]);
        }

        // Get company owner user
        $company = Company::find($companyId);
        $userIds = collect();

        if ($company && $company->user_id) {
            $userIds->push($company->user_id);
        }

        // Get users attached via company_user pivot
        if ($company) {
            $pivotUserIds = DB::table('company_user')
                ->where('company_id', $companyId)
                ->pluck('user_id');
            $userIds = $userIds->merge($pivotUserIds);
        }

        // Get users linked to employees in this company
        $employeeUserIds = Employee::where('company_id', $companyId)
            ->whereNotNull('user_id')
            ->pluck('user_id');
        $userIds = $userIds->merge($employeeUserIds);

        // Get users with matching current_company_id
        $directUserIds = User::where('current_company_id', $companyId)
            ->pluck('id');
        $userIds = $userIds->merge($directUserIds)->unique()->filter();

        $assignees = User::whereIn('id', $userIds)
            ->select('id', 'name', 'email', 'profile_image')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'avatar_url' => $u->profile_image ? (str_starts_with($u->profile_image, 'http') ? $u->profile_image : asset('storage/' . $u->profile_image)) : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'assignees' => $assignees,
        ]);
    }

    /**
     * Bulk action for selected tasks (Batch status, priority, assign, delete).
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:status,priority,assign,delete',
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'task_column_id' => 'nullable|exists:task_columns,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assignee_ids' => 'nullable|array',
        ]);

        $user = Auth::user();
        $companyId = $user->current_company_id;

        $tasks = Task::where('company_id', $companyId)
            ->whereIn('id', $request->task_ids)
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No matching tasks found.'], 400);
        }

        $action = $request->action;

        if ($action === 'status' && $request->task_column_id) {
            Task::whereIn('id', $tasks->pluck('id'))->update(['task_column_id' => $request->task_column_id]);
        } elseif ($action === 'priority' && $request->priority) {
            Task::whereIn('id', $tasks->pluck('id'))->update(['priority' => $request->priority]);
        } elseif ($action === 'assign') {
            $assigneeIds = $request->assignee_ids ?: [];
            $primaryAssignee = !empty($assigneeIds) ? $assigneeIds[0] : null;

            foreach ($tasks as $task) {
                $task->assigned_to_id = $primaryAssignee;
                $task->save();
                $task->assignees()->sync($assigneeIds);
            }
        } elseif ($action === 'delete') {
            foreach ($tasks as $task) {
                foreach ($task->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $task->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Bulk action performed successfully.',
        ]);
    }

    /**
     * Toggle Starred Status for a Task.
     */
    public function toggleStar(Task $task)
    {
        $user = Auth::user();
        if ($task->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $task->is_starred = !$task->is_starred;
        $task->save();

        return response()->json([
            'status' => 'success',
            'is_starred' => $task->is_starred,
            'message' => $task->is_starred ? 'Task starred.' : 'Task unstarred.',
        ]);
    }

    /**
     * Add a comment to a Task.
     */
    public function addComment(Request $request, Task $task)
    {
        $user = Auth::user();
        if ($task->company_id !== $user->current_company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        $comment->load('user');

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully.',
            'comment' => $comment,
        ]);
    }
}
