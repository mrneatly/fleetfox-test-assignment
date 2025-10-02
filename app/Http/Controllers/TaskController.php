<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    }

    public function store(Request $request): RedirectResponse
    {

    }

    public function edit(Task $task): Response
    {

    }

    public function update(Request $request, Task $task): RedirectResponse
    {

    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted.');
    }
}
