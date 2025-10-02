<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { index, edit, update, destroy } from '@/actions/App/Http/Controllers/TaskController';

interface TaskCategoryOption { id: number; name: string }

interface TaskItem {
  id: number
  category_id: number | null
  title: string
  text?: string | null
  due_date?: string | null
  done_at?: string | null
}

const props = defineProps<{ task: TaskItem; categories: TaskCategoryOption[] }>();

const form = useForm({
  category_id: props.task.category_id ?? '' as number | '' | null,
  title: props.task.title,
  text: (props.task.text ?? '') as string | null,
  due_date: (props.task.due_date ? props.task.due_date.slice(0, 16) : '') as string | null,
  done: !!props.task.done_at,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tasks', href: index.url() },
  { title: 'Edit', href: edit.url(props.task.id) },
];

function submit() {
  const payload = {
    ...form.data(),
    category_id: form.category_id === '' ? null : form.category_id,
    text: form.text === '' ? null : form.text,
    due_date: form.due_date === '' ? null : form.due_date,
    done: !!form.done,
  } as Record<string, unknown>;
  form.put(update.url(props.task.id), { preserveScroll: true, data: payload });
}

function destroyTask() {
  if (!confirm('Are you sure you want to delete this task?')) return;
  form.delete(destroy.url(props.task.id));
}
</script>

<template>
  <Head title="Edit task" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 max-w-2xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
          <Label for="title">Title</Label>
          <Input id="title" v-model="form.title" required />
          <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div class="grid gap-2">
          <Label for="category_id">Category</Label>
          <select
            id="category_id"
            v-model="form.category_id"
            class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          >
            <option :value="''">No category</option>
            <option v-for="c in props.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <InputError class="mt-2" :message="form.errors.category_id as any" />
        </div>

        <div class="grid gap-2">
          <Label for="text">Text</Label>
          <textarea id="text" v-model="form.text" rows="5" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"></textarea>
          <InputError class="mt-2" :message="form.errors.text" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="grid gap-2">
            <Label for="due_date">Due date</Label>
            <input id="due_date" type="datetime-local" v-model="form.due_date" class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
            <InputError class="mt-2" :message="form.errors.due_date" />
          </div>
          <div class="grid gap-2">
            <Label for="done">Done</Label>
            <div class="flex items-center gap-2 mt-1">
              <input id="done" type="checkbox" v-model="form.done" class="h-4 w-4 rounded border-input" />
              <span class="text-sm text-muted-foreground">Mark as done</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Button type="submit" :disabled="form.processing">Save</Button>
          <Link :href="index.url()" as="button">
            <Button type="button" variant="secondary">Cancel</Button>
          </Link>
          <div class="ml-auto">
            <Button type="button" variant="destructive" :disabled="form.processing" @click="destroyTask">Delete</Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
