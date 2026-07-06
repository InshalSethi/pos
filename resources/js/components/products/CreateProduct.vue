<template>
  <ProductForm 
    title="New Product" 
    subtitle="Create a stunning new addition to your inventory"
    :loading="loading"
    :errors="errors"
    @submit="handleCreate"
  />
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import ProductForm from './ProductForm.vue';
import axios from 'axios';

const router = useRouter();
const loading = ref(false);
const errors = ref([]);

const handleCreate = async (formData) => {
  loading.value = true;
  errors.value = [];

  try {
    const data = new FormData();
    Object.keys(formData).forEach(key => {
      // Don't send the preview URL or empty values
      if (key === 'image_url') return;
      
      const value = formData[key];
      
      // Handle complex arrays/objects as JSON strings, others normally
      if (['variations', 'tags', 'taxes', 'attributes', 'warehouses', 'warehouse_ids'].includes(key)) {
        data.append(key, JSON.stringify(value));
      } else if (Array.isArray(value)) {
        value.forEach((item, index) => {
          if (typeof item === 'object' && item !== null) {
            Object.keys(item).forEach(objKey => {
              data.append(`${key}[${index}][${objKey}]`, item[objKey] === null ? '' : item[objKey]);
            });
          } else {
            data.append(`${key}[]`, item);
          }
        });
      } else if (typeof value === 'boolean') {
        data.append(key, value ? '1' : '0');
      } else if (value === null && key === 'image') {
        data.append(key, '');
      } else if (value !== null && value !== '') {
        data.append(key, value);
      }
    });

    await axios.post('/api/products', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    // Success redirect
    router.push({ name: 'Products' });
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = Object.values(error.response.data.errors).flat();
    } else {
      errors.value = [error.response?.data?.message || 'An error occurred during submission'];
    }
  } finally {
    loading.value = false;
  }
};
</script>
