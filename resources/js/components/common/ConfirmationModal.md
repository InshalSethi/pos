# ConfirmationModal Component

A reusable confirmation modal component that can be used throughout the application for confirming destructive or important actions.

## Features

- **Multiple Types**: Supports danger, warning, info, and default styles
- **Customizable Content**: Title, message, and additional details
- **Loading States**: Shows loading spinner during async operations
- **Confirmation Text**: Optional requirement to type specific text to confirm
- **Keyboard Support**: ESC key to cancel, Enter to confirm
- **Backdrop Click**: Optional close on backdrop click
- **Responsive Design**: Works on all screen sizes

## Basic Usage

```vue
<template>
  <div>
    <button @click="showConfirmation = true">Delete Item</button>
    
    <ConfirmationModal
      v-model:show="showConfirmation"
      title="Delete Item"
      message="Are you sure you want to delete this item?"
      type="danger"
      confirm-text="Delete"
      @confirm="handleDelete"
      @cancel="showConfirmation = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ConfirmationModal from '@/components/common/ConfirmationModal.vue';

const showConfirmation = ref(false);

const handleDelete = async () => {
  // Perform delete operation
  console.log('Item deleted');
  showConfirmation.value = false;
};
</script>
```

## Advanced Usage with Loading State

```vue
<template>
  <ConfirmationModal
    v-model:show="showDeleteConfirmation"
    :title="deleteData.title"
    :message="deleteData.message"
    :details="deleteData.details"
    type="danger"
    confirm-text="Delete Permanently"
    cancel-text="Cancel"
    :loading="deleteData.loading"
    loading-text="Deleting..."
    @confirm="confirmDelete"
    @cancel="cancelDelete"
  />
</template>

<script setup>
import { ref } from 'vue';
import ConfirmationModal from '@/components/common/ConfirmationModal.vue';

const showDeleteConfirmation = ref(false);
const deleteData = ref({
  title: '',
  message: '',
  details: '',
  loading: false,
  itemToDelete: null
});

const showDeleteModal = (item) => {
  deleteData.value = {
    title: 'Delete Item',
    message: `Are you sure you want to delete "<strong>${item.name}</strong>"?`,
    details: `This action cannot be undone. All associated data will be permanently removed.`,
    loading: false,
    itemToDelete: item
  };
  showDeleteConfirmation.value = true;
};

const confirmDelete = async () => {
  deleteData.value.loading = true;
  
  try {
    await deleteItem(deleteData.value.itemToDelete.id);
    showDeleteConfirmation.value = false;
    // Show success message
  } catch (error) {
    // Handle error
  } finally {
    deleteData.value.loading = false;
  }
};

const cancelDelete = () => {
  deleteData.value = {
    title: '',
    message: '',
    details: '',
    loading: false,
    itemToDelete: null
  };
  showDeleteConfirmation.value = false;
};
</script>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `show` | Boolean | `false` | Controls modal visibility |
| `title` | String | `'Confirm Action'` | Modal title |
| `message` | String | `'Are you sure you want to proceed?'` | Main message (supports HTML) |
| `details` | String | `''` | Additional details (supports HTML) |
| `type` | String | `'danger'` | Modal type: `danger`, `warning`, `info`, `default` |
| `confirmText` | String | `'Confirm'` | Confirm button text |
| `cancelText` | String | `'Cancel'` | Cancel button text |
| `loadingText` | String | `'Processing...'` | Loading button text |
| `loading` | Boolean | `false` | Shows loading state |
| `showCloseButton` | Boolean | `true` | Shows X button in header |
| `requireConfirmationText` | Boolean | `false` | Requires typing confirmation text |
| `confirmationText` | String | `'DELETE'` | Text that must be typed to confirm |
| `closeOnBackdrop` | Boolean | `true` | Allow closing by clicking backdrop |

## Events

| Event | Description |
|-------|-------------|
| `confirm` | Emitted when confirm button is clicked |
| `cancel` | Emitted when cancel button is clicked or modal is closed |
| `update:show` | Emitted to update the show prop (v-model support) |

## Examples by Type

### Danger (Delete Actions)
```vue
<ConfirmationModal
  type="danger"
  title="Delete Account"
  message="This will permanently delete your account and all associated data."
  confirm-text="Delete Account"
/>
```

### Warning (Potentially Destructive)
```vue
<ConfirmationModal
  type="warning"
  title="Unsaved Changes"
  message="You have unsaved changes. Are you sure you want to leave?"
  confirm-text="Leave Page"
/>
```

### Info (General Confirmation)
```vue
<ConfirmationModal
  type="info"
  title="Send Notification"
  message="This will send notifications to all users."
  confirm-text="Send"
/>
```

### With Confirmation Text (High-Risk Actions)
```vue
<ConfirmationModal
  type="danger"
  title="Delete Database"
  message="This will permanently delete the entire database."
  :require-confirmation-text="true"
  confirmation-text="DELETE DATABASE"
  confirm-text="Delete Database"
/>
```

## Styling

The component uses Tailwind CSS classes and automatically applies appropriate colors based on the `type` prop:

- **Danger**: Red colors for destructive actions
- **Warning**: Yellow colors for potentially harmful actions  
- **Info**: Blue colors for informational confirmations
- **Default**: Gray colors for neutral confirmations

## Integration with Toast Notifications

The component works well with toast notifications for showing success/error messages:

```javascript
import { useToast } from '@/composables/useToast';

const { success, error } = useToast();

const confirmAction = async () => {
  try {
    await performAction();
    success('Action completed successfully');
  } catch (err) {
    error('Action failed. Please try again.');
  }
};
```
