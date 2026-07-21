<template>
  <div v-if="loadingProduct" class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-zinc-950">
     <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 dark:border-indigo-400 mx-auto"></div>
        <p class="mt-4 text-gray-500 dark:text-slate-400 font-bold uppercase tracking-widest text-xs">Loading Product Data...</p>
     </div>
  </div>
  <ProductForm 
    v-else
    title="Edit Product" 
    subtitle="Update and refine your product specifications"
    :initialData="product"
    :loading="loading"
    :errors="errors"
    @submit="handleUpdate"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import ProductForm from './ProductForm.vue';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const loadingProduct = ref(true);
const errors = ref([]);
const product = ref(null);

onMounted(async () => {
  try {
    const response = await axios.get(`/api/products/${route.params.id}`);
    product.value = response.data;
    // Map initial image if exists
    if (product.value.image && !product.value.image.includes('Temp') && !product.value.image.includes('.tmp')) {
      product.value.image_url = product.value.image.startsWith('/') ? product.value.image : '/' + product.value.image;
    }
  } catch (error) {
    console.error('Error fetching product:', error);
    alert('Product not found');
    router.push({ name: 'Products' });
  } finally {
    loadingProduct.value = false;
  }
});

const handleUpdate = async (formData) => {
  loading.value = true;
  errors.value = [];

  try {
    const data = new FormData();
    // Laravel bug: PUT/PATCH requests don't handle files in FormData well.
    // Use POST with _method spoofing if there's a file.
    data.append('_method', 'PUT');
    
    Object.keys(formData).forEach(key => {
      if (key === 'image_url') return;
      
      const value = formData[key];
      
      // Handle complex arrays/objects as JSON strings, others normally
      if (['variations', 'tags', 'taxes', 'attributes', 'warehouses', 'warehouse_ids'].includes(key)) {
        data.append(key, JSON.stringify(value));
      } else if (key === 'images' && Array.isArray(value)) {
        const validItems = value.filter(item => item !== null && item !== undefined && item !== 'null' && item !== 'undefined' && item !== '');
        if (validItems.length === 0) {
          data.append('images', '');
        } else {
          validItems.forEach((item, index) => {
            data.append(`images[${index}]`, item);
          });
        }
      } else if (Array.isArray(value)) {
        value.forEach((item, index) => {
          if (typeof item === 'object' && item !== null && !(item instanceof File) && !(item instanceof Blob)) {
            Object.keys(item).forEach(objKey => {
              data.append(`${key}[${index}][${objKey}]`, item[objKey] === null ? '' : item[objKey]);
            });
          } else {
            data.append(`${key}[]`, item);
          }
        });
      } else if (typeof value === 'boolean') { // Handle booleans explicitly for FormData
        data.append(key, value ? '1' : '0');
      } else if ((value === null || value === 'null' || value === 'undefined') && key === 'image') {
        data.append(key, '');
      } else if (value !== null && value !== undefined && value !== 'null' && value !== 'undefined' && value !== '') {
        data.append(key, value);
      }
    });

    await axios.post(`/api/products/${route.params.id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    router.push({ name: 'Products' });
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = Object.values(error.response.data.errors).flat();
    } else {
      errors.value = [error.response?.data?.message || 'Update failed'];
    }
  } finally {
    loading.value = false;
  }
};
</script>
