<template>
    <div class="w-full h-full flex overflow-hidden bg-slate-50 dark:bg-zinc-950">
        <!-- Sidebar -->
        <aside 
            :class="[isSidebarCollapsed ? 'w-20' : 'w-[260px]']" 
            class="bg-white dark:bg-zinc-950 border-r border-zinc-200 dark:border-zinc-800 flex flex-col flex-shrink-0 z-20 h-full transition-all duration-300 ease-in-out"
        >
            <!-- Sidebar Brand Header -->
            <div class="h-16 flex items-center border-b border-zinc-200 dark:border-zinc-800 overflow-hidden" :class="[isSidebarCollapsed ? 'justify-center px-0' : 'px-6']">
                <div class="w-9 h-9 rounded-xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-lg shrink-0 shadow-sm" :class="[isSidebarCollapsed ? '' : 'mr-3']">
                    A
                </div>
                <div v-if="!isSidebarCollapsed" class="flex flex-col min-w-0">
                    <h2 class="font-black text-zinc-950 dark:text-white text-base tracking-tight truncate leading-tight">Admin System</h2>
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-wider">Control Panel</span>
                </div>
            </div>
            
            <!-- Navigation items -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 overflow-x-hidden custom-scrollbar">
                <div v-if="!isSidebarCollapsed" class="px-3 pt-2 pb-1">
                    <h3 class="text-[10px] tracking-widest text-zinc-400 dark:text-zinc-500 font-bold uppercase">Main Menu</h3>
                </div>
                <div v-else class="h-4"></div>
                
                <router-link 
                    :to="{ name: 'admin.dashboard' }" 
                    class="group flex items-center px-3 py-2.5 text-xs rounded-xl transition-all duration-200 relative cursor-pointer"
                    :class="[
                        $route.name === 'admin.dashboard'
                            ? 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800/80 dark:text-white font-extrabold border-l-4 border-black dark:border-white rounded-l-none'
                            : 'border-l-4 border-transparent text-zinc-500 hover:bg-zinc-100/60 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/40 dark:hover:text-zinc-100 font-semibold rounded-l-none'
                    ]"
                    :title="isSidebarCollapsed ? 'Dashboard' : ''"
                >
                    <i class="fas fa-chart-line w-5 text-center text-sm shrink-0" :class="[
                        isSidebarCollapsed ? 'mx-auto' : 'mr-3',
                        $route.name === 'admin.dashboard' ? 'text-black dark:text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                    ]"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap tracking-wide">Dashboard</span>
                </router-link>
                
                <div v-if="!isSidebarCollapsed" class="px-3 pt-6 pb-1">
                    <h3 class="text-[10px] tracking-widest text-zinc-400 dark:text-zinc-500 font-bold uppercase">Administration</h3>
                </div>
                <div v-else class="h-4 my-2 border-t border-zinc-100 dark:border-zinc-800/60 mx-2"></div>
                
                <router-link 
                    :to="{ name: 'admin.admins.index' }" 
                    class="group flex items-center px-3 py-2.5 text-xs rounded-xl transition-all duration-200 relative cursor-pointer"
                    :class="[
                        $route.name?.includes('admins')
                            ? 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800/80 dark:text-white font-extrabold border-l-4 border-black dark:border-white rounded-l-none'
                            : 'border-l-4 border-transparent text-zinc-500 hover:bg-zinc-100/60 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/40 dark:hover:text-zinc-100 font-semibold rounded-l-none'
                    ]"
                    :title="isSidebarCollapsed ? 'Admins' : ''"
                >
                    <i class="fas fa-user-shield w-5 text-center text-sm shrink-0" :class="[
                        isSidebarCollapsed ? 'mx-auto' : 'mr-3',
                        $route.name?.includes('admins') ? 'text-black dark:text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                    ]"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap tracking-wide">Admins</span>
                </router-link>
                
                <router-link 
                    :to="{ name: 'admin.users.index' }" 
                    class="group flex items-center px-3 py-2.5 text-xs rounded-xl transition-all duration-200 relative cursor-pointer"
                    :class="[
                        $route.name?.includes('users')
                            ? 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800/80 dark:text-white font-extrabold border-l-4 border-black dark:border-white rounded-l-none'
                            : 'border-l-4 border-transparent text-zinc-500 hover:bg-zinc-100/60 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/40 dark:hover:text-zinc-100 font-semibold rounded-l-none'
                    ]"
                    :title="isSidebarCollapsed ? 'Website Users' : ''"
                >
                    <i class="fas fa-users w-5 text-center text-sm shrink-0" :class="[
                        isSidebarCollapsed ? 'mx-auto' : 'mr-3',
                        $route.name?.includes('users') ? 'text-black dark:text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                    ]"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap tracking-wide">Website Users</span>
                </router-link>
                
                <router-link 
                    :to="{ name: 'admin.roles.index' }" 
                    class="group flex items-center px-3 py-2.5 text-xs rounded-xl transition-all duration-200 relative cursor-pointer"
                    :class="[
                        $route.name?.includes('roles')
                            ? 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800/80 dark:text-white font-extrabold border-l-4 border-black dark:border-white rounded-l-none'
                            : 'border-l-4 border-transparent text-zinc-500 hover:bg-zinc-100/60 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/40 dark:hover:text-zinc-100 font-semibold rounded-l-none'
                    ]"
                    :title="isSidebarCollapsed ? 'Roles & Permissions' : ''"
                >
                    <i class="fas fa-shield-alt w-5 text-center text-sm shrink-0" :class="[
                        isSidebarCollapsed ? 'mx-auto' : 'mr-3',
                        $route.name?.includes('roles') ? 'text-black dark:text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                    ]"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap tracking-wide">Roles & Permissions</span>
                </router-link>

                <router-link 
                    :to="{ name: 'admin.business-types.index' }" 
                    class="group flex items-center px-3 py-2.5 text-xs rounded-xl transition-all duration-200 relative cursor-pointer"
                    :class="[
                        $route.name?.includes('business-types')
                            ? 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800/80 dark:text-white font-extrabold border-l-4 border-black dark:border-white rounded-l-none'
                            : 'border-l-4 border-transparent text-zinc-500 hover:bg-zinc-100/60 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/40 dark:hover:text-zinc-100 font-semibold rounded-l-none'
                    ]"
                    :title="isSidebarCollapsed ? 'Business Types' : ''"
                >
                    <i class="fas fa-briefcase w-5 text-center text-sm shrink-0" :class="[
                        isSidebarCollapsed ? 'mx-auto' : 'mr-3',
                        $route.name?.includes('business-types') ? 'text-black dark:text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300'
                    ]"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap tracking-wide">Business Types</span>
                </router-link>
            </nav>
        </aside>

        <!-- Main Content Container -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/70 dark:bg-zinc-950">
            <!-- Header -->
            <header class="h-16 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between px-6 flex-shrink-0 z-10 transition-colors">
                <div class="flex items-center">
                    <button 
                        @click="isSidebarCollapsed = !isSidebarCollapsed" 
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white focus:outline-none w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center mr-4 transition-all cursor-pointer"
                        title="Toggle Sidebar"
                    >
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                    <h1 class="text-xl font-extrabold text-zinc-950 dark:text-white tracking-tight">{{ routeName }}</h1>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Dark/Light Mode Switcher -->
                    <button 
                        @click="toggleDarkMode" 
                        class="w-9 h-9 rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center justify-center transition-all cursor-pointer shadow-xs"
                        :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                    >
                        <i :class="isDarkMode ? 'fas fa-sun text-amber-400' : 'fas fa-moon text-zinc-600'"></i>
                    </button>

                    <!-- Profile Dropdown Container -->
                    <div class="flex items-center ml-2 pl-3 border-l border-zinc-200 dark:border-zinc-800">
                        <div class="relative">
                            <button 
                                @click="showDropdown = !showDropdown" 
                                class="w-9 h-9 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition-transform hover:scale-105 cursor-pointer"
                            >
                                {{ userInitials }}
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div 
                                    v-if="showDropdown" 
                                    class="absolute right-0 mt-3 w-64 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-800 py-2 z-50 overflow-hidden"
                                >
                                    <!-- User Info Header -->
                                    <div class="px-4 py-3 flex items-center border-b border-zinc-100 dark:border-zinc-800 mb-1">
                                        <div class="w-10 h-10 rounded-xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-extrabold text-base mr-3 shadow-sm shrink-0">
                                            {{ userInitials }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Admin Operator</p>
                                            <p class="text-xs font-extrabold text-zinc-950 dark:text-white leading-tight truncate">{{ userName }}</p>
                                            <p class="text-[11px] font-medium text-zinc-400 dark:text-zinc-500 truncate mt-0.5">{{ userEmail }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="p-1 space-y-0.5">
                                        <router-link 
                                            :to="{ name: 'admin.profile' }" 
                                            @click="showDropdown = false" 
                                            class="px-3 py-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-950 dark:hover:text-white transition-colors flex items-center rounded-xl"
                                        >
                                            <i class="far fa-user w-5 text-center mr-2.5 text-zinc-400 dark:text-zinc-500"></i> Account Profile
                                        </router-link>
                                        <router-link 
                                            :to="{ name: 'admin.settings' }" 
                                            @click="showDropdown = false" 
                                            class="px-3 py-2 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-950 dark:hover:text-white transition-colors flex items-center rounded-xl"
                                        >
                                            <i class="fas fa-cog w-5 text-center mr-2.5 text-zinc-400 dark:text-zinc-500"></i> System Settings
                                        </router-link>
                                    </div>
                                    
                                    <div class="h-px bg-zinc-100 dark:bg-zinc-800 my-1 mx-2"></div>
                                    
                                    <div class="p-1">
                                        <button 
                                            @click="logout" 
                                            class="w-full text-left px-3 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors flex items-center rounded-xl cursor-pointer"
                                        >
                                            <i class="fas fa-sign-out-alt w-5 text-center mr-2.5"></i> Secure Sign Out
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    <router-view></router-view>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const showDropdown = ref(false);
const isSidebarCollapsed = ref(false);

const isDarkMode = ref(document.documentElement.classList.contains('dark') || localStorage.getItem('theme') === 'dark');

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    }
};

const userName = ref(localStorage.getItem('admin_name') || 'Admin');
const userEmail = ref(localStorage.getItem('admin_email') || 'admin@example.com');

const userInitials = computed(() => {
    return userName.value ? userName.value.substring(0, 1).toUpperCase() : 'A';
});

const routeName = computed(() => {
    if (route.name === 'admin.dashboard') return 'Dashboard Overview';
    if (route.name?.includes('admins')) return 'System Admins';
    if (route.name?.includes('users')) return 'Website Users Management';
    if (route.name?.includes('roles')) return 'Roles & Permissions';
    if (route.name === 'admin.profile') return 'Account Profile';
    if (route.name === 'admin.settings') return 'System Settings';
    return 'Admin Panel';
});

const logout = async () => {
    try {
        await axios.post('/admin/api/logout');
        localStorage.removeItem('admin_token');
        sessionStorage.removeItem('admin_logged_in');
        router.push({ name: 'admin.login' });
    } catch (e) {
        localStorage.removeItem('admin_token');
        sessionStorage.removeItem('admin_logged_in');
        router.push({ name: 'admin.login' });
    }
};

const closeDropdown = (e) => {
    if (!e.target.closest('.relative')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

