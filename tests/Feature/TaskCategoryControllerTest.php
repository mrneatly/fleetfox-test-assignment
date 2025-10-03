<?php

use App\Models\TaskCategory;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('index displays task categories page', function () {
    $response = $this->get(route('task-categories.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('task-categories/Index'));
});

test('index filters categories by search query', function () {
    TaskCategory::factory()->create(['name' => 'Personal']);
    TaskCategory::factory()->create(['name' => 'Work']);

    $response = $this->get(route('task-categories.index', ['search' => 'Personal']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('task-categories/Index')
            ->where('categories.data.0.name', 'Personal')
            ->where('categories.total', 1)
    );
});

test('create displays category creation page', function () {
    $response = $this->get(route('task-categories.create'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('task-categories/Create'));
});

test('store creates a new category', function () {
    $data = [
        'name' => 'New Category',
    ];

    $response = $this->post(route('task-categories.store'), $data);

    $response->assertRedirect(route('task-categories.index'));
    $this->assertDatabaseHas('task_categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});

test('store auto generates slug from name', function () {
    $data = [
        'name' => 'My Test Category',
    ];

    $this->post(route('task-categories.store'), $data);

    $this->assertDatabaseHas('task_categories', [
        'name' => 'My Test Category',
        'slug' => 'my-test-category',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('task-categories.store'), []);

    $response->assertSessionHasErrors(['name']);
});

test('store validates unique slug', function () {
    TaskCategory::factory()->create(['name' => 'Test', 'slug' => 'test']);

    $response = $this->post(route('task-categories.store'), ['name' => 'Test']);

    $response->assertSessionHasErrors(['slug']);
});

test('edit displays category edit page', function () {
    $category = TaskCategory::factory()->create();

    $response = $this->get(route('task-categories.edit', $category));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('task-categories/Edit')
            ->where('category.id', $category->id)
    );
});

test('update modifies existing category', function () {
    $category = TaskCategory::factory()->create(['name' => 'Original Name']);
    $data = [
        'name' => 'Updated Name',
    ];

    $response = $this->put(route('task-categories.update', $category), $data);

    $response->assertRedirect(route('task-categories.index'));
    $this->assertDatabaseHas('task_categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
    ]);
});

test('update regenerates slug from name', function () {
    $category = TaskCategory::factory()->create(['name' => 'Old', 'slug' => 'old']);
    $data = [
        'name' => 'New Name Here',
    ];

    $this->put(route('task-categories.update', $category), $data);

    $this->assertDatabaseHas('task_categories', [
        'id' => $category->id,
        'slug' => 'new-name-here',
    ]);
});

test('update validates required fields', function () {
    $category = TaskCategory::factory()->create();

    $response = $this->put(route('task-categories.update', $category), ['name' => '']);

    $response->assertSessionHasErrors(['name']);
});

test('update validates unique slug except for current category', function () {
    $category1 = TaskCategory::factory()->create(['name' => 'First', 'slug' => 'first']);
    $category2 = TaskCategory::factory()->create(['name' => 'Second', 'slug' => 'second']);

    $response = $this->put(route('task-categories.update', $category2), ['name' => 'First']);

    $response->assertSessionHasErrors(['slug']);
});

test('update allows same category to keep its slug', function () {
    $category = TaskCategory::factory()->create(['name' => 'Test', 'slug' => 'test']);

    $response = $this->put(route('task-categories.update', $category), ['name' => 'Test']);

    $response->assertRedirect(route('task-categories.index'));
    $response->assertSessionHasNoErrors();
});

test('destroy deletes category', function () {
    $category = TaskCategory::factory()->create();

    $response = $this->delete(route('task-categories.destroy', $category));

    $response->assertRedirect(route('task-categories.index'));
    $this->assertDatabaseMissing('task_categories', ['id' => $category->id]);
});

test('guest cannot access task categories', function () {
    auth()->logout();

    $this->get(route('task-categories.index'))->assertRedirect(route('login'));
    $this->get(route('task-categories.create'))->assertRedirect(route('login'));
    $this->post(route('task-categories.store'), [])->assertRedirect(route('login'));
});
