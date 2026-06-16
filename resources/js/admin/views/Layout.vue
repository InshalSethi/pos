<template>
    <div class="w-full h-full flex overflow-hidden">
        <!-- Sidebar -->
        <aside :class="[isSidebarCollapsed ? 'w-[80px]' : 'w-[260px]']" class="bg-white border-r border-gray-200 flex flex-col flex-shrink-0 z-20 h-full transition-all duration-300 ease-in-out">
            <div class="h-16 flex items-center border-b border-gray-100 overflow-hidden" :class="[isSidebarCollapsed ? 'justify-center px-0' : 'px-6']">
                <div class="w-8 h-8 rounded bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shrink-0" :class="[isSidebarCollapsed ? '' : 'mr-3']">A</div>
                <h2 v-if="!isSidebarCollapsed" class="font-bold text-gray-800 text-lg tracking-tight whitespace-nowrap">Admin System</h2>
            </div>
            
            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 overflow-x-hidden">
                <p v-if="!isSidebarCollapsed" class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4 whitespace-nowrap">Main Menu</p>
                <div v-else class="h-6 mt-4"></div>
                
                <router-link :to="{ name: 'admin.dashboard' }" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm text-gray-500" :title="isSidebarCollapsed ? 'Dashboard' : ''" active-class="" exact-active-class="router-link-active">
                    <i class="fas fa-home w-5 text-center text-gray-400 text-lg shrink-0" :class="[isSidebarCollapsed ? 'mx-auto' : 'mr-3']"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Dashboard</span>
                </router-link>
                
                <p v-if="!isSidebarCollapsed" class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-6 whitespace-nowrap">Administration</p>
                <div v-else class="h-6 mt-6 border-t border-gray-100 mx-2"></div>
                
                <router-link :to="{ name: 'admin.admins.index' }" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm text-gray-500" :title="isSidebarCollapsed ? 'Admins' : ''">
                    <i class="fas fa-user-shield w-5 text-center text-gray-400 text-lg shrink-0" :class="[isSidebarCollapsed ? 'mx-auto' : 'mr-3']"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Admins</span>
                </router-link>
                
                <router-link :to="{ name: 'admin.users.index' }" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm text-gray-500" :title="isSidebarCollapsed ? 'Website Users' : ''">
                    <i class="fas fa-users w-5 text-center text-gray-400 text-lg shrink-0" :class="[isSidebarCollapsed ? 'mx-auto' : 'mr-3']"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Website Users</span>
                </router-link>
                
                <router-link :to="{ name: 'admin.roles.index' }" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm text-gray-500" :title="isSidebarCollapsed ? 'Roles & Permissions' : ''">
                    <i class="fas fa-shield-alt w-5 text-center text-gray-400 text-lg shrink-0" :class="[isSidebarCollapsed ? 'mx-auto' : 'mr-3']"></i>
                    <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Roles & Permissions</span>
                </router-link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50">
            <!-- Header -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 flex-shrink-0 z-10 transition-all duration-300">
                <div class="flex items-center">
                    <button @click="isSidebarCollapsed = !isSidebarCollapsed" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md bg-gray-100 mr-4 transition-colors">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ routeName }}</h1>
                </div>
                
                <div class="flex items-center space-x-4">

                    <button @click="toggleDarkMode" class="w-8 h-8 rounded-full text-gray-400 hover:text-yellow-500 hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <i :class="isDarkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
                    </button>

                    
                    <div class="flex items-center ml-2 pl-4 border-l border-gray-200">
                        <div class="relative">
                            <button @click="showDropdown = !showDropdown" class="w-9 h-9 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold shadow-sm shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-transform hover:scale-105">
                                {{ userInitials }}
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div v-if="showDropdown" class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 transform origin-top-right transition-all">
                                <!-- User Info -->
                                <div class="px-5 py-4 flex items-center border-b border-gray-100 mb-2">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-500 text-white flex items-center justify-center font-extrabold text-xl mr-4 shadow-lg shadow-indigo-200 shrink-0">
                                        {{ userInitials }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-0.5">Operator</p>
                                        <p class="text-sm font-extrabold text-gray-800 leading-tight truncate">{{ userName }}</p>
                                        <p class="text-[11px] font-medium text-gray-400 truncate mt-0.5">{{ userEmail }}</p>
                                    </div>
                                </div>
                                
                                <router-link :to="{ name: 'admin.profile' }" @click="showDropdown = false" class="block px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors flex items-center">
                                    <i class="far fa-user w-5 text-center mr-3 text-gray-400 text-lg"></i> Account Profile
                                </router-link>
                                <router-link :to="{ name: 'admin.settings' }" @click="showDropdown = false" class="block px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors flex items-center">
                                    <i class="fas fa-cog w-5 text-center mr-3 text-gray-400 text-lg"></i> System Settings
                                </router-link>
                                
                                <div class="h-px bg-gray-100 my-2 mx-2"></div>
                                
                                <button @click="logout" class="w-full text-left block px-5 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 transition-colors flex items-center">
                                    <i class="fas fa-sign-out-alt w-5 text-center mr-3 text-lg"></i> Secure Sign Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
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
    if (route.name === 'admin.dashboard') return 'Dashboard';
    if (route.name?.includes('admins')) return 'Admins';
    if (route.name?.includes('users')) return 'Users Management';
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
        // Force redirect if API fails
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
