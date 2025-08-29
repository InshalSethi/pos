# DataTable Component

A flexible, reusable DataTable component with sorting, searching, pagination, and memory functionality.

## Features

- ✅ **Sortable columns** - Click column headers to sort
- ✅ **Global search** - Search across all data
- ✅ **Pagination** - Navigate through large datasets
- ✅ **Memory function** - Remembers state after page refresh
- ✅ **Responsive design** - Works on all screen sizes
- ✅ **Customizable columns** - Support for different data types
- ✅ **Custom slots** - Override column rendering
- ✅ **Loading states** - Built-in loading and empty states
- ✅ **Flexible styling** - Easy to customize appearance

## Basic Usage

```vue
<template>
  <DataTable
    title="My Data"
    subtitle="Manage your data efficiently"
    :columns="columns"
    :data="data"
    :loading="loading"
    :pagination="pagination"
    storage-key="my-table-state"
    @search="handleSearch"
    @sort="handleSort"
    @page-change="handlePageChange"
    @per-page-change="handlePerPageChange"
  />
</template>

<script setup>
import DataTable from '@/components/common/DataTable.vue';

const columns = [
  {
    key: 'id',
    label: 'ID',
    sortable: true,
    align: 'left'
  },
  {
    key: 'name',
    label: 'Name',
    sortable: true,
    align: 'left'
  },
  {
    key: 'amount',
    label: 'Amount',
    sortable: true,
    type: 'currency',
    align: 'right'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false,
    type: 'badge',
    align: 'center',
    badgeColors: {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-red-100 text-red-800'
    }
  }
];
</script>
```

## Column Configuration

### Column Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `key` | String | Required | Data property key |
| `label` | String | Required | Column header text |
| `sortable` | Boolean | `false` | Enable sorting |
| `align` | String | `'left'` | Text alignment (`left`, `center`, `right`) |
| `type` | String | `'text'` | Data type (`text`, `currency`, `date`, `badge`) |
| `class` | String | `'text-gray-900'` | CSS classes for cell content |
| `badgeColors` | Object | `{}` | Color mapping for badge type |
| `badgeLabels` | Object | `{}` | Label mapping for badge type |

### Column Types

#### Text (default)
```javascript
{
  key: 'name',
  label: 'Name',
  type: 'text' // or omit type
}
```

#### Currency
```javascript
{
  key: 'amount',
  label: 'Amount',
  type: 'currency',
  align: 'right'
}
```

#### Date
```javascript
{
  key: 'created_at',
  label: 'Created',
  type: 'date'
}
```

#### Badge
```javascript
{
  key: 'status',
  label: 'Status',
  type: 'badge',
  badgeColors: {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    inactive: 'bg-red-100 text-red-800'
  },
  badgeLabels: {
    active: 'Active',
    pending: 'Pending',
    inactive: 'Inactive'
  }
}
```

## Custom Column Content

Use slots to customize column rendering:

```vue
<DataTable :columns="columns" :data="data">
  <!-- Custom content for specific column -->
  <template #column-actions="{ item, index }">
    <div class="flex space-x-2">
      <button @click="edit(item)" class="text-blue-600 hover:text-blue-900">
        Edit
      </button>
      <button @click="delete(item)" class="text-red-600 hover:text-red-900">
        Delete
      </button>
    </div>
  </template>
  
  <!-- Custom status column with complex logic -->
  <template #column-status="{ item }">
    <span :class="getStatusClass(item.status)">
      {{ getStatusText(item.status) }}
    </span>
  </template>
</DataTable>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | `''` | Table title |
| `subtitle` | String | `''` | Table subtitle |
| `columns` | Array | Required | Column configuration |
| `data` | Array | `[]` | Table data |
| `loading` | Boolean | `false` | Loading state |
| `pagination` | Object | `null` | Pagination data |
| `emptyMessage` | String | `'No data found'` | Empty state message |
| `emptySubMessage` | String | `'Try adjusting...'` | Empty state sub-message |
| `storageKey` | String | `'datatable-state'` | LocalStorage key for state |
| `perPageOptions` | Array | `[10, 25, 50, 100]` | Items per page options |
| `defaultPerPage` | Number | `25` | Default items per page |
| `defaultSort` | Object | `{ field: null, order: 'asc' }` | Default sort |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `search` | `String` | Search query changed |
| `sort` | `{ field: String, order: String }` | Sort changed |
| `page-change` | `Number` | Page changed |
| `per-page-change` | `Number` | Items per page changed |

## Memory Function

The DataTable automatically saves and restores:
- Search query
- Sort field and order
- Items per page
- Current page (optional)

State is saved to localStorage with the provided `storage-key` and expires after 24 hours.

## Action Buttons

Add custom action buttons using the `actions` slot:

```vue
<DataTable :columns="columns" :data="data">
  <template #actions>
    <button class="bg-green-600 text-white px-4 py-2 rounded-lg">
      Add New Item
    </button>
  </template>
</DataTable>
```

## Backend Integration

Your backend should support these parameters:

```php
// Laravel Controller Example
public function index(Request $request)
{
    $query = Model::query();
    
    // Search
    if ($request->has('search')) {
        $query->where('name', 'like', "%{$request->search}%");
    }
    
    // Sorting
    if ($request->has('sort_field')) {
        $query->orderBy($request->sort_field, $request->sort_order ?? 'asc');
    }
    
    // Pagination
    $perPage = $request->get('per_page', 25);
    return $query->paginate($perPage);
}
```

## Styling

The component uses Tailwind CSS classes and follows the design system shown in the provided image. You can customize colors by modifying the component or extending with CSS.
