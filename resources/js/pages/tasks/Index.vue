<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { index, create, edit, destroy, setDone } from '@/actions/App/Http/Controllers/TaskController';
import { AlertTriangle, CheckCircle, CalendarDays, Tag, Pencil, Trash2, Check, Undo2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface TaskCategoryOption { id: number; name: string }

interface TaskItem {
  id: number
  category_id: number | null
  title: string
  text?: string | null
  due_date?: string | null
  done_at?: string | null
  created_at?: string
  updated_at?: string
  category?: { id: number; name: string } | null
}

interface Props {
  tasks: {
    data: TaskItem[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
  }
  filters: {
    search?: string
    category_id?: number | null
    status?: 'open' | 'done' | null
  }
  categories: TaskCategoryOption[]
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tasks', href: index.url() },
];

function onSearch(e: Event) {
  const value = (e.target as HTMLInputElement).value;
  router.get(index.url(), { ...props.filters, search: value }, { preserveScroll: true, preserveState: true, replace: true });
}

function onFilterChange(e: Event) {
  const form = e.target as HTMLSelectElement;
  const name = form.name as 'category_id' | 'status';
  const value = form.value || null;
  router.get(index.url(), { ...props.filters, [name]: value }, { preserveScroll: true, preserveState: true, replace: true });
}

function destroyTask(id: number) {
  if (!confirm('Are you sure you want to delete this task?')) return;
  router.delete(destroy.url(id), { preserveScroll: true });
}

const expanded = ref<Record<number, boolean>>({});
function toggleExpand(id: number) {
  expanded.value[id] = !expanded.value[id];
}

function isDone(t: TaskItem) {
  if (!t.done_at) return false;
  const done = new Date(t.done_at).getTime();
  const now = Date.now();
  return done <= now; // done in the past
}

function isDueSoon(t: TaskItem) {
  if (!t.due_date) return false;
  const due = new Date(t.due_date).getTime();
  const now = Date.now();
  const threeDays = 3 * 24 * 60 * 60 * 1000;
  return due >= now && due <= now + threeDays;
}

function cardClasses(t: TaskItem) {
  if (isDone(t)) return 'bg-green-50 border-green-200';
  if (isDueSoon(t)) return 'bg-yellow-50 border-yellow-200';
  return 'bg-background';
}

function toggleDone(t: TaskItem) {
  router.patch(
    setDone.url(t.id, { mergeQuery: {} }),
    {},
    { preserveScroll: true, preserveState: true, replace: true }
  );
}
</script>

<template>
  <Head title="Tasks" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex justify-between gap-2 p-4 flex-wrap">
      <div class="md:flex md:items-start md:gap-2 w-full md:w-auto">
        <div class="grid gap-2 w-full md:max-w-sm">
          <Label for="search">Search</Label>
          <Input id="search" name="search" :default-value="filters.search" placeholder="Search title or text" @input="onSearch" />
        </div>
        <div class="grid gap-2 md:w-48">
          <Label for="category_id">Category</Label>
          <select id="category_id" name="category_id" class="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" @change="onFilterChange">
            <option :value="''" :selected="!filters.category_id">All</option>
            <option v-for="c in categories" :key="c.id" :value="c.id" :selected="filters.category_id === c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="grid gap-2 md:w-40">
          <Label for="status">Status</Label>
          <select id="status" name="status" class="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" @change="onFilterChange">
            <option :value="''" :selected="!filters.status">All</option>
            <option value="open" :selected="filters.status === 'open'">Open</option>
            <option value="done" :selected="filters.status === 'done'">Done</option>
          </select>
        </div>
      </div>
      <div class="flex items-end gap-2 w-full md:w-auto">
        <div class="ml-auto">
          <Link :href="create.url()" as="button">
            <Button>Add task</Button>
          </Link>
        </div>
      </div>
    </div>

    <div class="p-4">
      <div class="space-y-3">
        <div
          v-for="t in tasks.data"
          :key="t.id"
          class="rounded-lg border p-4"
          :class="cardClasses(t)"
        >
          <div class="flex items-start gap-3">
            <div class="pt-1">
              <CheckCircle v-if="isDone(t)" class="text-green-600" :size="20" />
              <AlertTriangle v-else-if="isDueSoon(t)" class="text-yellow-600" :size="20" />
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <h3 class="text-base font-semibold">{{ t.title }}</h3>
                <span class="text-xs text-muted-foreground">#{{ t.id }}</span>
              </div>
              <div v-if="t.text" class="mt-1 text-sm text-muted-foreground">
                <template v-if="expanded[t.id]">
                  <span>{{ t.text }}</span>
                  <button type="button" class="ml-2 text-primary hover:underline" @click="toggleExpand(t.id)">collapse</button>
                </template>
                <template v-else>
                  <span>{{ t.text.length > 200 ? t.text.slice(0, 200) + '…' : t.text }}</span>
                  <button v-if="t.text.length > 200" type="button" class="ml-2 text-primary hover:underline" @click="toggleExpand(t.id)">expand</button>
                </template>
              </div>

              <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                <div class="inline-flex items-center gap-1 text-muted-foreground">
                  <Tag :size="16" />
                  <span>{{ t.category?.name ?? 'No category' }}</span>
                </div>
                <div class="inline-flex items-center gap-1 text-muted-foreground">
                  <CalendarDays :size="16" />
                  <span>Due: {{ t.due_date ? new Date(t.due_date).toLocaleString() : '-' }}</span>
                </div>
              </div>
            </div>
            <div class="ml-auto flex items-start gap-2">
              <Button variant="outline" size="icon" class="h-8 w-8" @click="toggleDone(t)" :title="isDone(t) ? 'Mark as undone' : 'Mark as done'">
                <Undo2 v-if="isDone(t)" :size="16" />
                <Check v-else :size="16" />
              </Button>
              <Link :href="edit.url(t.id)" as="button">
                <Button variant="secondary" size="icon" class="h-8 w-8" title="Edit">
                  <Pencil :size="16" />
                </Button>
              </Link>
              <Button variant="destructive" size="icon" class="h-8 w-8" title="Delete" @click="destroyTask(t.id)">
                <Trash2 :size="16" />
              </Button>
            </div>
          </div>
        </div>

        <div v-if="tasks.data.length === 0" class="rounded-lg border bg-background p-6 text-center text-muted-foreground">
          No tasks found.
        </div>
      </div>

      <nav v-if="tasks.links?.length" class="mt-4 flex flex-wrap items-center gap-2">
        <Link v-for="(link, idx) in tasks.links" :key="idx" :href="link.url || '#'" :class="['px-3 py-1 rounded border', link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background hover:bg-muted']" preserve-scroll>
          <span v-html="link.label" />
        </Link>
      </nav>
    </div>
  </AppLayout>
</template>
