<?php

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('index displays tasks page', function () {
    $response = $this->get(route('tasks.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('tasks/Index'));
});

test('index filters tasks by search query', function () {
    Task::factory()->create(['title' => 'Buy groceries']);
    Task::factory()->create(['title' => 'Schedule meeting']);

    $response = $this->get(route('tasks.index', ['search' => 'groceries']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('tasks/Index')
            ->where('tasks.data.0.title', 'Buy groceries')
            ->where('tasks.total', 1)
    );
});

test('index filters tasks by category', function () {
    $category = TaskCategory::factory()->create();
    $task = Task::factory()->create(['category_id' => $category->id]);
    Task::factory()->create(['category_id' => null]);

    $response = $this->get(route('tasks.index', ['category_id' => $category->id]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('tasks/Index')
            ->where('tasks.total', 1)
            ->where('tasks.data.0.id', $task->id)
    );
});

test('index filters tasks by status open', function () {
    Task::factory()->create(['done_at' => null]);
    Task::factory()->create(['done_at' => now()]);

    $response = $this->get(route('tasks.index', ['status' => 'open']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('tasks/Index')
            ->where('tasks.total', 1)
    );
});

test('index filters tasks by status done', function () {
    Task::factory()->create(['done_at' => null]);
    Task::factory()->create(['done_at' => now()]);

    $response = $this->get(route('tasks.index', ['status' => 'done']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('tasks/Index')
            ->where('tasks.total', 1)
    );
});

test('create displays task creation page', function () {
    $response = $this->get(route('tasks.create'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('tasks/Create'));
});

test('store creates a new task', function () {
    $category = TaskCategory::factory()->create();
    $data = [
        'category_id' => $category->id,
        'title' => 'New Task',
        'text' => 'Task description',
        'due_date' => now()->addDays(3)->toDateString(),
    ];

    $response = $this->post(route('tasks.store'), $data);

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseHas('tasks', [
        'category_id' => $category->id,
        'title' => 'New Task',
        'text' => 'Task description',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('tasks.store'), []);

    $response->assertSessionHasErrors(['title']);
});

test('store creates task with null done_at by default', function () {
    $data = [
        'title' => 'Test Task',
    ];

    $this->post(route('tasks.store'), $data);

    $task = Task::where('title', 'Test Task')->first();
    expect($task->done_at)->toBeNull();
});

test('edit displays task edit page', function () {
    $task = Task::factory()->create();

    $response = $this->get(route('tasks.edit', $task));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('tasks/Edit')
            ->where('task.id', $task->id)
    );
});

test('update modifies existing task', function () {
    $task = Task::factory()->create(['title' => 'Original Title']);
    $data = [
        'title' => 'Updated Title',
        'text' => 'Updated description',
    ];

    $response = $this->put(route('tasks.update', $task), $data);

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
        'text' => 'Updated description',
    ]);
});

test('update can mark task as done', function () {
    $task = Task::factory()->create(['done_at' => null]);
    $data = [
        'title' => $task->title,
        'done' => true,
    ];

    $this->put(route('tasks.update', $task), $data);

    $task->refresh();
    expect($task->done_at)->not->toBeNull();
});

test('update can mark task as not done', function () {
    $task = Task::factory()->create(['done_at' => now()]);
    $data = [
        'title' => $task->title,
        'done' => false,
    ];

    $this->put(route('tasks.update', $task), $data);

    $task->refresh();
    expect($task->done_at)->toBeNull();
});

test('update validates required fields', function () {
    $task = Task::factory()->create();

    $response = $this->put(route('tasks.update', $task), ['title' => '']);

    $response->assertSessionHasErrors(['title']);
});

test('destroy deletes task', function () {
    $task = Task::factory()->create();

    $response = $this->delete(route('tasks.destroy', $task));

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('setDone toggles task done status', function () {
    $task = Task::factory()->create(['done_at' => null]);

    $response = $this->patch(route('tasks.done', $task));

    $response->assertRedirect();
    $task->refresh();
    expect($task->done_at)->not->toBeNull();
});

test('setDone can set task as not done', function () {
    $task = Task::factory()->create(['done_at' => now()]);

    $this->patch(route('tasks.done', $task));

    $task->refresh();
    expect($task->done_at)->toBeNull();
});

test('setDone accepts explicit done parameter', function () {
    $task = Task::factory()->create(['done_at' => null]);

    $this->patch(route('tasks.done', $task), ['done' => true]);

    $task->refresh();
    expect($task->done_at)->not->toBeNull();
});

test('guest cannot access tasks', function () {
    auth()->logout();

    $this->get(route('tasks.index'))->assertRedirect(route('login'));
    $this->get(route('tasks.create'))->assertRedirect(route('login'));
    $this->post(route('tasks.store'), [])->assertRedirect(route('login'));
});