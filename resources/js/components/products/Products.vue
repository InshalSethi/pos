<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Products</h1>
          <div class="flex space-x-3">
            <button
              v-if="authStore.hasPermission('products.import')"
              @click="showImportModal = true"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Import Products
            </button>
            <button
              v-if="authStore.hasPermission('products.export')"
              @click="exportProducts"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Export Products
            </button>
            <button
              @click="openCategoryModal"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Manage Categories
            </button>
            <button
              v-if="authStore.hasPermission('products.create')"
              @click="showCreateModal = true"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Add Product
            </button>
          </div>
        </div>

        <!-- View Toggle -->
        <div class="flex justify-between items-center mb-6">
          <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
            <button
              @click="activeView = 'grid'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                activeView === 'grid'
                  ? 'bg-white text-gray-900 shadow-sm'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
              Grid
            </button>
            <button
              @click="activeView = 'table'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                activeView === 'table'
                  ? 'bg-white text-gray-900 shadow-sm'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
              Table
            </button>
          </div>
          <div class="text-sm text-gray-500">
            {{ products.length }} products total
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                @input="debouncedSearch"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select
                v-model="selectedCategory"
                @change="fetchProducts"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.full_name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="selectedStatus"
                @change="fetchProducts"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">All Status</option>
                <option value="true">Active</option>
                <option value="false">Inactive</option>
              </select>
            </div>
            <div class="flex items-end space-x-2">
              <button
                @click="toggleLowStock"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium',
                  showLowStock
                    ? 'bg-red-600 hover:bg-red-700 text-white'
                    : 'bg-gray-500 hover:bg-gray-600 text-white'
                ]"
              >
                {{ showLowStock ? 'All Products' : 'Low Stock' }}
              </button>
              <button
                @click="clearFilters"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Clear
              </button>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-if="loading" class="bg-white shadow rounded-lg p-6 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-2 text-gray-600">Loading products...</p>
        </div>

        <div v-else-if="products.length === 0" class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
          No products found.
        </div>

        <!-- Products Grid -->
        <div v-if="activeView === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="product in products"
            :key="product.id"
            class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
          >
            <!-- Product Image -->
            <div class="h-48 bg-gray-200 flex items-center justify-center">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="h-full w-full object-cover"
              />
              <div v-else class="text-gray-400">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-medium text-gray-900 truncate">{{ product.name }}</h3>
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    product.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <p class="text-sm text-gray-600 mb-2">SKU: {{ product.sku }}</p>

              <div class="flex justify-between items-center mb-2">
                <span class="text-lg font-bold text-green-600">{{ product.formatted_price }}</span>
                <span v-if="product.category" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                  {{ product.category.name }}
                </span>
              </div>

              <!-- Stock Info -->
              <div class="flex justify-between items-center mb-3">
                <span class="text-sm text-gray-600">Stock: {{ product.stock_quantity }}</span>
                <span
                  v-if="product.is_low_stock"
                  class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded"
                >
                  Low Stock
                </span>
              </div>

              <!-- Actions -->
              <div class="flex justify-between">
                <button
                  @click="editProduct(product)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                >
                  Edit
                </button>
                <button
                  @click="viewProduct(product)"
                  class="text-green-600 hover:text-green-900 text-sm font-medium"
                >
                  View
                </button>
                <button
                  @click="deleteProduct(product)"
                  class="text-red-600 hover:text-red-900 text-sm font-medium"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Table -->
        <div v-if="activeView === 'table'" class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Category
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Price
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stock
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-12 w-12">
                        <img
                          v-if="product.image"
                          :src="product.image"
                          :alt="product.name"
                          class="h-12 w-12 rounded-lg object-cover"
                        />
                        <div v-else class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                          <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                          </svg>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                        <div class="text-sm text-gray-500">{{ product.sku || 'No SKU' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="product.category" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ product.category.name }}
                    </span>
                    <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                      No Category
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ product.formatted_price }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ product.stock_quantity }}</div>
                    <div class="text-sm text-gray-500">Min: {{ product.low_stock_threshold }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                        product.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span
                      v-if="product.is_low_stock"
                      class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                    >
                      Low Stock
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-3">
                      <button
                        @click="viewProduct(product)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        View
                      </button>
                      <button
                        @click="editProduct(product)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteProduct(product)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="mt-6 bg-white shadow rounded-lg px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Previous
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      page === pagination.current_page
                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
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
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

// Reactive data
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const submitting = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');
const showLowStock = ref(false);
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
const activeView = ref('grid');
const loadingCategories = ref(false);
const creatingCategory = ref(false);
const editingCategory = ref(false);
const editingCategoryData = ref(null);
const categoryForm = ref({
  name: '',
  description: '',
  parent_id: ''
});

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
  from: 0,
  to: 0
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

// Computed
const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Methods
const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page
    };

    if (searchQuery.value) {
      params.search = searchQuery.value;
    }

    if (selectedCategory.value) {
      params.category_id = selectedCategory.value;
    }

    if (selectedStatus.value !== '') {
      params.is_active = selectedStatus.value;
    }

    if (showLowStock.value) {
      params.low_stock = true;
    }

    const response = await axios.get('/api/products', { params });
    products.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    loading.value = false;
  }
};

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
    fetchProducts();
  } catch (error) {
    alert(error.response?.data?.message || 'An error occurred');
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchProducts(page);
  }
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
  selectedStatus.value = '';
  showLowStock.value = false;
  fetchProducts();
};

const toggleLowStock = () => {
  showLowStock.value = !showLowStock.value;
  fetchProducts();
};

const createProduct = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.post('/api/products', productForm.value);
    closeProductModal();
    fetchProducts();
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

const updateProduct = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.put(`/api/products/${editingProduct.value.id}`, productForm.value);
    closeProductModal();
    fetchProducts();
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

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchProducts();
  }, 500);
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
    await fetchProducts();

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
    const params = new URLSearchParams();

    if (searchQuery.value) {
      params.append('search', searchQuery.value);
    }

    if (selectedCategory.value) {
      params.append('category_id', selectedCategory.value);
    }

    const response = await axios.get('/api/products/export', {
      params: params,
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
  fetchProducts();
  fetchCategories();
});
</script>
