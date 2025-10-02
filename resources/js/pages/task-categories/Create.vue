<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { index, create, store } from '@/actions/App/Http/Controllers/TaskCategoryController';
import { watch } from 'vue';

const form = useForm({
  name: '',
  slug: '',
  icon_name: '' as string | null,
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
  { title: 'Create', href: create.url() },
];

function submit() {
  form.post(store.url());
}
</script>

<template>
  <Head title="Create task category" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 max-w-xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
          <Label for="name">Name</Label>
          <Input id="name" v-model="form.name" required placeholder="e.g. Maintenance" />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
          <Label for="slug">Slug</Label>
          <Input id="slug" v-model="form.slug" required placeholder="maintenance" disabled readonly />
          <InputError class="mt-2" :message="form.errors.slug" />
        </div>

        <div class="grid gap-2">
          <Label for="icon_name">Icon name</Label>
          <Input id="icon_name" v-model="form.icon_name" placeholder="optional icon name" />
          <InputError class="mt-2" :message="form.errors.icon_name" />
        </div>

        <div class="flex items-center gap-2">
          <Button type="submit" :disabled="form.processing">Create</Button>
          <Link :href="index.url()" as="button">
            <Button type="button" variant="secondary">Cancel</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
