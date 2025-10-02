<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $categoryId = $request->integer('category_id');
        $status = $request->string('status')->toString();

        $query = Task::query()
            ->with('category')
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $w) use ($search) {
                    $w->where('title', 'like', "%{$search}%")
                        ->orWhere('text', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn (Builder $q) => $q->where('category_id', $categoryId))
            ->when($status === 'open', fn (Builder $q) => $q->whereNull('done_at'))
            ->when($status === 'done', fn (Builder $q) => $q->whereNotNull('done_at'))
            ->orderByDesc('created_at');

        $tasks = $query->paginate(10)->withQueryString();

        $categories = TaskCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('tasks/Index', [
            'tasks' => $tasks,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId ?: null,
                'status' => $status ?: null,
            ],
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        $categories = TaskCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('tasks/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', Rule::exists('task_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'done_at' => ['nullable', 'date'],
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created.');
    }

    public function edit(Task $task): Response
    {
        $categories = TaskCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('tasks/Edit', [
            'task' => $task,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', Rule::exists('task_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'done_at' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted.');
    }
}
