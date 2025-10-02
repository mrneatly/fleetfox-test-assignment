<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { index, create, edit, destroy } from '@/actions/App/Http/Controllers/TaskCategoryController';
import { House, User, Settings, Bell, Star, Tag, Folder, Bookmark, Heart, LineChart } from 'lucide-vue-next';

interface Category {
  id: number
  slug: string
  name: string
  icon_name?: string | null
  created_at?: string
  updated_at?: string
}

interface Props {
  categories: {
    data: Category[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
  }
  filters: {
    search?: string
  }
}

defineProps<Props>();

const iconMap = {
  house: House,
  user: User,
  settings: Settings,
  bell: Bell,
  star: Star,
  tag: Tag,
  folder: Folder,
  bookmark: Bookmark,
  heart: Heart,
  'chart-line': LineChart,
} as const;

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Task categories', href: index.url() },
];

function onSearch(e: Event) {
  const value = (e.target as HTMLInputElement).value;
  router.get(index.url(), { search: value }, { preserveScroll: true, preserveState: true, replace: true });
}

function destroyCategory(id: number) {
  if (!confirm('Are you sure you want to delete this category?')) return;
  router.delete(destroy.url(id), { preserveScroll: true });
}
</script>

<template>
  <Head title="Task categories" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex items-end justify-between gap-2 p-4">
      <div class="grid gap-2 w-full max-w-sm">
        <Label for="search">Search</Label>
        <Input id="search" name="search" :default-value="filters.search" placeholder="Search by name" @input="onSearch" />
      </div>
      <div>
        <Link :href="create.url()" as="button">
          <Button>Add category</Button>
        </Link>
      </div>
    </div>

    <div class="p-4">
      <div class="overflow-x-auto rounded-lg border">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-muted/40 text-muted-foreground">
            <tr>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Slug</th>
              <th class="px-4 py-3">Icon</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cat in categories.data" :key="cat.id" class="border-t">
              <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
              <td class="px-4 py-3">{{ cat.slug }}</td>
              <td class="px-4 py-3">
                <component v-if="cat.icon_name && iconMap[cat.icon_name as keyof typeof iconMap]" :is="iconMap[cat.icon_name as keyof typeof iconMap]" :size="18" class="text-muted-foreground" />
                <span v-else>-</span>
              </td>
              <td class="px-4 py-3 text-right space-x-2">
                <Link :href="edit.url(cat.id)" as="button">
                  <Button variant="secondary" size="sm">Edit</Button>
                </Link>
                <Button variant="destructive" size="sm" @click="destroyCategory(cat.id)">Delete</Button>
              </td>
            </tr>
            <tr v-if="categories.data.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-muted-foreground">No categories found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <nav v-if="categories.links?.length" class="mt-4 flex flex-wrap items-center gap-2">
        <Link v-for="(link, idx) in categories.links" :key="idx" :href="link.url || '#'" :class="['px-3 py-1 rounded border', link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background hover:bg-muted']" preserve-scroll>
          <span v-html="link.label" />
        </Link>
      </nav>
    </div>
  </AppLayout>
</template>
