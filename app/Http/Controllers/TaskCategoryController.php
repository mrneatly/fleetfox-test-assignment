<?php

namespace App\Http\Controllers;

use App\Enums\TaskCategoryIcon;
use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Inertia\Inertia;
use Inertia\Response;

class TaskCategoryController
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $query = TaskCategory::query()
            ->when($search, function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $categories = $query->paginate(10)->withQueryString();

        return Inertia::render('task-categories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('task-categories/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Always derive slug from the provided name to prevent manual input
        $request->merge([
            'slug' => Str::slug((string) $request->string('name')),
        ]);

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:task_categories,slug'],
            'name' => ['required', 'string', 'max:255'],
            'icon_name' => ['nullable', 'string', 'max:255', new EnumRule(TaskCategoryIcon::class)],
        ]);

        TaskCategory::create($validated);

        return redirect()->route('task-categories.index')
            ->with('success', 'Task category created.');
    }

    public function edit(TaskCategory $taskCategory): Response
    {
        return Inertia::render('task-categories/Edit', [
            'category' => $taskCategory,
        ]);
    }

    public function update(Request $request, TaskCategory $taskCategory): RedirectResponse
    {
        // Regenerate slug from name to prevent manual input
        $request->merge([
            'slug' => Str::slug((string) $request->string('name')),
        ]);

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:task_categories,slug,' . $taskCategory->id],
            'name' => ['required', 'string', 'max:255'],
            'icon_name' => ['nullable', 'string', 'max:255', new EnumRule(TaskCategoryIcon::class)],
        ]);

        $taskCategory->update($validated);

        return redirect()->route('task-categories.index')
            ->with('success', 'Task category updated.');
    }

    public function destroy(TaskCategory $taskCategory): RedirectResponse
    {
        $taskCategory->delete();

        return redirect()->route('task-categories.index')
            ->with('success', 'Task category deleted.');
    }
}
