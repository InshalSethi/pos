<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Products</h1>
        </div>


        <!-- Products Table -->
        <DataTable
          title="Products"
          subtitle="Manage your product inventory and details"
          :columns="tableColumns"
          :data="products"
          :loading="loading"
          :pagination="tablePagination"
          storage-key="products-table-state"
          empty-message="No products found"
          empty-sub-message="Get started by adding your first product."
          @search="handleTableSearch"
          @sort="handleSort"
          @page-change="handlePageChange"
          @per-page-change="handlePerPageChange"
        >
            <!-- Custom column content -->
            <template #column-product="{ item }">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-12 w-12">
                  <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.name"
                    class="h-12 w-12 rounded-lg object-cover"
                  />
                  <div v-else class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                  <div class="text-sm text-gray-500">{{ item.sku || 'No SKU' }}</div>
                </div>
              </div>
            </template>

            <template #column-category="{ item }">
              <span v-if="item.category" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ item.category.name }}
              </span>
              <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                No Category
              </span>
            </template>

            <template #column-stock="{ item }">
              <div>
                <div class="text-sm text-gray-900">{{ item.stock_quantity }}</div>
                <div class="text-sm text-gray-500">Min: {{ item.low_stock_threshold }}</div>
              </div>
            </template>

            <template #column-status="{ item }">
              <div class="flex flex-col space-y-1">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    item.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span
                  v-if="item.is_low_stock"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                >
                  Low Stock
                </span>
              </div>
            </template>

            <template #column-actions="{ item }">
              <div class="flex space-x-3">
                <button
                  @click="viewProduct(item)"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  View
                </button>
                <button
                  @click="editProduct(item)"
                  class="text-green-600 hover:text-green-900"
                >
                  Edit
                </button>
                <button
                  @click="deleteProduct(item)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </div>
            </template>

            <!-- Action buttons in header -->
            <template #actions>
              <div class="flex space-x-3">
                <button
                  v-if="authStore.hasPermission('products.import')"
                  @click="showImportModal = true"
                  class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                  </svg>
                  Import
                </button>
                <button
                  v-if="authStore.hasPermission('products.export')"
                  @click="exportProducts"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  Export
                </button>
                <button
                  @click="openCategoryModal"
                  class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                  Categories
                </button>
                <button
                  v-if="authStore.hasPermission('products.create')"
                  @click="showCreateModal = true"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Add Product
                </button>
              </div>
            </template>
        </DataTable>
      </div>
    </div>

    <!-- Import Products Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Import Products</h3>
            <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <!-- Download Template -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h4 class="text-sm font-medium text-blue-900 mb-2">Download Template</h4>
              <p class="text-sm text-blue-700 mb-3">Download the CSV template to see the required format for importing products.</p>
              <button
                @click="downloadTemplate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Download Template
              </button>
            </div>

            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
              <input
                ref="fileInput"
                type="file"
                accept=".csv,.xlsx,.xls"
                @change="handleFileSelect"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
              <p class="text-xs text-gray-500 mt-1">Supported formats: CSV, XLSX, XLS (Max: 10MB)</p>
            </div>

            <!-- Selected File Info -->
            <div v-if="selectedFile" class="bg-gray-50 border border-gray-200 rounded-lg p-3">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm text-gray-700">{{ selectedFile.name }}</span>
                <span class="text-xs text-gray-500 ml-2">({{ formatFileSize(selectedFile.size) }})</span>
              </div>
            </div>

            <!-- Import Results -->
            <div v-if="importResults" class="space-y-3">
              <div v-if="importResults.imported > 0" class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-sm text-green-800">
                  ✓ Successfully imported {{ importResults.imported }} products
                </p>
              </div>

              <div v-if="importResults.errors && importResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="text-sm text-red-800 font-medium mb-2">Import Errors:</p>
                <div class="max-h-32 overflow-y-auto">
                  <div v-for="error in importResults.errors" :key="error.row" class="text-xs text-red-700 mb-1">
                    Row {{ error.row }}: {{ error.errors.join(', ') }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                @click="closeImportModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                @click="importProducts"
                :disabled="!selectedFile || importing"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ importing ? 'Importing...' : 'Import Products' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Product Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showCreateModal ? 'Create Product' : 'Edit Product' }}
          </h3>

          <form @submit.prevent="showCreateModal ? createProduct() : updateProduct()">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Basic Information -->
              <div class="md:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-3">Basic Information</h4>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                <input
                  v-model="productForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select
                  v-model="productForm.category_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Select Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.full_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                <input
                  v-model="productForm.sku"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                <input
                  v-model="productForm.barcode"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                  v-model="productForm.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                ></textarea>
              </div>

              <!-- Pricing -->
              <div class="md:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-3 mt-4">Pricing</h4>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price</label>
                <input
                  v-model="productForm.cost_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price *</label>
                <input
                  v-model="productForm.selling_price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                <input
                  v-model="productForm.tax_rate"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <!-- Inventory -->
              <div class="md:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-3 mt-4">Inventory</h4>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                <input
                  v-model="productForm.stock_quantity"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure</label>
                <select
                  v-model="productForm.unit_of_measure"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="pcs">Pieces</option>
                  <option value="kg">Kilograms</option>
                  <option value="lbs">Pounds</option>
                  <option value="liters">Liters</option>
                  <option value="meters">Meters</option>
                  <option value="boxes">Boxes</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Min Stock Level</label>
                <input
                  v-model="productForm.min_stock_level"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Max Stock Level</label>
                <input
                  v-model="productForm.max_stock_level"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <!-- Additional Info -->
              <div class="md:col-span-2">
                <h4 class="text-md font-medium text-gray-800 mb-3 mt-4">Additional Information</h4>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                <input
                  v-model="productForm.weight"
                  type="number"
                  step="0.001"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions</label>
                <input
                  v-model="productForm.dimensions"
                  type="text"
                  placeholder="L x W x H"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div class="md:col-span-2 flex space-x-4">
                <label class="flex items-center">
                  <input
                    v-model="productForm.track_inventory"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <span class="ml-2 text-sm text-gray-700">Track Inventory</span>
                </label>

                <label class="flex items-center">
                  <input
                    v-model="productForm.is_active"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
              </div>
            </div>

            <div v-if="formErrors.length > 0" class="mt-4">
              <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <ul class="text-sm text-red-600">
                  <li v-for="error in formErrors" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closeProductModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ submitting ? 'Saving...' : (showCreateModal ? 'Create' : 'Update') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Product Modal -->
    <div v-if="showViewModal && viewingProduct" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Product Details</h3>
            <button
              @click="showViewModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">SKU</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.sku }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Category</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.category?.name || 'No category' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Barcode</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.barcode || 'N/A' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Cost Price</label>
              <p class="mt-1 text-sm text-gray-900">${{ viewingProduct.cost_price }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Selling Price</label>
              <p class="mt-1 text-sm text-gray-900">${{ viewingProduct.selling_price }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Stock Quantity</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.stock_quantity }} {{ viewingProduct.unit_of_measure }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Min Stock Level</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.min_stock_level }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <p class="mt-1 text-sm text-gray-900">{{ viewingProduct.description || 'No description' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Management Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 overflow-y-auto h-full w-full" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.5);">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">Manage Categories</h3>
              <button
                @click="closeCategoryModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Create/Edit Category -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-md font-medium text-gray-900 mb-4">
                {{ editingCategoryData ? 'Edit Category' : 'Create New Category' }}
              </h4>
              <form @submit.prevent="editingCategoryData ? updateCategory() : createCategory()">
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                  <input
                    v-model="categoryForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter category name"
                  />
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                  <textarea
                    v-model="categoryForm.description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter category description"
                  ></textarea>
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                  <select
                    v-model="categoryForm.parent_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">No Parent (Root Category)</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>
                <div class="flex justify-end space-x-3">
                  <button
                    v-if="editingCategoryData"
                    type="button"
                    @click="cancelEdit"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="creatingCategory || editingCategory"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50"
                  >
                    {{ editingCategoryData
                        ? (editingCategory ? 'Updating...' : 'Update Category')
                        : (creatingCategory ? 'Creating...' : 'Create Category')
                    }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Categories List -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Existing Categories</h4>
              <div class="max-h-96 overflow-y-auto">
                <div v-if="loadingCategories" class="text-center py-4">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600 mx-auto"></div>
                </div>
                <div v-else-if="categories.length === 0" class="text-center py-4 text-gray-500">
                  No categories found.
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="category in categories"
                    :key="category.id"
                    class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                  >
                    <div>
                      <div class="font-medium text-gray-900">{{ category.name }}</div>
                      <div v-if="category.description" class="text-sm text-gray-500">{{ category.description }}</div>
                      <div class="text-xs text-gray-400">
                        {{ category.products_count || 0 }} products
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="editCategory(category)"
                        class="text-indigo-600 hover:text-indigo-900 text-sm"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteCategory(category)"
                        class="text-red-600 hover:text-red-900 text-sm"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

const authStore = useAuthStore();

// Reactive data
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const submitting = ref(false);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showCategoryModal = ref(false);
const showImportModal = ref(false);
const editingProduct = ref(null);
const viewingProduct = ref(null);
const formErrors = ref([]);

// Import/Export related
const importing = ref(false);
const selectedFile = ref(null);
const importResults = ref(null);
const fileInput = ref(null);

// Category management
const loadingCategories = ref(false);
const creatingCategory = ref(false);
const editingCategory = ref(false);
const editingCategoryData = ref(null);
const categoryForm = ref({
  name: '',
  description: '',
  parent_id: ''
});

// Table pagination
const tablePagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
  from: 0,
  to: 0
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'product',
    label: 'Product',
    sortable: true,
    align: 'left'
  },
  {
    key: 'category',
    label: 'Category',
    sortable: true,
    align: 'left'
  },
  {
    key: 'formatted_price',
    label: 'Price',
    sortable: true,
    align: 'right'
  },
  {
    key: 'stock',
    label: 'Stock',
    sortable: true,
    align: 'left'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'left'
  }
]);

// Table filters
const tableFilters = ref({
  search: '',
  sort_field: '',
  sort_order: ''
});

// Product form
const productForm = ref({
  name: '',
  description: '',
  sku: '',
  barcode: '',
  cost_price: '',
  selling_price: '',
  markup_percentage: '',
  stock_quantity: '',
  min_stock_level: '',
  max_stock_level: '',
  unit_of_measure: 'pcs',
  track_inventory: true,
  is_active: true,
  category_id: '',
  weight: '',
  dimensions: '',
  tax_rate: ''
});



// Methods

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const editProduct = (product) => {
  editingProduct.value = product;
  productForm.value = {
    name: product.name,
    description: product.description || '',
    sku: product.sku,
    barcode: product.barcode || '',
    cost_price: product.cost_price,
    selling_price: product.selling_price,
    markup_percentage: product.markup_percentage,
    stock_quantity: product.stock_quantity,
    min_stock_level: product.min_stock_level,
    max_stock_level: product.max_stock_level || '',
    unit_of_measure: product.unit_of_measure,
    track_inventory: product.track_inventory,
    is_active: product.is_active,
    category_id: product.category_id || '',
    weight: product.weight || '',
    dimensions: product.dimensions || '',
    tax_rate: product.tax_rate
  };
  showEditModal.value = true;
};

const viewProduct = (product) => {
  viewingProduct.value = product;
  showViewModal.value = true;
};

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete ${product.name}?`)) {
    return;
  }

  try {
    await axios.delete(`/api/products/${product.id}`);
    fetchProductsForTable();
  } catch (error) {
    alert(error.response?.data?.message || 'An error occurred');
  }
};



// DataTable event handlers
const handleTableSearch = (searchQuery) => {
  tableFilters.value.search = searchQuery;
  fetchProductsForTable(1);
};

const handleSort = (sortData) => {
  tableFilters.value.sort_field = sortData.field;
  tableFilters.value.sort_order = sortData.order;
  fetchProductsForTable(1);
};

const handlePageChange = (page) => {
  fetchProductsForTable(page);
};

const handlePerPageChange = (perPage) => {
  tablePagination.value.per_page = perPage;
  fetchProductsForTable(1);
};

// Separate fetch method for table view
const fetchProductsForTable = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: tablePagination.value.per_page,
      ...tableFilters.value
    };

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/products', { params });
    products.value = response.data.data;

    // Update table pagination
    tablePagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching products for table:', error);
  } finally {
    loading.value = false;
  }
};



const createProduct = () => {
  showCreateModal.value = true;
};

const updateProduct = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.put(`/api/products/${editingProduct.value.id}`, productForm.value);
    closeProductModal();
    fetchProductsForTable();
  } catch (error) {
    if (error.response?.data?.errors) {
      formErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      formErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    submitting.value = false;
  }
};

const closeProductModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingProduct.value = null;
  productForm.value = {
    name: '',
    description: '',
    sku: '',
    barcode: '',
    cost_price: '',
    selling_price: '',
    markup_percentage: '',
    stock_quantity: '',
    min_stock_level: '',
    max_stock_level: '',
    unit_of_measure: 'pcs',
    track_inventory: true,
    is_active: true,
    category_id: '',
    weight: '',
    dimensions: '',
    tax_rate: ''
  };
  formErrors.value = [];
};





// Category management functions
const openCategoryModal = async () => {
  console.log('Opening category modal...');
  try {
    showCategoryModal.value = true;
    await fetchCategories(); // Refresh categories when opening modal
    console.log('Category modal opened successfully');
  } catch (error) {
    console.error('Error opening category modal:', error);
    alert('Error opening category modal. Please check the console for details.');
  }
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
  categoryForm.value = { name: '', description: '', parent_id: '' };
  editingCategoryData.value = null;
};

const createCategory = async () => {
  creatingCategory.value = true;
  try {
    const response = await axios.post('/api/categories', categoryForm.value);
    categories.value.push(response.data.category);
    categoryForm.value = { name: '', description: '', parent_id: '' };
    alert('Category created successfully!');
  } catch (error) {
    console.error('Error creating category:', error);
    alert('Failed to create category');
  } finally {
    creatingCategory.value = false;
  }
};

const editCategory = (category) => {
  editingCategoryData.value = category;
  categoryForm.value = {
    name: category.name,
    description: category.description || '',
    parent_id: category.parent_id || ''
  };
  // Scroll to the form
  setTimeout(() => {
    const form = document.querySelector('.bg-gray-50');
    if (form) {
      form.scrollIntoView({ behavior: 'smooth' });
    }
  }, 100);
};

const updateCategory = async () => {
  editingCategory.value = true;
  try {
    const response = await axios.put(`/api/categories/${editingCategoryData.value.id}`, categoryForm.value);
    const index = categories.value.findIndex(c => c.id === editingCategoryData.value.id);
    if (index !== -1) {
      categories.value[index] = response.data.category;
    }
    categoryForm.value = { name: '', description: '', parent_id: '' };
    editingCategoryData.value = null;
    alert('Category updated successfully!');
  } catch (error) {
    console.error('Error updating category:', error);
    alert('Failed to update category');
  } finally {
    editingCategory.value = false;
  }
};

const cancelEdit = () => {
  editingCategoryData.value = null;
  categoryForm.value = { name: '', description: '', parent_id: '' };
};

const deleteCategory = async (category) => {
  if (!confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
    return;
  }

  try {
    await axios.delete(`/api/categories/${category.id}`);
    categories.value = categories.value.filter(c => c.id !== category.id);
    alert('Category deleted successfully!');
  } catch (error) {
    console.error('Error deleting category:', error);
    alert('Failed to delete category');
  }
};

// Import/Export methods
const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    importResults.value = null;
  }
};

const downloadTemplate = async () => {
  try {
    const response = await axios.get('/api/products/download-template', {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'product_import_template.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading template:', error);
    alert('Failed to download template');
  }
};

const importProducts = async () => {
  if (!selectedFile.value) {
    alert('Please select a file to import');
    return;
  }

  importing.value = true;
  importResults.value = null;

  try {
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    const response = await axios.post('/api/products/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    importResults.value = response.data;

    // Refresh products list
    await fetchProductsForTable();

    // Clear file selection
    selectedFile.value = null;
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  } catch (error) {
    console.error('Error importing products:', error);
    alert(error.response?.data?.message || 'Failed to import products');
  } finally {
    importing.value = false;
  }
};

const exportProducts = async () => {
  try {
    // Export all products (DataTable search filters are not applied to export)
    const response = await axios.get('/api/products/export', {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;

    // Extract filename from response headers or use default
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'products_export.csv';
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch) {
        filename = filenameMatch[1];
      }
    }

    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting products:', error);
    alert('Failed to export products');
  }
};

const closeImportModal = () => {
  showImportModal.value = false;
  selectedFile.value = null;
  importResults.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};



// Lifecycle
onMounted(() => {
  fetchProductsForTable();
  fetchCategories();
});
</script>
