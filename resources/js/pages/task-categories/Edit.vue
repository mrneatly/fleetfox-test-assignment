<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { index, edit, update, destroy } from '@/actions/App/Http/Controllers/TaskCategoryController';
import { watch } from 'vue';

interface Category {
  id: number
  slug: string
  name: string
  icon_name?: string | null
}

const props = defineProps<{ category: Category }>();

const form = useForm({
  name: props.category.name,
  slug: props.category.slug,
  icon_name: (props.category.icon_name ?? null) as string | null,
});

function slugify(input: string) {
  // Mimic Laravel's Str::slug() helper
  return input
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9\-]/g, '')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

watch(() => form.name, (val) => {
  form.slug = slugify(val || '');
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Task categories', href: index.url() },
  { title: 'Edit', href: edit.url(props.category.id) },
];

function submit() {
  form.put(update.url(props.category.id));
}

function destroyCategory() {
  if (!confirm('Are you sure you want to delete this category?')) return;
  form.delete(destroy.url(props.category.id));
}
</script>

<template>
  <Head title="Edit task category" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 max-w-xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
          <Label for="name">Name</Label>
          <Input id="name" v-model="form.name" required />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
          <Label for="slug">Slug</Label>
          <Input id="slug" v-model="form.slug" required disabled readonly />
          <InputError class="mt-2" :message="form.errors.slug" />
        </div>

        <div class="grid gap-2">
          <Label for="icon_name">Icon</Label>
          <select
            id="icon_name"
            v-model="form.icon_name"
            class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option :value="''">No icon</option>
            <option value="house">house</option>
            <option value="user">user</option>
            <option value="settings">settings</option>
            <option value="bell">bell</option>
            <option value="star">star</option>
            <option value="tag">tag</option>
            <option value="folder">folder</option>
            <option value="bookmark">bookmark</option>
            <option value="heart">heart</option>
            <option value="chart-line">chart-line</option>
          </select>
          <InputError class="mt-2" :message="form.errors.icon_name" />
        </div>

        <div class="flex items-center gap-2">
          <Button type="submit" :disabled="form.processing">Save</Button>
          <Link :href="index.url()" as="button">
            <Button type="button" variant="secondary">Cancel</Button>
          </Link>
          <div class="ml-auto">
            <Button type="button" variant="destructive" :disabled="form.processing" @click="destroyCategory">Delete</Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
