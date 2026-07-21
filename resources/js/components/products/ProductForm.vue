<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 font-sans text-slate-750 dark:text-slate-350 min-h-screen">
    <!-- Sticky Header -->
    <div class="sticky top-0 bg-white dark:bg-[#1E1E1E] border-b border-slate-100 dark:border-[#2E2E2E] shadow-[0_1px_3px_rgba(0,0,0,0.02)] z-30 w-full">
      <div class="px-4 py-3 w-full max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button @click="$router.back()" type="button" class="p-2 rounded-md hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          </button>
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ title || 'New Item' }}</h1>
            <!-- Clean Hollow Favorite Star Icon -->
            <button type="button" @click="isStarred = !isStarred" class="text-slate-400 hover:text-amber-400 transition-colors focus:outline-none">
              <svg class="w-6 h-6" :class="{ 'fill-amber-400 text-amber-400': isStarred, 'text-slate-300 dark:text-slate-600': !isStarred }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.773-.564-.374-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
              </svg>
            </button>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span v-if="form.status === 'active'" class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-900/40">Active</span>
          <span v-else-if="form.status === 'inactive'" class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-rose-50 dark:bg-rose-955/20 text-rose-700 dark:text-rose-450 border-rose-200 dark:border-rose-900/30">Inactive</span>
          <span v-else class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-amber-50 dark:bg-amber-955/20 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-900/40">Draft</span>
        </div>
      </div>
    </div>

    <!-- Main Form Canvas Container -->
    <div class="px-6 py-4">
      <div class="w-full max-w-7xl mx-auto">
        <form id="product-form" @submit.prevent="submit" class="space-y-4">
          
          <!-- 1. GENERAL SECTION -->
          <div class="space-y-3">
            <div class="border-b border-gray-200 dark:border-[#2E2E2E] pb-2 mb-3 flex items-center justify-between">
              <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200">General</h2>
              <!-- Elegant Inline Toggle Switcher -->
              <div class="flex items-center gap-2 select-none">
                <div class="inline-flex p-1 bg-slate-100 dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-inner">
                  <button
                    v-for="opt in statusOptions"
                    :key="opt.value"
                    type="button"
                    @click="setStatus(opt.value)"
                    class="px-3.5 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all duration-200 cursor-pointer"
                    :class="form.status === opt.value
                      ? 'bg-white dark:bg-zinc-800 text-indigo-650 dark:text-indigo-400 shadow-md border border-slate-200/80 dark:border-zinc-750'
                      : 'text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300'"
                  >
                    {{ opt.label }}
                  </button>
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Left Column: Input Controls -->
              <div class="md:col-span-2 space-y-3">
                <!-- Name -->
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                    Name *
                    <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                      <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                        Enter the full descriptive name of the product or item.
                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                      </span>
                    </span>
                  </label>
                  <input 
                    v-model="form.name" 
                    type="text" 
                    class="w-full px-3 py-1.5 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 rounded-md text-sm font-medium transition-all text-slate-800 dark:text-slate-300 outline-none" 
                    required
                  />
                </div>

                <!-- Category & Tags Side-by-Side Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Multi-select Category Badge / Tag Input -->
                  <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                      Category *
                      <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                        <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                          <circle cx="12" cy="12" r="10" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                          <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                          Select one or more categories to classify this product.
                          <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                        </span>
                      </span>
                    </label>
                    <div class="relative" id="category-multiselect-container">
                      <div 
                        @click="showCategoryDropdown = !showCategoryDropdown"
                        class="w-full min-h-[34px] px-3 py-1 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus-within:border-slate-300 dark:focus-within:border-slate-700 focus-within:ring-1 focus-within:ring-slate-300/25 rounded-md text-sm font-medium transition-all text-slate-800 dark:text-slate-300 outline-none cursor-pointer flex flex-wrap items-center gap-1 pr-8"
                      >
                        <div 
                          v-for="catId in form.category_ids" 
                          :key="catId" 
                          class="bg-slate-800 dark:bg-[#1E1E1E] text-slate-100 dark:text-slate-300 border border-slate-700 dark:border-[#2E2E2E] rounded px-2 py-0.5 text-xs flex items-center gap-1 font-semibold shadow-xs"
                        >
                          <span>{{ getCategoryLabel(catId) }}</span>
                          <button type="button" @click.stop="removeCategory(catId)" class="hover:text-red-350 font-bold focus:outline-none">&times;</button>
                        </div>
                        <span v-if="form.category_ids.length === 0" class="text-gray-400 dark:text-slate-550 text-sm">Add category</span>
                        
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                        </span>
                      </div>
 
                      <!-- Dropdown Menu -->
                      <div v-if="showCategoryDropdown" class="absolute z-50 left-0 mt-1 w-full bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] shadow-lg dark:shadow-slate-950/80 rounded-xl max-h-60 overflow-y-auto p-1 animate-in fade-in zoom-in-95 duration-100">
                        <div 
                          v-for="opt in categoryOptions" 
                          :key="opt.value"
                          @click="toggleCategorySelection(opt.value)"
                          class="px-3 py-2 text-xs font-semibold cursor-pointer hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/60 rounded-lg flex items-center justify-between"
                        >
                          <span :class="form.category_ids.includes(opt.value) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">{{ opt.label }}</span>
                          <span v-if="form.category_ids.includes(opt.value)" class="text-indigo-600 dark:text-indigo-450">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
 
                  <!-- Multi-select Tags Badge / Tag Input -->
                  <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                    Tags
                    <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                      <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                        Add tags to help search, filter, and organize products easily.
                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                      </span>
                    </span>
                  </label>
                  <div class="relative" id="tag-multiselect-container">
                    <div 
                      @click="showTagDropdown = !showTagDropdown"
                      class="w-full min-h-[34px] px-3 py-1 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus-within:border-slate-300 dark:focus-within:border-slate-700 focus-within:ring-1 focus-within:ring-slate-300/25 rounded-md text-sm font-medium transition-all text-slate-800 dark:text-slate-300 outline-none cursor-pointer flex flex-wrap items-center gap-1 pr-8"
                    >
                      <div 
                        v-for="tagName in (form.tags || [])" 
                        :key="tagName" 
                        class="bg-indigo-100 dark:bg-indigo-950/40 text-indigo-850 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/30 rounded px-2 py-0.5 text-xs flex items-center gap-1 font-semibold"
                      >
                        <span>{{ tagName }}</span>
                        <button type="button" @click.stop="removeTag(tagName)" class="hover:text-red-650 font-bold focus:outline-none">&times;</button>
                      </div>
                      <span v-if="(!form.tags || form.tags.length === 0)" class="text-gray-400 dark:text-slate-550 text-sm">Select Tag(s)</span>
                      
                      <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                      </span>
                    </div>
 
                    <!-- Dropdown Menu -->
                    <div v-if="showTagDropdown" class="absolute z-50 left-0 mt-1 w-full bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] shadow-lg dark:shadow-slate-950/80 rounded-xl max-h-60 p-1 animate-in fade-in zoom-in-95 duration-100 flex flex-col">
                      <!-- Scrollable List of Options -->
                      <div class="overflow-y-auto max-h-44 custom-scrollbar">
                        <div 
                          v-for="opt in tagOptions" 
                          :key="opt.value"
                          @click="toggleTagSelection(opt.value)"
                          class="px-3 py-2 text-xs font-semibold cursor-pointer hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/60 rounded-lg flex items-center justify-between"
                        >
                          <span :class="(form.tags || []).includes(opt.value) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-350'">{{ opt.label }}</span>
                          <span v-if="(form.tags || []).includes(opt.value)" class="text-indigo-600 dark:text-indigo-455">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                          </span>
                        </div>
 
                        <!-- Empty State -->
                        <div v-if="tagOptions.length === 0" class="p-3 text-center">
                          <p class="text-slate-400 dark:text-slate-500 text-xs font-semibold">No tags found</p>
                        </div>
                      </div>
 
                      <!-- Inline Creation Footer -->
                      <div class="border-t border-slate-100 dark:border-[#2E2E2E] mt-1 p-1 bg-white dark:bg-[#1E1E1E]">
                        <button 
                          v-if="!showInlineCreateTag" 
                          type="button"
                          @click.stop="startInlineTagCreate" 
                          class="w-full py-1.5 px-3 text-left text-xs font-bold text-indigo-650 dark:text-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-[#2D2D2D]/50 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer focus:outline-none"
                        >
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                          </svg>
                          Add Tag
                        </button>
                        <div v-else class="flex items-center gap-1.5" @click.stop>
                          <input 
                            v-model="newInlineTagName"
                            ref="inlineTagInputRef"
                            @keydown.enter.prevent="submitInlineTag"
                            @keydown.esc="cancelInlineTagCreate"
                            type="text" 
                            placeholder="Enter tag name..."
                            class="flex-1 px-2 py-1 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] focus:border-slate-350 focus:bg-white dark:focus:bg-slate-900 rounded-lg text-xs font-semibold outline-none transition-all text-slate-800 dark:text-slate-300"
                          />
                          <button 
                            @click="submitInlineTag"
                            type="button"
                            class="p-1 text-emerald-600 hover:bg-emerald-50 rounded transition-all focus:outline-none"
                            title="Save Tag"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                          </button>
                          <button 
                            @click="cancelInlineTagCreate"
                            type="button"
                            class="p-1 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 rounded transition-all focus:outline-none"
                            title="Cancel"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
 
              <!-- Right Column: Picture Gallery (Single view with "+ N more" support) -->
              <div class="md:col-span-1 flex flex-col justify-end">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Item Pictures
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      Upload up to 8 images. Crop, rotate, zoom, and select the primary image.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <div class="space-y-2">
                  <!-- Single Image Container -->
                  <div v-if="productImages.length > 0" class="relative border border-slate-200 dark:border-[#2E2E2E] rounded-lg overflow-hidden h-32 group bg-slate-50/50 dark:bg-[#1E1E1E]/20 flex items-center justify-center">
                    <img :src="productImages[primaryImageIndex]?.preview || productImages[0]?.preview" class="w-full h-full object-cover">
                    
                    <!-- Overlay Badge for Multiple Images -->
                    <div 
                      v-if="productImages.length > 1" 
                      @click="openEditorForExisting(primaryImageIndex)"
                      class="absolute top-2 right-2 bg-slate-900/70 backdrop-blur-sm text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full select-none cursor-pointer hover:bg-slate-900 transition-colors"
                    >
                      +{{ productImages.length - 1 }} more
                    </div>
 
                    <!-- Star Overlay Badge (replaces Primary text badge) -->
                    <div class="absolute top-2 left-2 bg-slate-900/60 backdrop-blur-sm p-1 rounded-full text-amber-400 select-none shadow">
                      <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 24 24">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                      </svg>
                    </div>
 
                    <!-- Action Hover Controls -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-2 gap-2">
                      <!-- View (Blue) -->
                      <button type="button" @click="openGalleryViewer(primaryImageIndex)" class="p-1 text-sky-400 hover:text-sky-300 hover:scale-125 transition-all focus:outline-none drop-shadow" title="View Gallery">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      </button>
                      
                      <!-- Edit (Green) -->
                      <button type="button" @click="openEditorForExisting(primaryImageIndex)" class="p-1 text-emerald-400 hover:text-emerald-300 hover:scale-125 transition-all focus:outline-none drop-shadow" title="Edit/Manage Gallery">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                      </button>
                      
                      <!-- Add (Yellow) -->
                      <button v-if="productImages.length < 8" type="button" @click="$refs.imageInputRef.click()" class="p-1 text-amber-400 hover:text-amber-300 hover:scale-125 transition-all focus:outline-none drop-shadow" title="Add Image">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                      </button>
                      
                      <!-- Remove (Dark Pink) -->
                      <button type="button" @click="removeProductImage(primaryImageIndex)" class="p-1 text-pink-500 hover:text-pink-400 hover:scale-125 transition-all focus:outline-none drop-shadow" title="Remove This Image">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </div>
                  </div>
 
                  <!-- Empty State Dropzone -->
                  <div 
                    v-if="productImages.length === 0"
                    @click="$refs.imageInputRef.click()" 
                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-[#2E2E2E] rounded-lg p-4 h-32 bg-slate-50/50 dark:bg-[#1E1E1E]/25 hover:bg-slate-55 dark:hover:bg-slate-900/40 hover:border-slate-350 dark:hover:border-slate-700 transition-all cursor-pointer"
                  >
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mt-1">Drop files here to upload</span>
                    <span class="text-[8px] text-slate-400 dark:text-slate-500 mt-0.5">Up to 8 images</span>
                  </div>
 
                  <input ref="imageInputRef" type="file" @change="onImageFilePicked" class="hidden" accept="image/*" multiple>
                </div>
              </div>
            </div>
 
            <!-- Description -->
            <div class="mt-3">
              <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                Description
                <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                  <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                    Provide a detailed overview of the product specifications and benefits.
                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                  </span>
                </span>
              </label>
              <textarea v-model="form.description" rows="3" class="w-full px-3 py-1.5 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 rounded-md text-sm font-medium transition-all text-slate-800 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-550 outline-none resize-none h-20" placeholder="Enter description..."></textarea>
            </div>
          </div>
 
          <!-- 2. BILLING SECTION -->
          <div v-show="!form.has_variations" class="space-y-3 pt-3 border-t border-slate-100 dark:border-[#2E2E2E] mt-4">
            <div class="border-b border-gray-200 dark:border-[#2E2E2E] pb-2 mb-3">
              <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200">Billing</h2>
            </div>

            <!-- Inline Activation Block Checkboxes -->
            <div class="flex items-center gap-5 mb-3">
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.enabled_for_sale" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Sale Information
              </label>
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.enabled_for_purchase" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Purchase Information
              </label>
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.enabled_for_wholesale" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Wholesale Information
              </label>
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.enabled_for_tax" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Tax
              </label>
            </div>

            <!-- Billing Sub-grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div v-show="form.enabled_for_sale">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Sale Price <span v-if="form.enabled_for_sale && !form.has_variations" class="text-rose-500">*</span>
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      The standard retail selling price offered to consumers.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <input 
                  type="number" 
                  step="0.01" 
                  v-model="form.selling_price" 
                  :disabled="!form.enabled_for_sale || form.has_variations"
                  :class="(!form.enabled_for_sale || form.has_variations) ? 'bg-gray-50 dark:bg-[#1E1E1E]/50 border-gray-200 dark:border-[#2E2E2E] text-gray-450 dark:text-slate-650 cursor-not-allowed' : 'bg-white dark:bg-[#1E1E1E] border-gray-200 dark:border-[#2E2E2E] text-slate-800 dark:text-slate-300 focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25'"
                  class="w-full px-3 py-1.5 rounded-md text-sm font-medium transition-all outline-none border" 
                  :required="form.enabled_for_sale && !form.has_variations"
                />
              </div>
              <div v-show="form.enabled_for_purchase">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Purchase Price <span v-if="form.enabled_for_purchase && !form.has_variations" class="text-rose-500">*</span>
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      The cost price paid to acquire or manufacture the item.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <input 
                  type="number" 
                  step="0.01" 
                  v-model="form.cost_price" 
                  :disabled="!form.enabled_for_purchase || form.has_variations"
                  :class="(!form.enabled_for_purchase || form.has_variations) ? 'bg-gray-50 dark:bg-[#1E1E1E]/50 border-gray-200 dark:border-[#2E2E2E] text-gray-450 dark:text-slate-650 cursor-not-allowed' : 'bg-white dark:bg-[#1E1E1E] border-gray-200 dark:border-[#2E2E2E] text-slate-800 dark:text-slate-300 focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25'"
                  class="w-full px-3 py-1.5 rounded-md text-sm font-medium transition-all outline-none border"
                  :required="form.enabled_for_purchase && !form.has_variations"
                />
              </div>
              <div v-show="form.enabled_for_wholesale">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Wholesale Price <span v-if="form.enabled_for_wholesale && !form.has_variations" class="text-rose-500">*</span>
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      The bulk purchase price offered to business-to-business clients.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <input 
                  type="number" 
                  step="0.01" 
                  v-model="form.wholesale_price" 
                  :disabled="!form.enabled_for_wholesale || form.has_variations"
                  :class="(!form.enabled_for_wholesale || form.has_variations) ? 'bg-gray-50 dark:bg-[#1E1E1E]/50 border-gray-200 dark:border-[#2E2E2E] text-gray-450 dark:text-slate-650 cursor-not-allowed' : 'bg-white dark:bg-[#1E1E1E] border-gray-200 dark:border-[#2E2E2E] text-slate-800 dark:text-slate-300 focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25'"
                  class="w-full px-3 py-1.5 rounded-md text-sm font-medium transition-all outline-none border"
                  :required="form.enabled_for_wholesale && !form.has_variations"
                />
              </div>
              <div v-show="form.enabled_for_tax">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Tax
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      Select applicable tax rates to apply during transaction billing.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <div class="relative" id="tax-multiselect-container">
                  <div 
                    @click="!form.has_variations && (showTaxDropdown = !showTaxDropdown)"
                    :class="(!form.enabled_for_tax || form.has_variations) ? 'bg-gray-50 dark:bg-[#1E1E1E]/50 border-gray-200 dark:border-[#2E2E2E] text-gray-450 dark:text-slate-650 cursor-not-allowed' : 'bg-white dark:bg-[#1E1E1E] border-gray-200 dark:border-[#2E2E2E] text-slate-800 dark:text-slate-300 focus-within:border-slate-300 dark:focus-within:border-slate-700 focus-within:ring-1 focus-within:ring-slate-300/25 cursor-pointer'"
                    class="w-full min-h-[34px] px-3 py-1 border rounded-md text-sm font-medium transition-all outline-none flex flex-wrap items-center gap-1 pr-8"
                  >
                    <div 
                      @click.stop
                      v-for="taxId in (form.taxes || [])" 
                      :key="taxId" 
                      class="bg-slate-800 dark:bg-[#1E1E1E] text-slate-100 dark:text-slate-300 border border-slate-700 dark:border-[#2E2E2E] rounded px-2 py-0.5 text-xs flex items-center gap-1 font-semibold shadow-xs"
                    >
                      <span>{{ getTaxLabel(taxId) }}</span>
                      <button v-if="!form.has_variations" type="button" @click.stop="removeTax(taxId)" class="hover:text-red-350 font-bold focus:outline-none">&times;</button>
                    </div>
                    <span v-if="(!form.taxes || form.taxes.length === 0)" class="text-gray-400 dark:text-slate-550 text-sm">Select Tax Rate(s)</span>
                    
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                    </span>
                  </div>

                  <!-- Dropdown Menu -->
                  <div v-if="showTaxDropdown && !form.has_variations" class="absolute z-50 left-0 mt-1 w-full bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] shadow-lg dark:shadow-slate-950/80 rounded-xl max-h-60 overflow-y-auto p-1 animate-in fade-in zoom-in-95 duration-100">
                    <div 
                      v-for="opt in taxOptions" 
                      :key="opt.value"
                      @click="toggleTaxSelection(opt.value)"
                      class="px-3 py-2 text-xs font-semibold cursor-pointer hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/60 rounded-lg flex items-center justify-between"
                    >
                      <span :class="(form.taxes || []).includes(opt.value) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">{{ opt.label }}</span>
                      <span v-if="(form.taxes || []).includes(opt.value)" class="text-indigo-600 dark:text-indigo-455">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. INVENTORY SECTION -->
          <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-[#2E2E2E] mt-4">
            <div class="border-b border-gray-200 dark:border-[#2E2E2E] pb-2 mb-3">
              <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200">Inventory</h2>
            </div>

            <!-- Toggles Flex Row -->
            <div class="flex flex-wrap items-center gap-5 mb-3">
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.is_returnable" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Returnable Item
              </label>
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.track_inventory" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Track Inventory
              </label>
              <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer select-none">
                <input type="checkbox" v-model="form.has_variations" class="rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" />
                Add Variants
              </label>
            </div>

            <!-- Balanced Grid Structure for Inventory parameters -->
            <div v-show="form.track_inventory" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  SKU
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      Enter a unique Stock Keeping Unit code for tracking inventory.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <input 
                  v-model="form.sku" 
                  type="text" 
                  class="w-full px-3 py-1.5 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 rounded-md text-sm font-medium transition-all text-slate-800 dark:text-slate-300 outline-none" 
                  placeholder="SKU"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Unit
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      Select the standard unit of measurement (e.g. Pcs, Kgs).
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <SystemSelect v-model="form.unit_id" :options="unitOptions" placeholder="Select Unit" />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Barcode
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      Enter the scanner barcode or UPC/EAN code for the item.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <input 
                  v-model="form.barcode" 
                  type="text" 
                  class="w-full px-3 py-1.5 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 rounded-md text-sm font-medium transition-all text-slate-800 dark:text-slate-300 outline-none" 
                  placeholder="Barcode"
                />
              </div>
            </div>

            <!-- Assign Warehouse & Stock Quantity Selection -->
            <div v-show="form.track_inventory && !form.has_variations" class="mt-4 border-t border-slate-100 dark:border-[#2E2E2E] pt-3">
              <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Inventory Stock & Location</h3>
              <div class="space-y-3 bg-slate-50 dark:bg-[#1E1E1E]/20 p-3 rounded-lg border border-slate-200/50 dark:border-[#2E2E2E]/80">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">
                    Assign Warehouse(s)
                    <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                      <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                        Select one or more warehouses where this item is physically stored.
                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                      </span>
                    </span>
                  </label>
                  <div class="relative" id="warehouse-multiselect-container">
                    <button 
                      type="button"
                      @click="!form.has_variations && (showWhDropdown = !showWhDropdown)"
                      :disabled="form.has_variations"
                      :class="form.has_variations ? 'bg-gray-50 dark:bg-[#1E1E1E]/50 border-gray-200 dark:border-[#2E2E2E] text-gray-400 dark:text-slate-650 cursor-not-allowed' : 'bg-white dark:bg-[#1E1E1E] border-gray-200 dark:border-[#2E2E2E] text-slate-800 dark:text-slate-300 focus-within:border-slate-300 dark:focus-within:border-slate-700 focus-within:ring-1 focus-within:ring-slate-300/25 cursor-pointer'"
                      class="w-full min-h-[34px] px-3 py-1 border rounded-md text-sm font-medium transition-all outline-none flex flex-wrap items-center gap-1 pr-8 text-left"
                    >
                      <div 
                        v-for="whId in (form.warehouse_ids || [])" 
                        :key="whId" 
                        class="bg-slate-800 dark:bg-[#1E1E1E] text-slate-100 dark:text-slate-300 border border-slate-700 dark:border-[#2E2E2E] rounded px-2 py-0.5 text-xs flex items-center gap-1 font-semibold shadow-xs"
                      >
                        <span>{{ getRowWarehouseLabel([whId]) }}</span>
                        <button type="button" @click.stop="toggleTopWarehouseSelection(whId)" class="hover:text-red-350 font-bold focus:outline-none">&times;</button>
                      </div>
                      <span v-if="(!form.warehouse_ids || form.warehouse_ids.length === 0)" class="text-slate-400 dark:text-slate-550 text-xs">{{ warehouseOptions.length === 0 ? 'No warehouse created' : 'Assign Warehouse(s)' }}</span>
                      
                      <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                      </span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-if="showWhDropdown && !form.has_variations" class="absolute z-50 left-0 mt-1 w-full bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] shadow-lg dark:shadow-slate-950/80 rounded-xl max-h-60 overflow-y-auto p-1 animate-in fade-in zoom-in-95 duration-100">
                      <div 
                        v-for="opt in warehouseOptions" 
                        :key="opt.value"
                        @click="toggleTopWarehouseSelection(opt.value)"
                        class="px-3 py-2 text-xs font-semibold cursor-pointer hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/60 rounded-lg flex items-center justify-between"
                      >
                        <span :class="(form.warehouse_ids || []).includes(opt.value) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-350'">{{ opt.label }}</span>
                        <span v-if="(form.warehouse_ids || []).includes(opt.value)" class="text-indigo-600 dark:text-indigo-455">
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </span>
                      </div>
                      <div v-if="warehouseOptions.length === 0" class="px-3 py-3 text-xs text-slate-400 dark:text-slate-550 italic text-center">
                        <svg class="w-5 h-5 mx-auto mb-1 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        No warehouse created
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Dynamic Warehouse Stock Allocations when Variations are OFF -->
                <div v-if="!form.has_variations && warehouses.length > 0" class="mt-3 space-y-2">
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    Warehouse Stock Allocations
                    <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                      <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                        Allocate the initial stock quantities and reorder thresholds.
                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                      </span>
                    </span>
                  </label>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div v-for="wh in warehouses" :key="wh.id" class="p-3 bg-white dark:bg-[#1E1E1E] rounded-lg border border-slate-200 dark:border-[#2E2E2E] flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
                      <div class="flex-1 min-w-0">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate block">{{ wh.name }}</span>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">Configure inventory quantities</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <div class="w-24">
                          <label class="block text-[8px] font-black uppercase text-slate-400 dark:text-slate-500 mb-0.5">Stock Qty</label>
                          <input 
                            type="number" 
                            v-model.number="wh.opening_stock"
                            min="0"
                            placeholder="0"
                            class="w-full px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-md text-xs font-bold text-slate-800 dark:text-slate-350 focus:outline-none"
                          />
                        </div>
                        <div class="w-28">
                          <label class="block text-[8px] font-black uppercase text-slate-400 dark:text-slate-500 mb-0.5">Min Stock</label>
                          <input 
                            type="number" 
                            v-model.number="wh.reorder_level"
                            min="0"
                            placeholder="0"
                            class="w-full px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-md text-xs font-bold text-slate-800 dark:text-slate-350 focus:outline-none"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- Dynamic Variation Config -->
            <div v-show="isVariantMode" class="mt-4 border-t border-slate-100 dark:border-[#2E2E2E] pt-3 space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Variations Configuration</h3>
                  <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">Build options to configure product pricing & inventory.</p>
                </div>
              </div>

              <!-- Multi-select searchable dropdown container -->
              <div class="relative md:w-1/2 w-full" id="master-attr-multiselect-container">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">
                  Search or Enter Variation Name
                  <span class="group relative inline-block ml-1.5 cursor-pointer align-middle select-none">
                    <svg class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                      <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:block w-52 bg-[#1E1E1E]/95 backdrop-blur-md text-slate-100 text-[10px] font-semibold leading-relaxed p-2.5 rounded-xl shadow-2xl border border-[#2E2E2E] text-center z-50 normal-case tracking-normal transition-all duration-200">
                      Select pre-created master attributes or type a new one to enable variations.
                      <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900/95"></span>
                    </span>
                  </span>
                </label>
                <div 
                  @click="showMasterAttrDropdown = true"
                  class="w-full min-h-[38px] px-3 py-1.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs font-medium transition-all outline-none flex flex-wrap items-center gap-1.5 cursor-pointer pr-10 focus-within:border-slate-300 dark:focus-within:border-slate-700 focus-within:ring-1 focus-within:ring-slate-300/25 relative"
                >
                  <div 
                    v-for="attr in attributes" 
                    :key="attr.name" 
                    class="bg-slate-800 dark:bg-[#1E1E1E] text-slate-100 dark:text-slate-300 border border-slate-700 dark:border-[#2E2E2E] rounded-lg px-2.5 py-1 text-xs flex items-center gap-1.5 font-semibold shadow-sm hover:bg-slate-700 dark:hover:bg-slate-900 transition-colors"
                  >
                    <span>{{ attr.name }}</span>
                    <button type="button" @click.stop="removeMasterAttribute(attr.name)" class="hover:text-red-350 font-bold focus:outline-none text-[14px] leading-none">&times;</button>
                  </div>
                  <input 
                    type="text" 
                    v-model="masterAttrSearchQuery"
                    @focus="showMasterAttrDropdown = true"
                    @keydown.enter.prevent="addCustomMasterAttribute"
                    placeholder="Type variation name..."
                    class="flex-1 min-w-[120px] bg-transparent border-none outline-none text-slate-850 dark:text-slate-200 p-0 text-xs focus:ring-0"
                  />
                  <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                  </span>
                </div>

                <!-- Dropdown Menu of suggestions -->
                <div v-if="showMasterAttrDropdown" class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] shadow-xl dark:shadow-slate-950/80 rounded-xl max-h-56 overflow-y-auto p-1.5 animate-in fade-in slide-in-from-top-2 duration-150 custom-scrollbar">
                  <div 
                    v-for="sysAttr in getFilteredMasterAttributes()" 
                    :key="sysAttr.id"
                    @click="selectMasterAttribute(sysAttr)"
                    class="px-3 py-2 text-xs font-semibold cursor-pointer hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 rounded-lg flex items-center justify-between text-slate-700 dark:text-slate-300 transition-colors"
                  >
                    <span class="text-slate-800 dark:text-slate-200 font-medium">{{ sysAttr.name }}</span>
                    <span class="text-[9px] text-slate-400 dark:text-slate-500 truncate max-w-[60%]">{{ sysAttr.values.map(v => v.value).join(', ') }}</span>
                  </div>
                  <div 
                    v-if="masterAttrSearchQuery.trim() && !hasExactMatch()"
                    @click="addCustomMasterAttribute"
                    class="px-3 py-2 text-xs font-bold cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-lg text-emerald-600 dark:text-emerald-450 transition-colors"
                  >
                    + Add "{{ masterAttrSearchQuery.trim() }}" as Custom Variation
                  </div>
                  <div v-if="getFilteredMasterAttributes().length === 0 && !masterAttrSearchQuery.trim()" class="px-3 py-2 text-xs text-slate-400 dark:text-slate-550 italic text-center">
                    No variations found
                  </div>
                  <!-- Create Variation option -->
                  <div class="border-t border-slate-100 dark:border-[#2E2E2E] mt-1 pt-1">
                    <div 
                      @click="openVariationModal"
                      class="px-3 py-2 text-xs font-bold cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-lg text-emerald-600 dark:text-emerald-450 transition-colors flex items-center gap-1.5"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                      Create Variation
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sourcing Matrix -->
              <div class="space-y-2">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                  <div class="flex flex-wrap items-center gap-3">
                    <h4 class="text-xs font-bold text-slate-650 dark:text-slate-400 uppercase tracking-wider">Variation Sourcing Matrix</h4>
                    
                    <div class="flex items-center gap-3 bg-slate-55/65 dark:bg-[#1E1E1E]/20 border border-slate-200/50 dark:border-[#2E2E2E] rounded-xl px-2.5 py-1">
                      <label class="flex items-center gap-1.5 text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wider cursor-pointer hover:text-slate-750 dark:hover:text-slate-350 transition-colors select-none">
                        <input 
                          type="checkbox" 
                          v-model="form.show_wholesale_price" 
                          class="w-3.5 h-3.5 rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        Wholesale
                      </label>
                      <span class="text-slate-300 dark:text-slate-800">|</span>
                      <label class="flex items-center gap-1.5 text-[9px] font-bold text-slate-500 dark:text-slate-455 uppercase tracking-wider cursor-pointer hover:text-slate-750 dark:hover:text-slate-350 transition-colors select-none">
                        <input 
                          type="checkbox" 
                          v-model="form.show_tax_rate" 
                          class="w-3.5 h-3.5 rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        Tax Rates
                      </label>
                    </div>
                  </div>
                  <div class="flex flex-wrap items-center gap-2">
                    <div v-if="attributes.length > 0" class="flex flex-wrap items-center gap-2 bg-slate-50 dark:bg-[#1E1E1E]/20 p-1.5 rounded-2xl border border-slate-200/45 dark:border-[#2E2E2E]">
                    <div v-for="(attr, aIdx) in attributes" :key="aIdx" class="flex flex-col relative" :id="`matrix-select-container-${aIdx}`">
                      <span class="text-[8px] font-black uppercase text-slate-400 dark:text-slate-500 px-1 mb-0.5 whitespace-nowrap">Select {{ attr.name }}</span>
                      <button 
                        type="button"
                        @click="toggleMatrixDropdown(aIdx)"
                        class="min-w-[125px] h-[32px] px-3 py-1 border border-slate-200 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] rounded-xl text-xs font-semibold transition-all outline-none flex items-center justify-between gap-1.5 cursor-pointer pr-8 text-left relative whitespace-nowrap"
                      >
                        <div class="flex items-center gap-1 min-w-0 flex-1">
                          <span v-if="(!selectedCombo[attr.name] || selectedCombo[attr.name].length === 0)" class="text-slate-400 dark:text-slate-550 text-[11px] truncate">Choose</span>
                          <span v-else class="text-slate-700 dark:text-slate-300 truncate font-semibold">
                            {{ getMatrixSelectLabel(attr.name) }}
                          </span>
                        </div>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                        </span>
                      </button>

                      <!-- Dropdown Menu (Opens upward) -->
                      <div v-if="activeMatrixDropdown === aIdx" class="absolute bottom-full mb-1.5 z-50 left-0 min-w-[200px] bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] shadow-xl dark:shadow-slate-950/80 rounded-xl max-h-64 overflow-y-auto p-1.5 animate-in fade-in slide-in-from-bottom-2 duration-100 custom-scrollbar">
                        <!-- Existing Values -->
                        <div 
                          v-for="val in attr.values" 
                          :key="val"
                          @click="toggleMatrixComboSelection(attr.name, val)"
                          class="px-2.5 py-1.5 text-xs font-semibold cursor-pointer hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 rounded-lg flex items-center justify-between transition-colors"
                        >
                          <span :class="(selectedCombo[attr.name] || []).includes(val) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">{{ val }}</span>
                          <span v-if="(selectedCombo[attr.name] || []).includes(val)" class="text-indigo-600 dark:text-indigo-455">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" /></svg>
                          </span>
                        </div>
                        <div v-if="!attr.values || attr.values.length === 0" class="px-2.5 py-2 text-[10px] text-slate-400 dark:text-slate-550 italic text-center">No values configured.</div>
                        
                        <!-- Add Custom Value Input -->
                        <div class="border-t border-slate-100 dark:border-[#2E2E2E] mt-1.5 pt-1.5 px-1.5">
                          <div class="flex items-center gap-1">
                            <input 
                              type="text" 
                              v-model="attr.newValueInput"
                              @keydown.enter.prevent="addMatrixCustomValue(aIdx)"
                              placeholder="New value..."
                              class="w-full px-2 py-1 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-[10px] focus:outline-none focus:border-slate-350 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium"
                            />
                            <button 
                              type="button" 
                              @click="addMatrixCustomValue(aIdx)"
                              class="p-1 text-emerald-600 dark:text-emerald-450 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-lg transition-colors"
                            >
                              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    </div>
                    <div class="flex flex-col">
                      <span v-if="attributes.length > 0" class="text-[8px] px-1 mb-0.5 invisible select-none">&nbsp;</span>
                      <button 
                        type="button" 
                        @click="addNewManualRow" 
                        class="h-[30px] px-3.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow transition-all active:scale-95 flex items-center justify-center cursor-pointer"
                      >
                        + Add Combo
                      </button>
                    </div>
                  </div>
                </div>

                <div class="w-full overflow-x-auto overflow-y-auto max-h-[560px] border border-slate-200 dark:border-[#2E2E2E] rounded-xl scrollbar-hidden shadow-sm">
                  <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-200 dark:divide-slate-850">
                    <thead class="bg-slate-50 dark:bg-[#1E1E1E]/50 text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-455">
                      <tr>
                        <th class="px-2.5 py-2 text-left bg-slate-50 dark:bg-[#1E1E1E]/60 z-10 w-40 shadow-[1px_0_0_0_#cbd5e1] dark:shadow-[1px_0_0_0_#334155]">Variant Profile</th>
                        <th class="px-2.5 py-2 text-left min-w-[120px]">SKU *</th>
                        <th class="px-2.5 py-2 text-left min-w-[150px]">Warehouse(s) *</th>
                        <th class="px-2.5 py-2 text-left min-w-[100px]">Purchase Cost ($)</th>
                        <th class="px-2.5 py-2 text-left min-w-[100px]">Retail Price ($) *</th>
                        <th v-if="form.show_wholesale_price" class="px-2.5 py-2 text-left min-w-[100px]">Wholesale ($) *</th>
                        <th v-if="form.show_tax_rate" class="px-2.5 py-2 text-left min-w-[120px]">Tax Rate(s)</th>
                        <th class="px-2.5 py-2 text-left min-w-[180px]">Stock Qty *</th>
                        <th class="px-2.5 py-2 text-left min-w-[120px]">Expiry Date</th>
                        <th class="px-2.5 py-2 text-center bg-slate-50 dark:bg-[#1E1E1E]/60 z-10 shadow-[-1px_0_0_0_#cbd5e1] dark:shadow-[-1px_0_0_0_#334155]">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                      <tr v-if="form.variations.length === 0">
                        <td :colspan="totalMatrixColumns" class="text-center py-4 text-xs text-slate-400 dark:text-slate-550 italic">No variants configured. Click "+ Add Combo" to create variant options.</td>
                      </tr>
                      <tr v-for="(row, index) in form.variations" :key="index" class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <td class="px-2.5 py-1.5 font-bold text-slate-900 dark:text-slate-350 bg-white dark:bg-[#1E1E1E] shadow-[1px_0_0_0_#cbd5e1] dark:shadow-[1px_0_0_0_#334155] z-10">{{ row.name_string }}</td>
                        <td class="px-2.5 py-1.5">
                          <input 
                            type="text" 
                            v-model="row.sku" 
                            required
                            placeholder="SKU" 
                            class="px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs w-full font-semibold focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/25 text-slate-800 dark:text-slate-300 transition-all"
                          />
                        </td>
                        <td class="px-2.5 py-1.5 relative wh-dropdown-cell">
                          <button
                            type="button"
                            :id="'wh-trigger-' + index"
                            @click="toggleRowWhDropdown(index)"
                            class="w-full text-left px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs flex justify-between items-center min-h-[30px]"
                          >
                            <span class="truncate pr-2 text-slate-700 dark:text-slate-350 font-medium">
                              {{ getRowWarehouseLabel(row.warehouse_ids) }}
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-450 shrink-0 transition-transform duration-200" :class="activeRowWhDropdown === index ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                            </svg>
                          </button>
                          
                          <Teleport to="body">
                            <transition
                              enter-active-class="transition duration-200 ease-out"
                              enter-from-class="opacity-0 scale-95"
                              enter-to-class="opacity-100 scale-100"
                              leave-active-class="transition duration-100 ease-in"
                              leave-from-class="opacity-100 scale-100"
                              leave-to-class="opacity-0 scale-95"
                            >
                              <div v-if="activeRowWhDropdown === index" :style="dropdownStyles[index]" class="wh-teleport-dropdown fixed z-[9999] max-h-48 overflow-y-auto bg-white dark:bg-[#1E1E1E] border border-slate-200/80 dark:border-[#2E2E2E] shadow-lg dark:shadow-slate-950/80 rounded-xl p-1.5 custom-scrollbar">
                                <label
                                  v-for="wh in warehouseOptions"
                                  :key="wh.value"
                                  class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition-colors text-xs font-semibold hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-350"
                                  :class="isRowWarehouseSelected(row, wh.value) ? 'bg-indigo-50/60 dark:bg-indigo-950/60 text-indigo-750 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-300'"
                                >
                                  <input
                                    type="checkbox"
                                    :value="wh.value"
                                    :checked="isRowWarehouseSelected(row, wh.value)"
                                    @change="toggleRowWarehouse(index, wh.value)"
                                    class="w-3.5 h-3.5 rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                  />
                                  <span>{{ wh.label }}</span>
                                </label>
                                <div v-if="warehouseOptions.length === 0" class="px-2 py-1.5 text-[10px] text-slate-400 dark:text-slate-550 italic">No warehouses</div>
                              </div>
                            </transition>
                          </Teleport>
                        </td>
                        <td class="px-2.5 py-1.5"><input type="number" v-model="row.cost_price" step="0.01" class="px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs w-full text-amber-600 dark:text-amber-500 focus:outline-none"></td>
                        <td class="px-2.5 py-1.5"><input type="number" v-model="row.retail_price" step="0.01" required class="px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs w-full text-emerald-600 dark:text-emerald-500 focus:outline-none"></td>
                        <td v-if="form.show_wholesale_price" class="px-2.5 py-1.5">
                          <input 
                            type="number" 
                            v-model="row.wholesale_price" 
                            step="0.01" 
                            :required="form.show_wholesale_price" 
                            class="px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs w-full text-slate-900 dark:text-slate-300 font-bold focus:outline-none"
                          />
                        </td>
                        
                        <!-- Multi-select Tax Rates for Variation -->
                        <td v-if="form.show_tax_rate" class="px-2.5 py-1.5 relative tax-dropdown-cell">
                          <button
                            type="button"
                            :id="'tax-trigger-' + index"
                            @click="toggleRowTaxDropdown(index)"
                            class="w-full text-left px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs flex justify-between items-center min-h-[30px]"
                          >
                            <span class="truncate pr-2 text-slate-700 dark:text-slate-350 font-medium">
                              {{ getRowTaxLabel(row.taxes) }}
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform duration-200" :class="activeRowTaxDropdown === index ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                            </svg>
                          </button>
                          
                          <Teleport to="body">
                            <transition
                              enter-active-class="transition duration-200 ease-out"
                              enter-from-class="opacity-0 scale-95"
                              enter-to-class="opacity-100 scale-100"
                              leave-active-class="transition duration-100 ease-in"
                              leave-from-class="opacity-100 scale-100"
                              leave-to-class="opacity-0 scale-95"
                            >
                              <div v-if="activeRowTaxDropdown === index" :style="taxDropdownStyles[index]" class="tax-teleport-dropdown fixed z-[9999] max-h-48 overflow-y-auto bg-white dark:bg-[#1E1E1E] border border-slate-200/80 dark:border-[#2E2E2E] shadow-lg dark:shadow-slate-950/80 rounded-xl p-1.5 custom-scrollbar">
                                <label
                                  v-for="taxOpt in variationTaxOptions"
                                  :key="taxOpt.value"
                                  class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition-colors text-xs font-semibold hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-slate-700 dark:text-slate-350"
                                  :class="isRowTaxSelected(row, taxOpt.value) ? 'bg-indigo-50/60 dark:bg-indigo-950/60 text-indigo-750 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-300'"
                                >
                                  <input
                                    type="checkbox"
                                    :value="taxOpt.value"
                                    :checked="isRowTaxSelected(row, taxOpt.value)"
                                    @change="toggleRowTaxSelection(index, taxOpt.value)"
                                    class="w-3.5 h-3.5 rounded border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                  />
                                  <span>{{ taxOpt.label }}</span>
                                </label>
                                <div v-if="variationTaxOptions.length === 0" class="px-2 py-1.5 text-[10px] text-slate-400 dark:text-slate-550 italic">No taxes</div>
                              </div>
                            </transition>
                          </Teleport>
                        </td>



                        <td class="px-2.5 py-1.5 min-w-[180px]">
                          <div v-if="(row.warehouse_ids || []).length > 0" class="flex flex-col gap-2">
                            <div v-for="whId in row.warehouse_ids" :key="whId" class="flex flex-col gap-1 pb-1.5 border-b border-slate-100/60 dark:border-[#2E2E2E]/60 last:border-0 last:pb-0">
                              <span class="text-[9px] text-slate-500 dark:text-slate-400 font-bold truncate block" :title="getRowWarehouseLabel([whId])">
                                {{ getRowWarehouseLabel([whId]) }}
                              </span>
                              <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                  <span class="text-[8px] text-slate-400 dark:text-slate-550 uppercase font-black">Qty:</span>
                                  <input 
                                    type="number" 
                                    v-model.number="(row.warehouse_stocks = row.warehouse_stocks || {})[whId]" 
                                    @input="updateVariantStockQty(row)"
                                    placeholder="0" 
                                    class="px-1.5 py-0.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded text-[10px] w-12 font-bold text-slate-800 dark:text-slate-350 focus:outline-none focus:border-slate-350 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-350/20"
                                  />
                                </div>
                                <div class="flex items-center gap-1.5">
                                  <span class="text-[8px] text-slate-400 dark:text-slate-550 uppercase font-black">Min:</span>
                                  <input 
                                    type="number" 
                                    v-model.number="(row.warehouse_min_stocks = row.warehouse_min_stocks || {})[whId]" 
                                    @input="updateVariantStockQty(row)"
                                    placeholder="0" 
                                    class="px-1.5 py-0.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded text-[10px] w-12 font-bold text-slate-800 dark:text-slate-350 focus:outline-none focus:border-slate-350 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-350/20"
                                  />
                                </div>
                              </div>
                            </div>
                            <div class="text-[10px] font-black text-slate-600 dark:text-slate-400 mt-1 pt-1 border-t border-slate-100/80 dark:border-[#2E2E2E] flex items-center justify-between">
                              <span>Total Qty/Min:</span>
                              <span class="text-indigo-650 dark:text-indigo-400 font-extrabold">{{ row.stock_qty }} / {{ row.min_stock_alert || 0 }}</span>
                            </div>
                          </div>
                          <span v-else class="text-[10px] text-rose-500 font-bold">Select Wh first</span>
                        </td>
                        <td class="px-2.5 py-1.5"><input type="date" v-model="row.expiry_date" class="px-2 py-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-[11px] w-full text-slate-800 dark:text-slate-300 focus:outline-none"></td>
                        <td class="px-2.5 py-1.5 text-center bg-white dark:bg-[#1E1E1E] shadow-[-1px_0_0_0_#cbd5e1] dark:shadow-[-1px_0_0_0_#334155] z-10"><button type="button" @click="removeRow(index)" class="text-rose-500 font-bold hover:text-rose-700 text-sm">&times;</button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- FOOTER BUTTONS GROUP -->
          <div class="flex items-center justify-end gap-3 mt-6">
            <button 
              type="button" 
              @click="$router.back()"
              class="px-4 py-2 text-sm font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-[#252525] hover:bg-slate-200 dark:hover:bg-slate-700 rounded-md transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="loading" 
              class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm hover:shadow transition-all flex items-center"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Save
            </button>
          </div>
        </form>
      </div>

      <!-- Error Notifications -->
      <transition-group name="list" tag="div" class="fixed top-20 right-6 space-y-2 z-[9999] max-w-sm">
        <div v-for="err in localErrors" :key="err.id" class="bg-[#0f172a] text-slate-50 border border-white/5 px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-semibold animate-in fade-in slide-in-from-top-4 duration-200">
          <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="break-words leading-relaxed select-none">{{ err.text }}</span>
        </div>
      </transition-group>
    </div>

    <!-- Barcode Scanner -->
    <BarcodeScanner v-if="scanning" @scan="onScan" @close="scanning = false" />

    <!-- Options & Attributes Modal -->
    <div v-if="showOptionsModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity">
      <div class="absolute inset-0" @click="showOptionsModal = false"></div>
      <div class="relative w-full max-w-sm p-5 bg-white dark:bg-[#1E1E1E] rounded-2xl border border-slate-200 dark:border-[#2E2E2E] shadow-xl dark:shadow-slate-950/80 space-y-3 z-10 animate-in fade-in zoom-in-95 duration-200">
        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Configure Attribute Group</h4>
        <div>
            <label class="text-[10px] font-bold text-slate-450 dark:text-slate-500 block mb-1">Option Name</label>
            <input type="text" v-model="tempAttrName" placeholder="e.g., Color, Size, Storage" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300">
        </div>
        <div>
            <label class="text-[10px] font-bold text-slate-450 dark:text-slate-500 block mb-1">Option Values (Comma separated)</label>
            <input type="text" v-model="tempAttrValues" @keydown.enter.prevent="addAttributesGroup" placeholder="e.g., Pink, Black, 128GB, 256GB" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300">
        </div>
        <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="showOptionsModal = false" class="px-3 py-1 text-slate-400 dark:text-slate-500 dark:hover:text-slate-400">Cancel</button>
            <button type="button" @click="addAttributesGroup" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow hover:bg-emerald-700 transition-colors">Add & Apply</button>
        </div>
      </div>
    </div>

    <!-- Add New Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity">
      <div class="absolute inset-0" @click="closeCategoryModal"></div>
      <div class="relative w-full max-w-md p-6 bg-white dark:bg-[#1E1E1E] rounded-2xl border border-slate-200 dark:border-[#2E2E2E] shadow-xl dark:shadow-slate-950/80 space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-[#2E2E2E]">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Add New Category</h4>
          <button @click="closeCategoryModal" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 font-bold text-lg">&times;</button>
        </div>
        <div v-if="categoryModalError" class="p-3 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-xs rounded-xl border border-red-200 dark:border-red-900/50 font-medium">
          {{ categoryModalError }}
        </div>
        <div class="space-y-3">
          <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Category Name *</label>
              <input type="text" v-model="newCategoryForm.name" placeholder="e.g., Electronics, Apparel" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-350 font-medium">
          </div>
          <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Parent Category</label>
              <SystemSelect v-model="newCategoryForm.parent_id" :options="parentCategoryOptions" placeholder="None (Top Level)" />
          </div>
          <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Description</label>
              <textarea v-model="newCategoryForm.description" rows="3" placeholder="Describe the category..." class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-350 resize-none font-medium"></textarea>
          </div>
          <div class="flex items-center justify-between py-1">
              <div>
                  <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Is Active</label>
                  <p class="text-[9px] text-slate-400 dark:text-slate-500">Visible and active across system.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                  <input type="checkbox" v-model="newCategoryForm.is_active" class="sr-only peer">
                  <div class="w-8 h-4.5 bg-slate-200 dark:bg-[#252525] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="closeCategoryModal" class="px-3 py-1 text-slate-450 dark:text-slate-500 font-medium hover:text-slate-600 dark:hover:text-slate-400">Cancel</button>
            <button type="button" @click="submitNewCategory" :disabled="submittingCategory" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow hover:bg-emerald-750 transition-colors flex items-center gap-1.5 disabled:opacity-50">
                <svg v-if="submittingCategory" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Create Category
            </button>
        </div>
      </div>
    </div>

    <!-- Add New Unit Modal -->
    <div v-if="showUnitModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity">
      <div class="absolute inset-0" @click="closeUnitModal"></div>
      <div class="relative w-full max-w-md p-6 bg-white dark:bg-[#1E1E1E] rounded-2xl border border-slate-200 dark:border-[#2E2E2E] shadow-xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-[#2E2E2E]">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Add New Unit</h4>
          <button @click="closeUnitModal" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 font-bold text-lg">&times;</button>
        </div>
        <div v-if="unitModalError" class="p-3 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-xs rounded-xl border border-red-200 dark:border-red-900/50 font-medium">
          {{ unitModalError }}
        </div>
        <div class="space-y-3">
          <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Unit Name *</label>
              <input type="text" v-model="newUnitForm.name" placeholder="e.g., Kilogram, Litre, Piece" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium">
          </div>
          <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Short Name / Code *</label>
              <input type="text" v-model="newUnitForm.short_name" placeholder="e.g., KG, LTR, PCS" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium">
          </div>
          <div class="flex items-center justify-between py-1">
              <div>
                  <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Is Active</label>
                  <p class="text-[9px] text-slate-400 dark:text-slate-500">Visible and active across system.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                  <input type="checkbox" v-model="newUnitForm.is_active" class="sr-only peer">
                  <div class="w-8 h-4.5 bg-slate-200 dark:bg-[#252525] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 text-xs">
            <button type="button" @click="closeUnitModal" class="px-3 py-1 text-slate-455 dark:text-slate-500 font-medium hover:text-slate-600 dark:hover:text-slate-400">Cancel</button>
            <button type="button" @click="submitNewUnit" :disabled="submittingUnit" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow hover:bg-emerald-700 transition-colors flex items-center gap-1.5 disabled:opacity-50">
                <svg v-if="submittingUnit" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Create Unit
            </button>
        </div>
      </div>
    </div>

    <!-- Add New Tax Modal -->
    <div v-if="showTaxModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-955/60 backdrop-blur-sm transition-opacity">
      <div class="absolute inset-0" @click="closeTaxModal"></div>
      <div class="relative w-full max-w-lg p-8 bg-white dark:bg-[#1E1E1E] rounded-2xl border border-slate-200 dark:border-[#2E2E2E] shadow-xl space-y-6 z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center">
          <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">New Tax Rule</h4>
          <button @click="closeTaxModal" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div v-if="taxModalError" class="p-3 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 text-xs rounded-xl border border-red-200 dark:border-red-900/50 font-medium">
          {{ taxModalError }}
        </div>
        <div class="space-y-5">
          <div>
            <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1.5">Tax Code / Name *</label>
            <input type="text" v-model="newTaxForm.name" placeholder="e.g., VAT, GST 18%, Sales Tax" class="w-full px-4 py-2.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-sm focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 text-slate-800 dark:text-slate-300 font-medium transition-all">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1.5">Rate / Value *</label>
              <input type="number" step="0.01" v-model="newTaxForm.value" placeholder="0" class="w-full px-4 py-2.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-sm focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 text-slate-800 dark:text-slate-300 font-medium transition-all">
            </div>
            <div>
              <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1.5">Value Type *</label>
              <div class="relative">
                <select v-model="newTaxForm.type" class="w-full px-4 py-2.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-sm focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 focus:ring-1 focus:ring-slate-300/25 text-slate-800 dark:text-slate-300 font-medium appearance-none pr-9 cursor-pointer transition-all">
                  <option value="percentage">Percentage (%)</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </span>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between bg-slate-50 dark:bg-[#1E1E1E] rounded-xl px-4 py-3 border border-slate-100 dark:border-[#2E2E2E]">
            <div>
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block">Is Active</label>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Toggle to enable/disable applying this tax rate on billing</p>
            </div>
            <input type="checkbox" v-model="newTaxForm.is_active" class="w-5 h-5 rounded border-slate-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] text-blue-600 focus:ring-blue-500 cursor-pointer">
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="closeTaxModal" class="px-5 py-2 text-sm text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-300 transition-colors">Cancel</button>
          <button type="button" @click="submitNewTax" :disabled="submittingTax" class="px-6 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl shadow hover:bg-emerald-700 transition-all flex items-center gap-2 disabled:opacity-50">
            <svg v-if="submittingTax" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Create Tax
          </button>
        </div>
      </div>
    </div>

    <!-- Create Variation Modal -->
    <div v-if="showVariationModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-955/60 backdrop-blur-sm transition-opacity">
      <div class="absolute inset-0" @click="closeVariationModal"></div>
      <div class="relative w-full max-w-md p-6 bg-white dark:bg-[#1E1E1E] rounded-2xl border border-slate-200 dark:border-[#2E2E2E] shadow-xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-[#2E2E2E]">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Create Variation</h4>
          <button @click="closeVariationModal" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 font-bold text-lg">&times;</button>
        </div>
        <div v-if="variationModalError" class="p-3 bg-red-50 dark:bg-red-955/30 text-red-700 dark:text-red-400 text-xs rounded-xl border border-red-200 dark:border-red-900/50 font-medium">
          {{ variationModalError }}
        </div>
        <div class="space-y-3">
          <div>
            <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Variation Name *</label>
            <input type="text" v-model="newVariationForm.name" placeholder="e.g., Size, Color, Material" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium">
          </div>
          <div>
            <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block mb-1">Values (Comma separated) *</label>
            <input type="text" v-model="newVariationForm.valuesString" @keydown.enter.prevent="submitNewVariation" placeholder="e.g., Small, Medium, Large, XL" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl text-xs focus:outline-none focus:border-slate-300 dark:focus:border-slate-700 text-slate-800 dark:text-slate-300 font-medium">
            <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1">Separate each value with a comma.</p>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 text-xs">
          <button type="button" @click="closeVariationModal" class="px-3 py-1 text-slate-455 dark:text-slate-500 font-medium hover:text-slate-600 dark:hover:text-slate-400">Cancel</button>
          <button type="button" @click="submitNewVariation" :disabled="submittingVariation" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow hover:bg-emerald-700 transition-colors flex items-center gap-1.5 disabled:opacity-50">
            <svg v-if="submittingVariation" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Create Variation
          </button>
        </div>
      </div>
    </div>

    <!-- Image Editor Modal -->
    <div v-if="showImageEditor" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-955/80 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-3xl bg-white dark:bg-[#1E1E1E] rounded-2xl border border-slate-200 dark:border-[#2E2E2E] shadow-2xl z-10 animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 border-b border-slate-100 dark:border-[#2E2E2E]">
          <div class="flex items-center gap-3">
            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Edit Image</h4>
            <span v-if="editorBatchImages.length > 1" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-[#252525] px-2 py-0.5 rounded-full">
              {{ editorActiveIdx + 1 }} / {{ editorBatchImages.length }}
            </span>
          </div>
          <button @click="closeImageEditor" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Cropper Canvas -->
        <div class="relative flex-1 overflow-hidden bg-slate-900 flex items-center justify-center min-h-[300px] max-h-[45vh]">
          <img ref="cropperImageRef" :src="editorImageSrc" crossorigin="anonymous" @load="onCropperImgLoad" class="max-w-full max-h-full block">
          <!-- Main focused area Star Overlay -->
          <button 
            type="button"
            @click="tempPrimaryIndex = editorActiveIdx"
            class="absolute top-4 left-4 z-10 p-2 bg-slate-900/60 hover:bg-slate-900/80 rounded-full transition-all group/star focus:outline-none shadow-md backdrop-blur-sm"
          >
            <!-- Filled Star -->
            <svg v-if="editorActiveIdx === tempPrimaryIndex" class="w-6 h-6 text-amber-450 fill-amber-400 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
            </svg>
            <!-- Outline Star -->
            <svg v-else class="w-6 h-6 text-white hover:text-amber-400 drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.773-.564-.374-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z"/>
            </svg>
          </button>
        </div>

        <!-- Thumbnail Filmstrip (only when multiple images) -->
        <div v-if="editorBatchImages.length > 1" class="px-4 py-2.5 border-t border-slate-100 dark:border-[#2E2E2E] bg-slate-50/80 dark:bg-[#1E1E1E]/60">
          <div class="flex items-center gap-2 overflow-x-auto scrollbar-hidden py-0.5">
            <div 
              v-for="(bImg, bIdx) in editorBatchImages" 
              :key="bIdx"
              @click="switchEditorImage(bIdx)"
              class="relative w-14 h-14 rounded-lg overflow-hidden cursor-pointer flex-shrink-0 border-2 transition-all duration-150 group"
              :class="bIdx === editorActiveIdx ? 'border-emerald-500 ring-2 ring-emerald-500/30 scale-105' : 'border-slate-200 dark:border-[#2E2E2E] hover:border-slate-300 dark:hover:border-slate-700 opacity-60 hover:opacity-90'"
            >
              <img :src="bImg.thumbSrc" class="w-full h-full object-cover">
              <div v-if="bIdx === editorActiveIdx" class="absolute inset-0 bg-emerald-500/10"></div>
              
              <!-- Star icon on bottom thumbnail -->
              <button 
                type="button"
                @click.stop="tempPrimaryIndex = bIdx"
                class="absolute top-1 left-1 z-10 p-0.5 rounded-full transition-all focus:outline-none"
              >
                <!-- Filled Star -->
                <svg v-if="bIdx === tempPrimaryIndex" class="w-3.5 h-3.5 text-amber-400 fill-amber-400 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <!-- Outline Star (shows on group hover) -->
                <svg v-else class="w-3.5 h-3.5 text-white opacity-0 group-hover:opacity-100 hover:scale-110 hover:text-amber-300 transition-all drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.773-.564-.374-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z"/>
                </svg>
              </button>

              <!-- Delete button on thumbnail -->
              <button 
                v-if="editorBatchImages.length > 1"
                type="button"
                @click.stop="removeEditorImage(bIdx)"
                class="absolute bottom-0.5 left-0.5 z-10 p-0.5 rounded-full bg-rose-600/80 text-white opacity-0 group-hover:opacity-100 hover:bg-rose-600 transition-all focus:outline-none"
                title="Delete this image"
              >
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>

              <span class="absolute bottom-0.5 right-0.5 text-[7px] font-bold text-white bg-slate-900/60 px-1 rounded">{{ bIdx + 1 }}</span>
            </div>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="px-6 py-2.5 border-t border-slate-100 dark:border-[#2E2E2E] flex items-center justify-center gap-2 flex-wrap bg-slate-50/50 dark:bg-[#1E1E1E]/60">
          <button type="button" @click="editorRotate(-90)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Rotate Left">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h1l1-2h2l1 2h1M7 4v4M3 14c0 4 3.5 6 7 6s7-2 7-6-3.5-6-7-6"/></svg>
          </button>
          <button type="button" @click="editorRotate(90)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Rotate Right">
            <svg class="w-5 h-5 -scale-x-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h1l1-2h2l1 2h1M7 4v4M3 14c0 4 3.5 6 7 6s7-2 7-6-3.5-6-7-6"/></svg>
          </button>
          <div class="w-px h-6 bg-slate-200 dark:bg-[#252525] mx-1"></div>
          <button type="button" @click="editorZoom(0.1)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Zoom In">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
          </button>
          <button type="button" @click="editorZoom(-0.1)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Zoom Out">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
          </button>
          <div class="w-px h-6 bg-slate-200 dark:bg-[#252525] mx-1"></div>
          <button type="button" @click="editorFlipH" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Flip Horizontal">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21l-4-4 4-4M17 21l4-4-4-4M3 17h18"/></svg>
          </button>
          <button type="button" @click="editorFlipV" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Flip Vertical">
            <svg class="w-5 h-5 rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21l-4-4 4-4M17 21l4-4-4-4M3 17h18"/></svg>
          </button>
          <div class="w-px h-6 bg-slate-200 dark:bg-[#252525] mx-1"></div>
          <button type="button" @click="editorReset" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 text-slate-600 dark:text-slate-400 transition-colors" title="Reset">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </button>
          <div class="w-px h-6 bg-slate-200 dark:bg-[#252525] mx-1"></div>
          <button v-if="editorTargetIndex >= 0" type="button" @click="removeEditorImage(editorActiveIdx)" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-955/35 text-rose-500 hover:text-rose-650 transition-colors" title="Delete This Image">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>

        <!-- Footer Actions -->
        <div class="flex justify-end gap-3 px-6 py-3 border-t border-slate-100 dark:border-[#2E2E2E]">
          <button type="button" @click="closeImageEditor" class="px-5 py-2 text-sm text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-300 transition-colors">Cancel</button>
          <button type="button" @click="applyAllEdits" class="px-6 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl shadow hover:bg-emerald-700 transition-all">
            Apply
          </button>
        </div>
      </div>
    </div>
  </div>

    <!-- Gallery Viewer Lightbox Modal -->
    <div v-if="showGalleryViewer" class="fixed inset-0 z-[70] flex flex-col items-center justify-center p-4 bg-slate-950/90 backdrop-blur-sm" @click.self="closeGalleryViewer">
      <!-- Centered Lightbox Container -->
      <div class="relative max-w-4xl w-full flex flex-col items-center justify-center bg-slate-900/60 rounded-2xl p-6 border border-white/10 shadow-2xl backdrop-blur-md">
        
        <!-- Header Controls (inside container) -->
        <div class="w-full flex justify-between items-center mb-4">
          <div class="text-white/70 text-[10px] font-extrabold uppercase tracking-wider bg-white/15 backdrop-blur-sm px-3 py-1 rounded-full select-none">
            {{ galleryViewerIndex + 1 }} / {{ productImages.length }}
          </div>
          <div class="flex items-center gap-2">
            <!-- Delete -->
            <button 
              @click="deleteFromGalleryViewer" 
              class="p-2 bg-white/10 hover:bg-rose-600/80 rounded-lg text-white transition-colors" 
              title="Delete this image"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
            <!-- Close -->
            <button @click="closeGalleryViewer" class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <!-- Image Area & Arrow Buttons -->
        <div class="relative w-full flex items-center justify-center">
          <!-- Left Arrow (attached closely inside card edges) -->
          <button 
            v-if="productImages.length > 1"
            @click="galleryViewerIndex = (galleryViewerIndex - 1 + productImages.length) % productImages.length" 
            class="absolute left-2 z-20 p-2.5 bg-black/60 hover:bg-black/80 hover:scale-105 rounded-full text-white transition-all shadow-lg border border-white/10"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
          </button>

          <!-- Focused Image Container -->
          <div class="w-full h-[55vh] flex items-center justify-center px-14">
            <img :src="productImages[galleryViewerIndex]?.preview" class="max-w-full max-h-full object-contain rounded-lg shadow-xl">
          </div>

          <!-- Right Arrow (attached closely inside card edges) -->
          <button 
            v-if="productImages.length > 1"
            @click="galleryViewerIndex = (galleryViewerIndex + 1) % productImages.length" 
            class="absolute right-2 z-20 p-2.5 bg-black/60 hover:bg-black/80 hover:scale-105 rounded-full text-white transition-all shadow-lg border border-white/10"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>

        <!-- Thumbnail Strip inside container at bottom -->
        <div v-if="productImages.length > 1" class="mt-4 flex items-center gap-2 bg-white/5 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/5 overflow-x-auto scrollbar-hidden max-w-full">
          <div 
            v-for="(img, idx) in productImages" 
            :key="idx"
            @click="galleryViewerIndex = idx"
            class="w-10 h-10 rounded-lg overflow-hidden cursor-pointer border-2 transition-all flex-shrink-0"
            :class="idx === galleryViewerIndex ? 'border-white scale-110' : 'border-transparent opacity-50 hover:opacity-80'"
          >
            <img :src="img.preview" class="w-full h-full object-cover">
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted, nextTick } from 'vue';
import SystemSelect from '@/components/common/SystemSelect.vue';
import BarcodeScanner from '@/components/common/BarcodeScanner.vue';
import { useCurrencyStore } from '@/stores/currency';
import axios from 'axios';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const currencyStore = useCurrencyStore();

const props = defineProps({
  initialData: { type: Object, default: () => ({}) },
  title: String,
  subtitle: String,
  loading: Boolean,
  errors: Array
});

const emit = defineEmits(['submit']);

// Hollow favorite star state
const isStarred = ref(false);

// Local errors for toast notifications with auto-hide timer
const localErrors = ref([]);

const showLocalError = (msg) => {
  const errId = Math.random();
  localErrors.value.push({ id: errId, text: msg });
  setTimeout(() => {
    localErrors.value = localErrors.value.filter(e => e.id !== errId);
  }, 5000);
};

watch(() => props.errors, (newErrors) => {
  if (Array.isArray(newErrors) && newErrors.length > 0) {
    localErrors.value = newErrors.map(error => ({
      id: Math.random(),
      text: error
    }));
    
    localErrors.value.forEach(err => {
      setTimeout(() => {
        localErrors.value = localErrors.value.filter(e => e.id !== err.id);
      }, 5000);
    });
  } else {
    localErrors.value = [];
  }
}, { deep: true });

const availableWarehouses = ref(
  props.initialData && props.initialData.warehouses && props.initialData.warehouses.length > 0
    ? props.initialData.warehouses.map(w => ({ id: w.id, name: w.name, is_default: !!w.is_default }))
    : []
);

// Warehouses stock mapping
const getInitialWarehouses = () => {
  if (props.initialData && props.initialData.warehouses && props.initialData.warehouses.length > 0) {
    return props.initialData.warehouses.map(w => ({
      id: w.id,
      name: w.name,
      is_default: !!w.is_default,
      opening_stock: parseInt(w.opening_stock ?? w.stock_qty ?? 0),
      reorder_level: parseInt(w.reorder_level ?? w.min_stock_level ?? 0)
    }));
  }
  if (props.initialData && props.initialData.stock_quantity) {
    return [
      { 
        id: 1, 
        name: 'Main Warehouse', 
        is_default: true, 
        opening_stock: parseInt(props.initialData.stock_quantity) || 0, 
        reorder_level: parseInt(props.initialData.min_stock_level) || 0 
      }
    ];
  }
  return [];
};

const warehouses = ref(getInitialWarehouses());

const sanitizeInitialData = (data) => {
  const defaults = {
    name: '',
    description: '',
    sku: '',
    barcode: '',
    cost_price: '',
    wholesale_price: '',
    selling_price: '',
    stock_quantity: '',
    min_stock_level: '',
    max_stock_level: '',
    unit_of_measure: '',
    unit_id: '',
    track_inventory: true,
    is_active: true,
    status: 'active', // Baseline fallback state is active
    category_id: '',
    category_ids: [],
    weight: '',
    dimensions: '',
    tax_rate: '',
    discount_type: '',
    discount_value: 0,
    supplier_id: '',
    batch_number: '',
    expiry_date: '',
    tags: [],
    has_variations: false,
    variations: [],
    attributes: [],
    image: null,
    image_url: null,
    type: 'product',
    enabled_for_sale: true,
    enabled_for_purchase: true,
    enabled_for_wholesale: false,
    is_returnable: true,
    enabled_for_tax: false,
    taxes: [],
    warehouse_id: '',
    warehouse_ids: [],
    show_wholesale_price: false,
    show_tax_rate: false,
  };

  if (!data) return defaults;

  const sanitized = { ...defaults };
  Object.keys(defaults).forEach(key => {
    if (key in data) {
      if (data[key] !== undefined && data[key] !== null) {
        if (key === 'variations') {
           sanitized[key] = data[key].map(v => ({
             ...v, 
             name_string: v.variation_name_string || v.name_string,
             sku: v.sku || '',
             warehouse_ids: v.warehouse_ids || [],
             warehouse_stocks: v.warehouse_stocks || {},
             warehouse_min_stocks: v.warehouse_min_stocks || {},
             taxes: Array.isArray(v.taxes) ? v.taxes.map(id => Number(id)) : [],
             tags: Array.isArray(v.tags) ? v.tags : []
           }));
        } else if (key === 'category_ids') {
           sanitized[key] = Array.isArray(data[key]) ? data[key] : (data.category_id ? [data.category_id] : []);
        } else if (key === 'taxes') {
           sanitized[key] = Array.isArray(data.taxes) ? data.taxes.map(id => Number(id)) : [];
        } else if (key === 'tags') {
           sanitized[key] = Array.isArray(data.tags) ? data.tags : [];
        } else {
           sanitized[key] = data[key];
        }
      } else {
        if (typeof defaults[key] === 'string') {
          sanitized[key] = '';
        }
      }
    }
  });

  if (data) {
    if (data.warehouses && Array.isArray(data.warehouses)) {
      sanitized.warehouse_ids = data.warehouses.map(w => w.id);
    } else if (data.warehouse_ids) {
      sanitized.warehouse_ids = Array.isArray(data.warehouse_ids) ? data.warehouse_ids : [data.warehouse_ids];
    } else if (data.warehouse_id) {
      sanitized.warehouse_ids = [data.warehouse_id];
    }
    sanitized.warehouse_id = sanitized.warehouse_ids[0] || '';
  }

  // Set enabled fields on edit/load based on data values
  if (data) {
    sanitized.enabled_for_wholesale = data.wholesale_price && parseFloat(data.wholesale_price) > 0 ? true : false;
    sanitized.enabled_for_tax = data.tax_rate !== undefined && data.tax_rate !== null && parseFloat(data.tax_rate) > 0 ? true : false;
    sanitized.enabled_for_sale = data.selling_price && parseFloat(data.selling_price) > 0 ? true : (data.selling_price === undefined ? true : false);
    sanitized.enabled_for_purchase = data.cost_price && parseFloat(data.cost_price) > 0 ? true : (data.cost_price === undefined ? true : false);
  }

  // Backward compatibility check
  if (sanitized.category_id && sanitized.category_ids.length === 0) {
    sanitized.category_ids = [sanitized.category_id];
  }

  if (data && data.variations && Array.isArray(data.variations)) {
    sanitized.variations = data.variations.map(v => ({
      ...v,
      name_string: v.variation_name_string || v.name_string || '',
      sku: v.sku || '',
      taxes: Array.isArray(v.taxes) ? v.taxes.map(id => Number(id)) : [],
      warehouse_ids: Array.isArray(v.warehouse_ids) ? v.warehouse_ids.map(id => Number(id)) : []
    }));
  }

  if (data && data.variations && data.variations.length > 0) {
    sanitized.show_wholesale_price = data.variations.some(v => parseFloat(v.wholesale_price || 0) > 0);
    sanitized.show_tax_rate = data.variations.some(v => (v.taxes && v.taxes.length > 0) || parseFloat(v.tax_rate || 0) > 0);
  } else {
    sanitized.show_wholesale_price = false;
    sanitized.show_tax_rate = false;
  }

  return sanitized;
};

const form = ref(sanitizeInitialData(props.initialData));

// Watch warehouse_ids and availableList to dynamically manage warehouses array (preserving existing stock inputs)
watch(
  [() => form.value.warehouse_ids, availableWarehouses],
  ([whIds, availableList]) => {
    if (form.value.has_variations) return; // Only for standard items (Variations OFF)
    
    const list = whIds || [];
    warehouses.value = list.map(whId => {
      const existing = warehouses.value.find(w => w.id === whId || w.value === whId);
      const whName = (availableList.find(w => w.id === whId) || {}).name || 'Warehouse';
      return {
        id: whId,
        name: whName,
        opening_stock: existing ? parseInt(existing.opening_stock || 0) : 0,
        reorder_level: existing ? parseInt(existing.reorder_level || 0) : 0,
        is_default: existing ? !!existing.is_default : false
      };
    });
  },
  { deep: true, immediate: true }
);

// Watch warehouses deep to sum up total stock_quantity and min_stock_level dynamically
watch(
  warehouses,
  (newWhs) => {
    if (form.value.has_variations) return; // Only for standard items
    
    form.value.stock_quantity = newWhs.reduce((sum, w) => sum + parseInt(w.opening_stock || 0), 0);
    form.value.min_stock_level = newWhs.reduce((sum, w) => sum + parseInt(w.reorder_level || 0), 0);
  },
  { deep: true }
);

const categories = ref([]);
const suppliers = ref([]);
const scanning = ref(false);
const imageInputRef = ref(null);
const newTag = ref('');
const showOptionsModal = ref(false);

// Row warehouse dropdown state
const activeRowWhDropdown = ref(null);
const dropdownStyles = ref({});

const toggleRowWhDropdown = async (index) => {
  if (activeRowWhDropdown.value === index) {
    activeRowWhDropdown.value = null;
  } else {
    activeRowWhDropdown.value = index;
    await nextTick();
    const button = document.getElementById(`wh-trigger-${index}`);
    if (button) {
      const rect = button.getBoundingClientRect();
      dropdownStyles.value[index] = {
        left: `${rect.left}px`,
        bottom: `${window.innerHeight - rect.top + 4}px`,
        width: `${rect.width}px`
      };
    }
  }
};

const getRowWarehouseLabel = (rowWhIds) => {
  const ids = rowWhIds || [];
  if (ids.length === 0) return 'Select Warehouse(s)';
  const names = ids.map(id => {
    const wh = warehouseOptions.value.find(w => w.value === id);
    return wh ? wh.label : 'Unknown';
  });
  if (names.length <= 1) return names.join(', ');
  return `${names[0]} +${names.length - 1} more`;
};

const updateVariantStockQty = (row) => {
  if (!row.warehouse_stocks) {
    row.warehouse_stocks = {};
  }
  if (!row.warehouse_min_stocks) {
    row.warehouse_min_stocks = {};
  }
  let total = 0;
  row.warehouse_ids.forEach(whId => {
    total += parseInt(row.warehouse_stocks[whId] || 0);
  });
  row.stock_qty = total;

  let totalMin = 0;
  row.warehouse_ids.forEach(whId => {
    totalMin += parseInt(row.warehouse_min_stocks[whId] || 0);
  });
  row.min_stock_alert = totalMin;
};

const isRowWarehouseSelected = (row, whId) => {
  return (row.warehouse_ids || []).some(id => Number(id) === Number(whId));
};

const toggleRowWarehouse = (index, whId) => {
  const row = form.value.variations[index];
  if (!row) return;
  if (!row.warehouse_ids) {
    row.warehouse_ids = [];
  }
  const targetId = Number(whId);
  const idx = row.warehouse_ids.findIndex(id => Number(id) === targetId);
  if (idx > -1) {
    row.warehouse_ids.splice(idx, 1);
    if (row.warehouse_stocks) {
      delete row.warehouse_stocks[targetId];
      delete row.warehouse_stocks[String(targetId)];
    }
    if (row.warehouse_min_stocks) {
      delete row.warehouse_min_stocks[targetId];
      delete row.warehouse_min_stocks[String(targetId)];
    }
  } else {
    row.warehouse_ids.push(targetId);
    if (!row.warehouse_stocks) row.warehouse_stocks = {};
    if (!row.warehouse_min_stocks) row.warehouse_min_stocks = {};
    row.warehouse_stocks[targetId] = 0;
    row.warehouse_min_stocks[targetId] = 0;
  }
  updateVariantStockQty(row);
};

const closeRowWhDropdownOnOutsideClick = (e) => {
  if (activeRowWhDropdown.value !== null) {
    const isInsideTrigger = e.target.closest('.wh-dropdown-cell');
    const isInsideDropdown = e.target.closest('.wh-teleport-dropdown');
    if (!isInsideTrigger && !isInsideDropdown) {
      activeRowWhDropdown.value = null;
    }
  }
};

const handleScrollClose = () => {
  activeRowWhDropdown.value = null;
  activeRowTaxDropdown.value = null;
  activeRowTagDropdown.value = null;
};

const systemAttributes = ref([]);

const sanitizeAttributes = (attrs) => {
  const list = Array.isArray(attrs) ? attrs : [];
  return list.map(attr => ({
    name: attr.name || '',
    values: Array.isArray(attr.values) ? attr.values.map(v => typeof v === 'object' ? v.value : v) : [],
    valuesString: Array.isArray(attr.values) ? attr.values.join(', ') : '',
    showSearch: false,
    showValuesDropdown: false,
    newValueInput: ''
  }));
};

const attributes = ref(sanitizeAttributes(props.initialData?.attributes));
const tempAttrName = ref('');
const tempAttrValues = ref('');

const selectedCombo = ref({});

const hydrateAttributesAndCombos = () => {
  let attrs = sanitizeAttributes(props.initialData?.attributes);

  const variations = form.value?.variations || [];
  if (form.value?.has_variations && variations.length > 0) {
    const colValuesMap = {};
    variations.forEach(v => {
      const nameStr = v.variation_name_string || v.name_string || '';
      const parts = nameStr.split(' / ').map(p => p.trim());
      parts.forEach((part, colIdx) => {
        if (part) {
          if (!colValuesMap[colIdx]) colValuesMap[colIdx] = new Set();
          colValuesMap[colIdx].add(part);
        }
      });
    });

    const numCols = Object.keys(colValuesMap).length;

    if (attrs.length < numCols) {
      for (let colIdx = attrs.length; colIdx < numCols; colIdx++) {
        const valuesArr = Array.from(colValuesMap[colIdx] || []);
        let matchedName = null;
        if (Array.isArray(systemAttributes.value)) {
          const sysMatch = systemAttributes.value.find(sys => {
            const sysVals = (sys.values || []).map(v => (typeof v === 'object' ? v.value : v).toLowerCase());
            return valuesArr.some(v => sysVals.includes(v.toLowerCase()));
          });
          if (sysMatch) {
            matchedName = sysMatch.name;
          }
        }

        if (!matchedName) {
          matchedName = `Option ${colIdx + 1}`;
        }

        attrs.push({
          name: matchedName,
          values: valuesArr,
          valuesString: valuesArr.join(', '),
          showSearch: false,
          showValuesDropdown: false,
          newValueInput: ''
        });
      }
    }

    attrs.forEach((attr, colIdx) => {
      if (!attr.values) attr.values = [];
      if (!selectedCombo.value[attr.name]) selectedCombo.value[attr.name] = [];

      const colValues = Array.from(colValuesMap[colIdx] || []);
      colValues.forEach(val => {
        const existingValInAttr = attr.values.find(v => v.toLowerCase() === val.toLowerCase());
        if (!existingValInAttr) {
          attr.values.push(val);
        }
        const valToPush = existingValInAttr || val;
        if (!selectedCombo.value[attr.name].includes(valToPush)) {
          selectedCombo.value[attr.name].push(valToPush);
        }
      });
    });
  }

  attributes.value = attrs;
};

// Initial hydration call
hydrateAttributesAndCombos();

const showMasterAttrDropdown = ref(false);
const masterAttrSearchQuery = ref('');

const getFilteredMasterAttributes = () => {
  const query = masterAttrSearchQuery.value.toLowerCase().trim();
  const list = Array.isArray(systemAttributes.value) ? systemAttributes.value : [];
  
  // Filter out already selected attributes
  const unselected = list.filter(sysAttr => 
    !attributes.value.some(a => a.name.toLowerCase() === sysAttr.name.toLowerCase())
  );
  
  if (!query) return unselected;
  return unselected.filter(sysAttr => 
    sysAttr.name.toLowerCase().includes(query)
  );
};

const hasExactMatch = () => {
  const query = masterAttrSearchQuery.value.toLowerCase().trim();
  if (!query) return false;
  
  const allAttrs = Array.isArray(systemAttributes.value) ? systemAttributes.value : [];
  return allAttrs.some(sysAttr => sysAttr.name.toLowerCase() === query) ||
         attributes.value.some(a => a.name.toLowerCase() === query);
};

const selectMasterAttribute = (sysAttr) => {
  const values = Array.isArray(sysAttr.values) ? sysAttr.values.map(v => typeof v === 'object' ? v.value : v) : [];
  attributes.value.push({
    name: sysAttr.name,
    values: values,
    valuesString: values.join(', '),
    showSearch: false,
    showValuesDropdown: false,
    newValueInput: ''
  });
  masterAttrSearchQuery.value = '';
  showMasterAttrDropdown.value = false;
};

const addCustomMasterAttribute = () => {
  const query = masterAttrSearchQuery.value.trim();
  if (!query) return;
  
  const isDuplicate = attributes.value.some(a => a.name.toLowerCase() === query.toLowerCase());
  if (isDuplicate) {
    showLocalError(`The variation "${query}" is already selected.`);
    return;
  }
  
  attributes.value.push({
    name: query,
    values: [],
    valuesString: '',
    showSearch: false,
    showValuesDropdown: false,
    newValueInput: ''
  });
  masterAttrSearchQuery.value = '';
  showMasterAttrDropdown.value = false;
};

const removeMasterAttribute = (name) => {
  attributes.value = attributes.value.filter(a => a.name.toLowerCase() !== name.toLowerCase());
  delete selectedCombo.value[name];
};

const closeMasterAttrDropdownOnOutsideClick = (e) => {
  const el = document.getElementById('master-attr-multiselect-container');
  if (el && !el.contains(e.target)) {
    showMasterAttrDropdown.value = false;
  }
};

const addMatrixCustomValue = (aIdx) => {
  const attr = attributes.value[aIdx];
  if (!attr) return;
  const val = (attr.newValueInput || '').trim();
  if (!val) return;
  if (!attr.values) {
    attr.values = [];
  }
  if (!attr.values.some(v => v.toLowerCase() === val.toLowerCase())) {
    attr.values.push(val);
  }
  
  // Auto-select this newly added value for the combo
  toggleMatrixComboSelection(attr.name, val);
  
  attr.newValueInput = '';
};

const activeMatrixDropdown = ref(null);
const toggleMatrixDropdown = (index) => {
  if (activeMatrixDropdown.value === index) {
    activeMatrixDropdown.value = null;
  } else {
    activeMatrixDropdown.value = index;
  }
};

const toggleMatrixComboSelection = (attrName, val) => {
  if (!selectedCombo.value[attrName]) {
    selectedCombo.value[attrName] = [];
  }
  const idx = selectedCombo.value[attrName].indexOf(val);
  if (idx > -1) {
    selectedCombo.value[attrName].splice(idx, 1);
  } else {
    selectedCombo.value[attrName].push(val);
  }
};

const getMatrixSelectLabel = (attrName) => {
  const selected = selectedCombo.value[attrName] || [];
  if (selected.length === 0) return 'Choose';
  if (selected.length === 1) return selected[0];
  return `${selected[0]} (+${selected.length - 1})`;
};

const closeMatrixDropdownOnOutsideClick = (e) => {
  if (activeMatrixDropdown.value !== null) {
    const el = document.getElementById(`matrix-select-container-${activeMatrixDropdown.value}`);
    if (el && !el.contains(e.target)) {
      activeMatrixDropdown.value = null;
    }
  }
};

const cartesianProduct = (arrays) => {
  return arrays.reduce((acc, curr) => {
    return acc.flatMap(d => curr.map(e => [d, e].flat()));
  }, [[]]);
};

const addNewManualRow = () => {
    if (attributes.value.length === 0) {
        showLocalError('Please select or enter a variation name first!');
        return;
    }

    const missingSelection = attributes.value.some(attr => !selectedCombo.value[attr.name] || selectedCombo.value[attr.name].length === 0);
    if (missingSelection) {
        showLocalError('Please select at least one value for all attribute types before adding!');
        return;
    }

    const selectedValuesArrays = attributes.value.map(attr => selectedCombo.value[attr.name]);
    const combos = cartesianProduct(selectedValuesArrays);

    let addedCount = 0;
    let duplicateCount = 0;

    combos.forEach(combo => {
        const nameString = combo.join(' / ');
        const isDuplicate = form.value.variations.some(r => r.name_string === nameString);
        if (isDuplicate) {
            duplicateCount++;
        } else {
            addSingleVariantRow(nameString);
            addedCount++;
        }
    });

    if (duplicateCount > 0 && addedCount === 0) {
        alert('All selected variation combinations have already been added to the matrix.');
    } else if (duplicateCount > 0) {
        alert(`Added ${addedCount} combinations. ${duplicateCount} duplicate combinations were skipped.`);
    }

    // Clear selections after adding
    attributes.value.forEach(attr => {
        selectedCombo.value[attr.name] = [];
    });
};

const addSingleVariantRow = (nameString) => {
    const rowWhs = availableWarehouses.value.length > 0 ? [availableWarehouses.value.find(w => w.is_default)?.id || availableWarehouses.value[0].id] : [];
    const whStocks = {};
    const whMinStocks = {};
    rowWhs.forEach(id => {
      whStocks[id] = 0;
      whMinStocks[id] = 0;
    });

    const baseSku = form.value.sku ? `${form.value.sku}-` : 'SKU-';
    const variantSuffix = nameString ? nameString.replace(/[^a-zA-Z0-9]/g, '').substring(0, 8).toUpperCase() : '';
    const randPart = Math.random().toString(36).substring(2, 6).toUpperCase();
    const generatedSku = `${baseSku}${variantSuffix ? variantSuffix + '-' : ''}${randPart}`;

    form.value.variations.push({
        name_string: nameString,
        sku: generatedSku,
        barcode: '',
        cost_price: '',
        retail_price: '',
        wholesale_price: '',
        tax_rate: 0,
        taxes: [],
        tags: [],
        discount_type: '',
        discount_value: '',
        stock_qty: 0,
        min_stock_alert: 0,
        unit_of_measure: '',
        expiry_date: '',
        warehouse_ids: rowWhs,
        warehouse_stocks: whStocks,
        warehouse_min_stocks: whMinStocks
    });
};

const removeRow = (idx) => {
    form.value.variations.splice(idx, 1);
};

const statusOptions = computed(() => {
  // If editing an existing product (initialData.id exists)
  if (props.initialData && props.initialData.id) {
    if (props.initialData.status === 'active' || props.initialData.status === 'inactive') {
      return [
        { value: 'inactive', label: 'Inactive' },
        { value: 'active', label: 'Active' }
      ];
    }
  }
  return [
    { value: 'draft', label: 'Draft' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'active', label: 'Active' }
  ];
});

const setStatus = (val) => {
  form.value.status = val;
  localStorage.setItem('pos_item_form_status', val);
};

// Multi-select Category Badge & Tag logic
const showCategoryDropdown = ref(false);

const getCategoryLabel = (id) => {
  const list = Array.isArray(categories.value)
    ? categories.value
    : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
  const cat = list.find(c => c.id === id || String(c.id) === String(id));
  return cat ? cat.name : id;
};

const toggleCategorySelection = (val) => {
  if (val === 'add_new_category') {
    openCategoryModal();
    showCategoryDropdown.value = false;
    return;
  }
  const idx = form.value.category_ids.indexOf(val);
  if (idx > -1) {
    form.value.category_ids.splice(idx, 1);
  } else {
    form.value.category_ids.push(val);
  }
  // Sync fallback category_id
  form.value.category_id = form.value.category_ids[0] || '';
};

const removeCategory = (val) => {
  const idx = form.value.category_ids.indexOf(val);
  if (idx > -1) {
    form.value.category_ids.splice(idx, 1);
    form.value.category_id = form.value.category_ids[0] || '';
  }
};

const closeCategoryDropdownOnOutsideClick = (e) => {
  const el = document.getElementById('category-multiselect-container');
  if (el && !el.contains(e.target)) {
    showCategoryDropdown.value = false;
  }
};

const taxes = ref([]);
const taxOptions = computed(() => {
  const list = Array.isArray(taxes.value) ? taxes.value : [];
  const options = list.map(t => ({ label: `${t.name} (${t.value}%)`, value: t.id }));
  options.unshift({ label: 'No Tax (0%)', value: 0 });
  options.push({ label: '+ ADD New Tax', value: 'add_new_tax' });
  return options;
});

// Multi-select Tax Rates logic
const showTaxDropdown = ref(false);

const initializeTaxRates = (taxRateVal) => {
  if (!taxRateVal || parseFloat(taxRateVal) === 0) {
    return [];
  }
  const target = parseFloat(taxRateVal);
  const list = Array.isArray(taxes.value) ? taxes.value : [];
  
  // 1. Try to find a single tax that matches
  const singleMatch = list.find(t => parseFloat(t.value) === target);
  if (singleMatch) {
    return [singleMatch.id];
  }
  
  // 2. Try to find a combination of two taxes that match
  for (let i = 0; i < list.length; i++) {
    for (let j = i + 1; j < list.length; j++) {
      if (parseFloat(list[i].value) + parseFloat(list[j].value) === target) {
        return [list[i].id, list[j].id];
      }
    }
  }
  
  // 3. Fallback
  return [];
};

const syncTaxRates = () => {
  if (form.value && form.value.tax_rate !== undefined && form.value.tax_rate !== null) {
    form.value.taxes = initializeTaxRates(form.value.tax_rate);
  }
};

const getTaxLabel = (id) => {
  const list = Array.isArray(taxes.value) ? taxes.value : [];
  const tax = list.find(t => t.id === id || Number(t.id) === Number(id));
  return tax ? `${tax.name} (${tax.value}%)` : `${id}%`;
};

const toggleTaxSelection = (val) => {
  if (val === 'add_new_tax') {
    showTaxDropdown.value = false;
    openTaxModal();
    return;
  }
  if (val === 0) {
    form.value.taxes = [];
    form.value.tax_rate = 0;
    return;
  }
  if (!form.value.taxes) {
    form.value.taxes = [];
  }
  const idx = form.value.taxes.indexOf(val);
  if (idx > -1) {
    form.value.taxes.splice(idx, 1);
  } else {
    form.value.taxes.push(val);
  }
  const sum = form.value.taxes.reduce((total, id) => {
    const tax = taxes.value.find(t => t.id === id || Number(t.id) === Number(id));
    return total + (tax ? parseFloat(tax.value) : 0);
  }, 0);
  form.value.tax_rate = sum;
};

const removeTax = (val) => {
  if (!form.value.taxes) return;
  const idx = form.value.taxes.indexOf(val);
  if (idx > -1) {
    form.value.taxes.splice(idx, 1);
    const sum = form.value.taxes.reduce((total, id) => {
      const tax = taxes.value.find(t => t.id === id || Number(t.id) === Number(id));
      return total + (tax ? parseFloat(tax.value) : 0);
    }, 0);
    form.value.tax_rate = sum;
  }
};

const closeTaxDropdownOnOutsideClick = (e) => {
  const el = document.getElementById('tax-multiselect-container');
  if (el && !el.contains(e.target)) {
    showTaxDropdown.value = false;
  }
};

watch([taxes, () => form.value.tax_rate], () => {
  if (form.value && (!form.value.taxes || form.value.taxes.length === 0) && form.value.tax_rate > 0) {
    syncTaxRates();
  }
}, { immediate: true });

const showWhDropdown = ref(false);

const toggleTopWarehouseSelection = (val) => {
  if (!form.value.warehouse_ids) {
    form.value.warehouse_ids = [];
  }
  const idx = form.value.warehouse_ids.indexOf(val);
  if (idx > -1) {
    form.value.warehouse_ids.splice(idx, 1);
  } else {
    form.value.warehouse_ids.push(val);
  }
  // Sync fallback warehouse_id
  form.value.warehouse_id = form.value.warehouse_ids[0] || '';
};

const closeWarehouseDropdownOnOutsideClick = (e) => {
  const el = document.getElementById('warehouse-multiselect-container');
  if (el && !el.contains(e.target)) {
    showWhDropdown.value = false;
  }
};

onMounted(() => {
  window.addEventListener('click', closeCategoryDropdownOnOutsideClick);
  window.addEventListener('click', closeMasterAttrDropdownOnOutsideClick);
  window.addEventListener('click', closeRowWhDropdownOnOutsideClick);
  window.addEventListener('click', closeWarehouseDropdownOnOutsideClick);
  window.addEventListener('click', closeTaxDropdownOnOutsideClick);
  window.addEventListener('click', closeTagDropdownOnOutsideClick);
  window.addEventListener('click', closeRowTaxDropdownOnOutsideClick);
  window.addEventListener('click', closeRowTagDropdownOnOutsideClick);
  window.addEventListener('click', closeMatrixDropdownOnOutsideClick);
  window.addEventListener('scroll', handleScrollClose, true);
  // Restore saved status if present
  const savedStatus = localStorage.getItem('pos_item_form_status');
  if (savedStatus) {
    form.value.status = savedStatus;
  }
});

onUnmounted(() => {
  window.removeEventListener('click', closeCategoryDropdownOnOutsideClick);
  window.removeEventListener('click', closeMasterAttrDropdownOnOutsideClick);
  window.removeEventListener('click', closeRowWhDropdownOnOutsideClick);
  window.removeEventListener('click', closeWarehouseDropdownOnOutsideClick);
  window.removeEventListener('click', closeTaxDropdownOnOutsideClick);
  window.removeEventListener('click', closeTagDropdownOnOutsideClick);
  window.removeEventListener('click', closeRowTaxDropdownOnOutsideClick);
  window.removeEventListener('click', closeRowTagDropdownOnOutsideClick);
  window.removeEventListener('click', closeMatrixDropdownOnOutsideClick);
  window.removeEventListener('scroll', handleScrollClose, true);
});

const categoryOptions = computed(() => {
  const list = Array.isArray(categories.value)
    ? categories.value
    : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
  const options = list.map(c => ({ label: c?.name || '', value: c?.id || '' }));
  options.push({ label: '+ ADD New Category', value: 'add_new_category' });
  return options;
});

const parentCategoryOptions = computed(() => {
  const list = Array.isArray(categories.value)
    ? categories.value
    : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
  return [
    { label: 'None (Top Level)', value: '' },
    ...list.map(c => ({ label: c?.name || '', value: c?.id || '' }))
  ];
});

const units = ref([]);

const unitOptions = computed(() => {
  const list = Array.isArray(units.value) ? units.value : [];
  const options = list.map(u => ({ label: `${u?.name} (${u?.short_name})`, value: u?.id }));
  options.push({ label: '+ ADD New Unit', value: 'add_new_unit' });
  return options;
});



const warehouseOptions = computed(() => {
  const list = Array.isArray(availableWarehouses.value) ? availableWarehouses.value : [];
  return list.map(w => ({ label: w.name, value: w.id }));
});

onMounted(async () => {
  try {
    const [catResponse, supResponse, unitResponse, taxResponse, tagResponse, whResponse, attrResponse] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/suppliers'),
      axios.get('/api/units'),
      axios.get('/api/taxes').catch(() => ({ data: [] })),
      axios.get('/api/tags').catch(() => ({ data: [] })),
      axios.get('/api/warehouses').catch(() => ({ data: [] })),
      axios.get('/api/attributes').catch(() => ({ data: [] }))
    ]);
    categories.value = catResponse?.data || [];
    suppliers.value = supResponse?.data || [];
    units.value = unitResponse?.data || [];
    taxes.value = taxResponse?.data || [];
    allTags.value = tagResponse?.data || [];
    availableWarehouses.value = whResponse?.data || [];
    systemAttributes.value = attrResponse?.data || [];
    
    // Re-hydrate attributes once systemAttributes are loaded
    hydrateAttributesAndCombos();

    if ((!form.value.warehouse_ids || form.value.warehouse_ids.length === 0) && availableWarehouses.value.length > 0) {
      const defaultWh = availableWarehouses.value.find(w => w.is_default) || availableWarehouses.value[0];
      if (defaultWh) {
        form.value.warehouse_ids = [defaultWh.id];
        form.value.warehouse_id = defaultWh.id;
      }
    }
  } catch (error) {
    console.error('Error fetching dynamic data:', error);
  }
});

const getInitialProductImages = (data) => {
  if (!data) return [];
  const list = [];
  const addUrl = (rawUrl) => {
    if (!rawUrl || typeof rawUrl !== 'string') return;
    if (rawUrl.includes('Temp') || rawUrl.includes('.tmp')) return;
    const fullUrl = rawUrl.startsWith('/') || rawUrl.startsWith('http') ? rawUrl : '/' + rawUrl;
    if (!list.some(item => item.preview === fullUrl)) {
      list.push({
        file: null,
        preview: fullUrl,
        isExisting: true,
        url: fullUrl
      });
    }
  };

  if (Array.isArray(data.images) && data.images.length > 0) {
    data.images.forEach(img => {
      if (typeof img === 'string') {
        addUrl(img);
      } else if (img && typeof img === 'object') {
        addUrl(img.url || img.path || img.preview || img.image);
      }
    });
  } else if (Array.isArray(data.media) && data.media.length > 0) {
    data.media.forEach(m => {
      if (m && typeof m === 'object') {
        addUrl(m.url || m.path || m.original_url);
      } else if (typeof m === 'string') {
        addUrl(m);
      }
    });
  } else if (data.image_url || data.image) {
    addUrl(data.image_url || data.image);
  }

  return list;
};

// ===== Multi-Image + Image Editor Logic =====
const primaryImageIndex = ref(0);
const tempPrimaryIndex = ref(0);
const productImages = ref(getInitialProductImages(props.initialData)); // Array of { file: File|Blob|null, preview: string, isExisting?: boolean, url?: string }

const syncFormImages = () => {
  if (productImages.value.length > 0) {
    if (primaryImageIndex.value >= productImages.value.length) {
      primaryImageIndex.value = 0;
    }
    const primaryImg = productImages.value[primaryImageIndex.value];
    form.value.image = primaryImg.file ? primaryImg.file : (primaryImg.isExisting ? primaryImg.url : null);
    form.value.image_url = primaryImg.preview;
    form.value.images = productImages.value
      .map(img => img.file || img.url || img.preview)
      .filter(src => src !== null && src !== undefined && src !== 'null' && src !== 'undefined' && src !== '');
    form.value.primary_image_index = primaryImageIndex.value;
  } else {
    form.value.image = null;
    form.value.image_url = null;
    form.value.images = [];
    form.value.primary_image_index = 0;
  }
};

watch(() => props.initialData, (newData) => {
  if (newData && Object.keys(newData).length > 0) {
    form.value = sanitizeInitialData(newData);
    productImages.value = getInitialProductImages(newData);
    hydrateAttributesAndCombos();
    syncFormImages();
  }
}, { deep: true, immediate: true });
const showImageEditor = ref(false);
const editorImageSrc = ref('');
const editorTargetIndex = ref(-1); // -1 = adding new batch, >= 0 = editing existing cohort
const editorActiveIdx = ref(0);
const editorBatchImages = ref([]); // Array of { file, originalSrc, croppedBlob, thumbSrc, isExisting, existingIndex }
const cropperImageRef = ref(null);
let cropperInstance = null;

const onImageFilePicked = (event) => {
  const files = Array.from(event.target.files || []);
  if (files.length === 0) return;
  
  const remaining = 8 - productImages.value.length;
  const toProcess = files.slice(0, remaining);
  
  if (toProcess.length === 0) {
    showLocalError('Maximum 8 images allowed.');
    return;
  }

  // Build the batch images array
  const newBatch = toProcess.map(file => {
    const src = URL.createObjectURL(file);
    return {
      file: file,
      originalSrc: src,
      croppedBlob: null,
      thumbSrc: src,
      isExisting: false,
      existingIndex: null
    };
  });

  editorBatchImages.value = newBatch;
  editorTargetIndex.value = -1;
  editorActiveIdx.value = 0;
  tempPrimaryIndex.value = primaryImageIndex.value; // default to existing primary index
  editorImageSrc.value = newBatch[0].originalSrc;
  showImageEditor.value = true;
  initCropper();
  
  if (imageInputRef.value) imageInputRef.value.value = '';
};

const openEditorForExisting = (idx = 0) => {
  if (!productImages.value || productImages.value.length === 0) return;
  const targetIdx = Math.max(0, Math.min(idx, productImages.value.length - 1));
  editorTargetIndex.value = targetIdx;
  
  // Load ALL existing product images into the batch for easy navigation
  editorBatchImages.value = productImages.value.map((img, index) => {
    return {
      file: img.file,
      originalSrc: img.preview,
      croppedBlob: null,
      thumbSrc: img.preview,
      isExisting: !!img.isExisting,
      existingIndex: index
    };
  });

  editorActiveIdx.value = targetIdx;
  tempPrimaryIndex.value = primaryImageIndex.value;
  editorImageSrc.value = editorBatchImages.value[targetIdx].originalSrc;
  showImageEditor.value = true;
  initCropper();
};

const onCropperImgLoad = () => {
  initCropper();
};

const initCropper = async () => {
  await nextTick();
  destroyCropper();
  await nextTick();
  
  const imgEl = cropperImageRef.value;
  if (!imgEl || !imgEl.src) return;

  const startCropper = () => {
    destroyCropper();
    if (!cropperImageRef.value) return;
    try {
      cropperInstance = new Cropper(cropperImageRef.value, {
        viewMode: 1,
        dragMode: 'move',
        aspectRatio: NaN,
        autoCropArea: 0.9,
        responsive: true,
        restore: false,
        guides: true,
        center: true,
        highlight: true,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: true,
        background: true,
        checkOrientation: false,
        checkCrossOrigin: false,
      });
    } catch (e) {
      console.warn('Failed to initialize Cropper instance:', e);
    }
  };

  if (imgEl.complete && imgEl.naturalWidth > 0) {
    startCropper();
  }
};

const destroyCropper = () => {
  if (cropperInstance) {
    cropperInstance.destroy();
    cropperInstance = null;
  }
};

const editorRotate = (deg) => {
  if (cropperInstance) cropperInstance.rotate(deg);
};

const editorZoom = (ratio) => {
  if (cropperInstance) cropperInstance.zoom(ratio);
};

const editorFlipH = () => {
  if (cropperInstance) {
    const data = cropperInstance.getData();
    cropperInstance.scaleX(data.scaleX === -1 ? 1 : -1);
  }
};

const editorFlipV = () => {
  if (cropperInstance) {
    const data = cropperInstance.getData();
    cropperInstance.scaleY(data.scaleY === -1 ? 1 : -1);
  }
};

const editorReset = () => {
  if (cropperInstance) cropperInstance.reset();
};

const saveCurrentCrop = () => {
  return new Promise((resolve) => {
    if (!cropperInstance) {
      resolve();
      return;
    }
    try {
      const canvas = cropperInstance.getCroppedCanvas({
        maxWidth: 1200,
        maxHeight: 1200,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
      });
      if (!canvas) {
        resolve();
        return;
      }
      canvas.toBlob((blob) => {
        if (blob && editorBatchImages.value[editorActiveIdx.value]) {
          const activeImg = editorBatchImages.value[editorActiveIdx.value];
          if (activeImg.croppedBlob && activeImg.thumbSrc.startsWith('blob:') && activeImg.thumbSrc !== activeImg.originalSrc) {
            URL.revokeObjectURL(activeImg.thumbSrc);
          }
          activeImg.croppedBlob = blob;
          activeImg.thumbSrc = URL.createObjectURL(blob);
        }
        resolve();
      }, 'image/jpeg', 0.9);
    } catch (e) {
      console.warn('Cropping canvas export failed, preserving original image:', e);
      resolve();
    }
  });
};

const switchEditorImage = async (newIdx) => {
  if (newIdx === editorActiveIdx.value) return;
  await saveCurrentCrop();
  destroyCropper();
  
  editorActiveIdx.value = newIdx;
  const nextImg = editorBatchImages.value[newIdx];
  // Load original image file/source so they can crop/re-crop the full resolution
  editorImageSrc.value = nextImg.originalSrc;
  initCropper();
};

const applyAllEdits = async () => {
  await saveCurrentCrop();
  
  if (editorTargetIndex.value >= 0) {
    // We were editing existing images
    editorBatchImages.value.forEach(bImg => {
      if (bImg.croppedBlob && bImg.existingIndex !== null) {
        const oldPreview = productImages.value[bImg.existingIndex].preview;
        if (oldPreview.startsWith('blob:')) {
          URL.revokeObjectURL(oldPreview);
        }
        productImages.value[bImg.existingIndex] = {
          file: bImg.croppedBlob,
          preview: bImg.thumbSrc
        };
      }
    });
  } else {
    // We were adding new images
    editorBatchImages.value.forEach(bImg => {
      productImages.value.push({
        file: bImg.croppedBlob || bImg.file,
        preview: bImg.croppedBlob ? bImg.thumbSrc : bImg.originalSrc
      });
    });
  }

  // Revoke unused batch object URLs to prevent leaks
  editorBatchImages.value.forEach(bImg => {
    if (!bImg.isExisting && bImg.originalSrc.startsWith('blob:')) {
      const wasUsedAsPreview = productImages.value.some(p => p.preview === bImg.originalSrc);
      if (!wasUsedAsPreview) {
        URL.revokeObjectURL(bImg.originalSrc);
      }
    }
  });

  // Commit temp primary image index
  primaryImageIndex.value = tempPrimaryIndex.value;
  syncFormImages();
  destroyCropper();
  showImageEditor.value = false;
  editorBatchImages.value = [];
};

const closeImageEditor = () => {
  destroyCropper();
  showImageEditor.value = false;
  
  // Revoke temp batch object URLs that were not saved
  editorBatchImages.value.forEach(bImg => {
    if (!bImg.isExisting && bImg.originalSrc.startsWith('blob:')) {
      URL.revokeObjectURL(bImg.originalSrc);
    }
    if (bImg.croppedBlob && bImg.thumbSrc.startsWith('blob:')) {
      URL.revokeObjectURL(bImg.thumbSrc);
    }
  });
  
  editorBatchImages.value = [];
};

const removeProductImage = (idx) => {
  const removed = productImages.value.splice(idx, 1)[0];
  if (removed && removed.preview.startsWith('blob:')) URL.revokeObjectURL(removed.preview);
  
  // Adjust primary index after removal
  if (primaryImageIndex.value === idx) {
    primaryImageIndex.value = 0;
  } else if (primaryImageIndex.value > idx) {
    primaryImageIndex.value -= 1;
  }
  syncFormImages();
};

// ===== Gallery Viewer Lightbox =====
const showGalleryViewer = ref(false);
const galleryViewerIndex = ref(0);

const handleGalleryKeydown = (e) => {
  if (!showGalleryViewer.value) return;
  if (e.key === 'ArrowLeft') {
    galleryViewerIndex.value = (galleryViewerIndex.value - 1 + productImages.value.length) % productImages.value.length;
  } else if (e.key === 'ArrowRight') {
    galleryViewerIndex.value = (galleryViewerIndex.value + 1) % productImages.value.length;
  } else if (e.key === 'Escape') {
    closeGalleryViewer();
  }
};

const openGalleryViewer = (startIdx = 0) => {
  galleryViewerIndex.value = startIdx;
  showGalleryViewer.value = true;
  window.addEventListener('keydown', handleGalleryKeydown);
};

const closeGalleryViewer = () => {
  showGalleryViewer.value = false;
  window.removeEventListener('keydown', handleGalleryKeydown);
};

const deleteFromGalleryViewer = () => {
  if (productImages.value.length === 0) return;
  const idx = galleryViewerIndex.value;
  removeProductImage(idx);
  if (productImages.value.length === 0) {
    closeGalleryViewer();
    return;
  }
  if (galleryViewerIndex.value >= productImages.value.length) {
    galleryViewerIndex.value = productImages.value.length - 1;
  }
};

// ===== Remove image from editor batch =====
const removeEditorImage = (bIdx) => {
  if (editorBatchImages.value.length <= 1) return; // don't remove last image

  // If editing existing images, also remove from productImages
  const bImg = editorBatchImages.value[bIdx];
  if (bImg.isExisting && bImg.existingIndex !== null) {
    removeProductImage(bImg.existingIndex);
    // Re-index existingIndex for remaining batch items
    editorBatchImages.value.splice(bIdx, 1);
    editorBatchImages.value.forEach((item, i) => {
      if (item.isExisting && item.existingIndex !== null && item.existingIndex > bImg.existingIndex) {
        item.existingIndex -= 1;
      }
    });
  } else {
    // New image batch — just remove from batch
    const removed = editorBatchImages.value.splice(bIdx, 1)[0];
    if (removed.originalSrc.startsWith('blob:')) URL.revokeObjectURL(removed.originalSrc);
    if (removed.croppedBlob && removed.thumbSrc.startsWith('blob:')) URL.revokeObjectURL(removed.thumbSrc);
  }

  // Adjust tempPrimaryIndex
  if (tempPrimaryIndex.value === bIdx) {
    tempPrimaryIndex.value = 0;
  } else if (tempPrimaryIndex.value > bIdx) {
    tempPrimaryIndex.value -= 1;
  }

  // Adjust active index
  if (editorActiveIdx.value >= editorBatchImages.value.length) {
    editorActiveIdx.value = editorBatchImages.value.length - 1;
  } else if (editorActiveIdx.value === bIdx) {
    // Stay at same index (now shows next image) or go to previous
    if (editorActiveIdx.value >= editorBatchImages.value.length) {
      editorActiveIdx.value = editorBatchImages.value.length - 1;
    }
  }

  // Reload the active image in the cropper
  destroyCropper();
  const current = editorBatchImages.value[editorActiveIdx.value];
  if (current) {
    editorImageSrc.value = current.originalSrc;
    initCropper();
  }
};

const onScan = (barcode) => {
  form.value.barcode = barcode;
  scanning.value = false;
};

const isVariantMode = computed(() => !!form.value.has_variations);

watch(() => form.value.has_variations, (newVal) => {
  if (!newVal) {
    attributes.value = [];
    form.value.variations = [];
  }
});

watch(
  [() => form.value.has_variations, () => form.value.variations],
  ([isVar, vars]) => {
    if (isVar) {
      const list = Array.isArray(vars) ? vars : [];
      const first = list[0];
      if (first) {
        form.value.selling_price = first.retail_price ?? '';
        form.value.cost_price = first.cost_price ?? '';
        form.value.wholesale_price = first.wholesale_price ?? '';
        form.value.tax_rate = first.tax_rate !== null && first.tax_rate !== undefined ? parseFloat(first.tax_rate) : '';
      }
      form.value.stock_quantity = list.reduce((total, row) => {
        const qty = parseInt(row.stock_qty);
        return total + (isNaN(qty) ? 0 : qty);
      }, 0);
      form.value.min_stock_level = list.reduce((total, row) => {
        const qty = parseInt(row.min_stock_alert);
        return total + (isNaN(qty) ? 0 : qty);
      }, 0);
    }
  },
  { deep: true, immediate: true }
);

// Category Modal Logic
const showCategoryModal = ref(false);
const submittingCategory = ref(false);
const categoryModalError = ref('');
const newCategoryForm = ref({
  name: '',
  parent_id: '',
  description: '',
  is_active: true
});

const openCategoryModal = () => {
  newCategoryForm.value = {
    name: '',
    parent_id: '',
    description: '',
    is_active: true
  };
  categoryModalError.value = '';
  showCategoryModal.value = true;
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
};

const submitNewCategory = async () => {
  if (!newCategoryForm.value.name.trim()) {
    categoryModalError.value = 'Category Name is required.';
    return;
  }
  submittingCategory.value = true;
  categoryModalError.value = '';
  try {
    const response = await axios.post('/api/categories', newCategoryForm.value);
    if (response.data && response.data.category) {
      const newCat = response.data.category;
      categories.value.push(newCat);
      form.value.category_ids.push(newCat.id);
      form.value.category_id = newCat.id;
      closeCategoryModal();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      categoryModalError.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      categoryModalError.value = error.response?.data?.message || 'An error occurred while creating the category.';
    }
  } finally {
    submittingCategory.value = false;
  }
};

watch(() => form.value.category_id, (newVal, oldVal) => {
  if (newVal === 'add_new_category') {
    openCategoryModal();
    form.value.category_id = oldVal || '';
  }
});

// Unit Modal Logic
const showUnitModal = ref(false);
const submittingUnit = ref(false);
const unitModalError = ref('');
const newUnitForm = ref({
  name: '',
  short_name: '',
  is_active: true
});

const openUnitModal = () => {
  newUnitForm.value = {
    name: '',
    short_name: '',
    is_active: true
  };
  unitModalError.value = '';
  showUnitModal.value = true;
};

const closeUnitModal = () => {
  showUnitModal.value = false;
};

const submitNewUnit = async () => {
  if (!newUnitForm.value.name.trim() || !newUnitForm.value.short_name.trim()) {
    unitModalError.value = 'Both Unit Name and Short Name are required.';
    return;
  }
  submittingUnit.value = true;
  unitModalError.value = '';
  try {
    const response = await axios.post('/api/units', {
      name: newUnitForm.value.name,
      short_name: newUnitForm.value.short_name,
      is_active: newUnitForm.value.is_active
    });

    if (response.data && response.data.unit) {
      const newUnit = response.data.unit;
      units.value.push(newUnit);
      form.value.unit_id = newUnit.id;
      closeUnitModal();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      unitModalError.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      unitModalError.value = error.response?.data?.message || 'An error occurred while creating the unit.';
    }
  } finally {
    submittingUnit.value = false;
  }
};

watch(() => form.value.unit_id, (newVal, oldVal) => {
  if (newVal === 'add_new_unit') {
    openUnitModal();
    form.value.unit_id = oldVal || '';
  }
});

// Tax Modal Logic
const showTaxModal = ref(false);
const submittingTax = ref(false);
const taxModalError = ref('');
const newTaxForm = ref({
  name: '',
  value: '',
  type: 'percentage',
  is_active: true
});

const openTaxModal = () => {
  newTaxForm.value = {
    name: '',
    value: '',
    type: 'percentage',
    is_active: true
  };
  taxModalError.value = '';
  showTaxModal.value = true;
};

const closeTaxModal = () => {
  showTaxModal.value = false;
};

const submitNewTax = async () => {
  if (!newTaxForm.value.name.trim() || newTaxForm.value.value === '') {
    taxModalError.value = 'Both Tax Name and Value (Percentage) are required.';
    return;
  }
  submittingTax.value = true;
  taxModalError.value = '';
  try {
    const response = await axios.post('/api/taxes', {
      name: newTaxForm.value.name,
      value: parseFloat(newTaxForm.value.value),
      type: newTaxForm.value.type,
      is_active: newTaxForm.value.is_active
    });

    if (response.data && response.data.tax) {
      const newTax = response.data.tax;
      taxes.value.push(newTax);
      if (!form.value.taxes) {
        form.value.taxes = [];
      }
      form.value.taxes.push(newTax.id);
      const sum = form.value.taxes.reduce((total, id) => {
        const tax = taxes.value.find(t => t.id === id || Number(t.id) === Number(id));
        return total + (tax ? parseFloat(tax.value) : 0);
      }, 0);
      form.value.tax_rate = sum;
      closeTaxModal();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      taxModalError.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      taxModalError.value = error.response?.data?.message || 'An error occurred while creating the tax.';
    }
  } finally {
    submittingTax.value = false;
  }
};

// Variation Modal Logic
const showVariationModal = ref(false);
const submittingVariation = ref(false);
const variationModalError = ref('');
const newVariationForm = ref({
  name: '',
  valuesString: ''
});

const openVariationModal = () => {
  newVariationForm.value = { name: masterAttrSearchQuery.value.trim(), valuesString: '' };
  variationModalError.value = '';
  showMasterAttrDropdown.value = false;
  showVariationModal.value = true;
};

const closeVariationModal = () => {
  showVariationModal.value = false;
};

const submitNewVariation = async () => {
  const name = newVariationForm.value.name.trim();
  const valuesStr = newVariationForm.value.valuesString.trim();
  
  if (!name) {
    variationModalError.value = 'Variation Name is required.';
    return;
  }
  if (!valuesStr) {
    variationModalError.value = 'At least one value is required.';
    return;
  }
  
  const valuesArr = valuesStr.split(',').map(v => v.trim()).filter(v => v !== '');
  if (valuesArr.length === 0) {
    variationModalError.value = 'At least one valid value is required.';
    return;
  }

  submittingVariation.value = true;
  variationModalError.value = '';
  try {
    const response = await axios.post('/api/attributes', {
      name: name,
      values: valuesArr
    });

    if (response.data) {
      const newAttr = response.data;
      // Add to the system attributes master list
      systemAttributes.value.push(newAttr);
      // Auto-select it in the multi-select
      selectMasterAttribute(newAttr);
      closeVariationModal();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      variationModalError.value = Object.values(error.response.data.errors).flat().join(' ');
    } else {
      variationModalError.value = error.response?.data?.message || 'An error occurred while creating the variation.';
    }
  } finally {
    submittingVariation.value = false;
  }
};

watch(() => form.value.tax_rate, (newVal, oldVal) => {
  if (newVal === 'add_new_tax') {
    openTaxModal();
    form.value.tax_rate = oldVal !== undefined && oldVal !== 'add_new_tax' ? oldVal : 0;
  }
});

watch(() => form.value.category_ids, (newCategoryIds) => {
  if (Array.isArray(newCategoryIds) && newCategoryIds.length > 0) {
    const firstCatId = newCategoryIds[0];
    const catList = Array.isArray(categories.value)
      ? categories.value
      : (categories.value && Array.isArray(categories.value.data) ? categories.value.data : []);
    const cat = catList.find(c => c.id === firstCatId || String(c.id) === String(firstCatId));
    if (cat && cat.tax) {
      form.value.enabled_for_tax = true;
      form.value.tax_rate = parseFloat(cat.tax.value);
      if (cat.tax.id) {
        form.value.taxes = [Number(cat.tax.id)];
      } else {
        form.value.taxes = [];
      }
    }
  }
}, { deep: true });

const submit = () => {
  if (isVariantMode.value) {
    const names = attributes.value.map(a => (a.name || '').trim().toLowerCase()).filter(n => n !== '');
    const uniqueNames = new Set(names);
    if (uniqueNames.size !== names.length) {
      showLocalError('Please resolve duplicate variation option names (e.g. configuring "Color" multiple times).');
      return;
    }

    if (form.value.show_wholesale_price) {
      for (const v of (form.value.variations || [])) {
        if (v.wholesale_price === undefined || v.wholesale_price === null || String(v.wholesale_price).trim() === '') {
          showLocalError(`Wholesale price is required for variation "${v.name_string}".`);
          return;
        }
      }
    }

    if (form.value.show_tax_rate) {
      for (const v of (form.value.variations || [])) {
        if (!v.taxes || v.taxes.length === 0) {
          showLocalError(`At least one tax rate must be selected for variation "${v.name_string}".`);
          return;
        }
      }
    }

    form.value.stock_quantity = (form.value.variations || []).reduce((sum, row) => {
      const qty = parseInt(row.stock_qty);
      return sum + (isNaN(qty) ? 0 : qty);
    }, 0);
  }

  // Clear localStorage status on successful form submission intent
  localStorage.removeItem('pos_item_form_status');

  const finalForm = { ...form.value };
  if (!finalForm.enabled_for_wholesale) {
    finalForm.wholesale_price = 0;
  }

  if (isVariantMode.value) {
    finalForm.variations = (finalForm.variations || []).map(v => {
      const updated = { ...v };
      if (!finalForm.show_wholesale_price) {
        updated.wholesale_price = 0;
      }
      if (!finalForm.show_tax_rate) {
        updated.taxes = [];
        updated.tax_rate = 0;
      }
      return updated;
    });
  }

  const payload = { 
    ...finalForm, 
    is_active: finalForm.status === 'active',
    attributes: isVariantMode.value ? attributes.value : [],
    has_variations: isVariantMode.value,
    variations: isVariantMode.value ? finalForm.variations : [],
    category_ids: finalForm.category_ids,
    warehouses: warehouses.value,
    images: productImages.value.map(img => img.file),
    primary_image_index: primaryImageIndex.value
  };
  emit('submit', payload);
};

// Tags state & dropdown selection logic
const allTags = ref([]);
const showTagDropdown = ref(false);
const creatingTag = ref(false);

const showInlineCreateTag = ref(false);
const newInlineTagName = ref('');
const inlineTagInputRef = ref(null);

const tagOptions = computed(() => {
  const list = Array.isArray(allTags.value) ? allTags.value : [];
  const options = list.map(t => ({ label: t.name, value: t.name }));
  return options;
});

const toggleTagSelection = (val) => {
  if (!form.value.tags) {
    form.value.tags = [];
  }
  const idx = form.value.tags.indexOf(val);
  if (idx > -1) {
    form.value.tags.splice(idx, 1);
  } else {
    form.value.tags.push(val);
  }
};

const removeTag = (val) => {
  if (!form.value.tags) return;
  const idx = form.value.tags.indexOf(val);
  if (idx > -1) {
    form.value.tags.splice(idx, 1);
  }
};

const startInlineTagCreate = () => {
  showInlineCreateTag.value = true;
  nextTick(() => {
    if (inlineTagInputRef.value) {
      inlineTagInputRef.value.focus();
    }
  });
};

const cancelInlineTagCreate = () => {
  showInlineCreateTag.value = false;
  newInlineTagName.value = '';
};

const submitInlineTag = async () => {
  const tagName = newInlineTagName.value.trim();
  if (!tagName) return;

  const exists = allTags.value.some(t => t.name.toLowerCase() === tagName.toLowerCase());
  if (exists) {
    const found = allTags.value.find(t => t.name.toLowerCase() === tagName.toLowerCase());
    if (found) {
      if (!form.value.tags) form.value.tags = [];
      if (!form.value.tags.includes(found.name)) {
        form.value.tags.push(found.name);
      }
    }
    cancelInlineTagCreate();
    return;
  }

  creatingTag.value = true;
  try {
    const res = await axios.post('/api/tags', { name: tagName });
    const createdTag = res.data?.tag || res.data?.data || res.data || { name: tagName };
    const nameToAdd = createdTag.name || tagName;

    // Add to allTags if not already there
    if (!allTags.value.some(t => t.name.toLowerCase() === nameToAdd.toLowerCase())) {
      allTags.value.push({ id: createdTag.id || Date.now(), name: nameToAdd });
    }

    if (!form.value.tags) form.value.tags = [];
    if (!form.value.tags.includes(nameToAdd)) {
      form.value.tags.push(nameToAdd);
    }
  } catch (err) {
    console.error('Failed to create tag:', err);
    // Fallback local creation
    if (!allTags.value.some(t => t.name.toLowerCase() === tagName.toLowerCase())) {
      allTags.value.push({ id: Date.now(), name: tagName });
    }
    if (!form.value.tags) form.value.tags = [];
    if (!form.value.tags.includes(tagName)) {
      form.value.tags.push(tagName);
    }
  } finally {
    creatingTag.value = false;
    cancelInlineTagCreate();
  }
};

const closeTagDropdownOnOutsideClick = (e) => {
  const el = document.getElementById('tag-multiselect-container');
  if (el && !el.contains(e.target)) {
    showTagDropdown.value = false;
    cancelInlineTagCreate();
  }
};

// Variation Table Tax & Tag dropdown logic
const activeRowTaxDropdown = ref(null);
const taxDropdownStyles = ref({});

const totalMatrixColumns = computed(() => {
  let cols = 8; // Variant Profile, SKU, Warehouse, Cost, Retail, Stock, Expiry, Action
  if (form.value.show_wholesale_price) cols++;
  if (form.value.show_tax_rate) cols++;
  return cols;
});

const variationTaxOptions = computed(() => {
  const list = Array.isArray(taxes.value) ? taxes.value : [];
  return list.map(t => ({ label: `${t.name} (${t.value}%)`, value: t.id }));
});

const toggleRowTaxDropdown = async (index) => {
  if (activeRowTaxDropdown.value === index) {
    activeRowTaxDropdown.value = null;
  } else {
    activeRowTaxDropdown.value = index;
    await nextTick();
    const button = document.getElementById(`tax-trigger-${index}`);
    if (button) {
      const rect = button.getBoundingClientRect();
      taxDropdownStyles.value[index] = {
        left: `${rect.left}px`,
        bottom: `${window.innerHeight - rect.top + 4}px`,
        width: `${rect.width}px`
      };
    }
  }
};

const getRowTaxLabel = (rowTaxes) => {
  const ids = rowTaxes || [];
  if (ids.length === 0) return 'No Tax (0%)';
  const names = ids.map(id => {
    const tax = taxes.value.find(t => t.id === id || Number(t.id) === Number(id));
    return tax ? `${tax.name} (${tax.value}%)` : `${id}%`;
  });
  if (names.length <= 1) return names.join(', ');
  return `${names[0]} +${names.length - 1} more`;
};

const isRowTaxSelected = (row, taxId) => {
  return (row.taxes || []).some(id => Number(id) === Number(taxId));
};

const toggleRowTaxSelection = (index, taxId) => {
  const row = form.value.variations[index];
  if (!row) return;
  if (!row.taxes) {
    row.taxes = [];
  }
  const targetId = Number(taxId);
  const idx = row.taxes.findIndex(id => Number(id) === targetId);
  if (idx > -1) {
    row.taxes.splice(idx, 1);
  } else {
    row.taxes.push(targetId);
  }
  // Sync aggregated tax_rate
  const sum = row.taxes.reduce((total, id) => {
    const tax = taxes.value.find(t => t.id === id || Number(t.id) === Number(id));
    return total + (tax ? parseFloat(tax.value) : 0);
  }, 0);
  row.tax_rate = sum;
};

const activeRowTagDropdown = ref(null);
const tagDropdownStyles = ref({});

const variationTagOptions = computed(() => {
  const list = Array.isArray(allTags.value) ? allTags.value : [];
  return list.map(t => ({ label: t.name, value: t.name }));
});

const toggleRowTagDropdown = async (index) => {
  if (activeRowTagDropdown.value === index) {
    activeRowTagDropdown.value = null;
  } else {
    activeRowTagDropdown.value = index;
    await nextTick();
    const button = document.getElementById(`tag-trigger-${index}`);
    if (button) {
      const rect = button.getBoundingClientRect();
      tagDropdownStyles.value[index] = {
        left: `${rect.left}px`,
        bottom: `${window.innerHeight - rect.top + 4}px`,
        width: `${rect.width}px`
      };
    }
  }
};

const getRowTagLabel = (rowTags) => {
  const tagsArr = rowTags || [];
  if (tagsArr.length === 0) return 'Select Tag(s)';
  if (tagsArr.length <= 1) return tagsArr.join(', ');
  return `${tagsArr[0]} +${tagsArr.length - 1} more`;
};

const toggleRowTagSelection = (index, tagName) => {
  const row = form.value.variations[index];
  if (!row) return;
  if (!row.tags) {
    row.tags = [];
  }
  const idx = row.tags.indexOf(tagName);
  if (idx > -1) {
    row.tags.splice(idx, 1);
  } else {
    row.tags.push(tagName);
  }
};

const closeRowTaxDropdownOnOutsideClick = (e) => {
  if (activeRowTaxDropdown.value !== null) {
    const isInsideTrigger = e.target.closest('.tax-dropdown-cell');
    const isInsideDropdown = e.target.closest('.tax-teleport-dropdown');
    if (!isInsideTrigger && !isInsideDropdown) {
      activeRowTaxDropdown.value = null;
    }
  }
};

const closeRowTagDropdownOnOutsideClick = (e) => {
  if (activeRowTagDropdown.value !== null) {
    const isInsideTrigger = e.target.closest('.tag-dropdown-cell');
    const isInsideDropdown = e.target.closest('.tag-teleport-dropdown');
    if (!isInsideTrigger && !isInsideDropdown) {
      activeRowTagDropdown.value = null;
    }
  }
};
</script>

<style scoped>
.list-enter-active, .list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar for better professional look */
.custom-scrollbar::-webkit-scrollbar,
::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track,
::-webkit-scrollbar-track {
  background: #f8fafc;
}
.custom-scrollbar::-webkit-scrollbar-thumb,
::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover,
::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Hidden scrollbar — scrolls but no visible scrollbar */
.scrollbar-hidden {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}
</style>

<style>
/* Dark theme overrides for perfect contrast and readability */
.dark label {
  color: #ffffff !important; /* Make all labels white in dark theme */
}

.dark .dark\:text-slate-500 {
  color: #e2e8f0 !important; /* Make labels / text that use slate-500 white/light-slate */
}

.dark .dark\:text-slate-400 {
  color: #f1f5f9 !important; /* Make checkbox labels / text that use slate-400 near-white */
}

.dark .dark\:text-slate-350 {
  color: #f8fafc !important; /* Make text using slate-350 white */
}

.dark .dark\:text-slate-300 {
  color: #ffffff !important; /* Make input values / text using slate-300 white */
}

.dark .dark\:text-slate-200 {
  color: #ffffff !important; /* Make section titles / text using slate-200 white */
}

.dark .dark\:text-slate-550 {
  color: #cbd5e1 !important; /* Make placeholders using slate-550 light slate so they are visible */
}

.dark input,
.dark textarea,
.dark select {
  color: #ffffff !important; /* Force all user-input text to be white in dark mode */
}
</style>
