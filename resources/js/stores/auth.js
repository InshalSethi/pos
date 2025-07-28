import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token'));
  const permissions = ref([]);
  const roles = ref([]);

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  
  const hasPermission = computed(() => (permission) => {
    return permissions.value.includes(permission);
  });
  
  const hasRole = computed(() => (role) => {
    return roles.value.includes(role);
  });

  // Actions
  const login = async (credentials) => {
    try {
      // Get CSRF cookie first
      await axios.get('/sanctum/csrf-cookie');
      
      // Attempt login
      const response = await axios.post('/api/login', credentials);
      
      if (response.data.token) {
        token.value = response.data.token;
        user.value = response.data.user;
        permissions.value = response.data.permissions || [];
        roles.value = response.data.roles || [];
        
        localStorage.setItem('auth_token', token.value);
        
        return { success: true };
      }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Login failed' 
      };
    }
  };

  const logout = async () => {
    try {
      if (token.value) {
        await axios.post('/api/logout');
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      user.value = null;
      token.value = null;
      permissions.value = [];
      roles.value = [];
      localStorage.removeItem('auth_token');
    }
  };

  const fetchUser = async () => {
    try {
      console.log('Fetching user with token:', token.value);
      const response = await axios.get('/api/user');
      console.log('User response:', response.data);
      user.value = response.data.user;
      permissions.value = response.data.permissions || [];
      roles.value = response.data.roles || [];
      return true;
    } catch (error) {
      console.error('Fetch user error:', error);
      if (error.response?.status === 401) {
        console.log('Token expired or invalid, logging out');
        await logout();
      }
      return false;
    }
  };

  const initializeAuth = async () => {
    const storedToken = localStorage.getItem('auth_token');
    console.log('Initializing auth, stored token:', storedToken ? 'exists' : 'none');
    if (storedToken) {
      token.value = storedToken;
      const success = await fetchUser();
      if (!success) {
        console.log('Failed to fetch user, logging out');
        await logout();
      } else {
        console.log('Auth initialized successfully');
      }
    } else {
      console.log('No stored token found');
    }
  };

  const register = async (userData) => {
    try {
      await axios.get('/sanctum/csrf-cookie');
      
      const response = await axios.post('/api/register', userData);
      
      if (response.data.token) {
        token.value = response.data.token;
        user.value = response.data.user;
        permissions.value = response.data.permissions || [];
        roles.value = response.data.roles || [];
        
        localStorage.setItem('auth_token', token.value);
        
        return { success: true };
      }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Registration failed',
        errors: error.response?.data?.errors || {}
      };
    }
  };

  return {
    // State
    user,
    token,
    permissions,
    roles,
    
    // Getters
    isAuthenticated,
    hasPermission,
    hasRole,
    
    // Actions
    login,
    logout,
    fetchUser,
    initializeAuth,
    register
  };
});
