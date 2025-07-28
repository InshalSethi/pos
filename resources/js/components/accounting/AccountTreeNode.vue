<template>
  <div>
    <!-- Account Node -->
    <div 
      :class="[
        'flex items-center justify-between p-3 rounded-lg border',
        level === 0 ? 'bg-gray-50 border-gray-200' : 'bg-white border-gray-100',
        'hover:bg-gray-100 transition-colors'
      ]"
      :style="{ marginLeft: `${level * 20}px` }"
    >
      <div class="flex items-center space-x-3">
        <!-- Expand/Collapse Button -->
        <button
          v-if="account.children && account.children.length > 0"
          @click="toggleExpanded"
          class="w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-600"
        >
          <svg
            :class="{ 'transform rotate-90': expanded }"
            class="w-4 h-4 transition-transform"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
        <div v-else class="w-5"></div>

        <!-- Account Info -->
        <div class="flex-1">
          <div class="flex items-center space-x-3">
            <span class="text-sm font-medium text-gray-900">{{ account.code }}</span>
            <span class="text-sm text-gray-700">{{ account.name }}</span>
            <span
              :class="[
                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                getAccountTypeColor(account.account_type)
              ]"
            >
              {{ formatAccountType(account.account_type) }}
            </span>
            <span v-if="account.is_system" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
              System
            </span>
          </div>
          <div v-if="account.description" class="text-xs text-gray-500 mt-1">
            {{ account.description }}
          </div>
        </div>

        <!-- Balance -->
        <div class="text-right">
          <div class="text-sm font-medium text-gray-900">
            {{ account.formatted_balance }}
          </div>
          <div class="text-xs text-gray-500">
            {{ account.is_active ? 'Active' : 'Inactive' }}
          </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-2">
          <button
            @click="$emit('edit', account)"
            class="text-indigo-600 hover:text-indigo-900 text-sm"
          >
            Edit
          </button>
          <button
            v-if="!account.is_system"
            @click="$emit('delete', account)"
            class="text-red-600 hover:text-red-900 text-sm"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Children -->
    <div v-if="expanded && account.children && account.children.length > 0" class="mt-2 space-y-2">
      <AccountTreeNode
        v-for="child in account.children"
        :key="child.id"
        :account="child"
        :level="level + 1"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Props
const props = defineProps({
  account: {
    type: Object,
    required: true
  },
  level: {
    type: Number,
    default: 0
  }
});

// Emits
const emit = defineEmits(['edit', 'delete']);

// Reactive data
const expanded = ref(true);

// Methods
const toggleExpanded = () => {
  expanded.value = !expanded.value;
};

const getAccountTypeColor = (type) => {
  const colors = {
    asset: 'bg-blue-100 text-blue-800',
    liability: 'bg-red-100 text-red-800',
    equity: 'bg-green-100 text-green-800',
    revenue: 'bg-purple-100 text-purple-800',
    expense: 'bg-yellow-100 text-yellow-800'
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const formatAccountType = (type) => {
  const types = {
    asset: 'Asset',
    liability: 'Liability',
    equity: 'Equity',
    revenue: 'Revenue',
    expense: 'Expense'
  };
  return types[type] || type;
};
</script>
