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

        // Store token with remember me preference
        localStorage.setItem('auth_token', token.value);

        // Store remember me preference
        if (credentials.rememberMe) {
          localStorage.setItem('remember_me', 'true');
          // Set a longer expiration for remember me (30 days)
          const expirationDate = new Date();
          expirationDate.setDate(expirationDate.getDate() + 30);
          localStorage.setItem('token_expiration', expirationDate.toISOString());
        } else {
          localStorage.removeItem('remember_me');
          // Set shorter expiration for regular login (1 day)
          const expirationDate = new Date();
          expirationDate.setDate(expirationDate.getDate() + 1);
          localStorage.setItem('token_expiration', expirationDate.toISOString());
        }

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
      localStorage.removeItem('remember_me');
      localStorage.removeItem('token_expiration');
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
    const tokenExpiration = localStorage.getItem('token_expiration');
    const rememberMe = localStorage.getItem('remember_me') === 'true';

    console.log('Initializing auth, stored token:', storedToken ? 'exists' : 'none');

    if (storedToken) {
      // Check if token has expired
      if (tokenExpiration) {
        const expirationDate = new Date(tokenExpiration);
        const now = new Date();

        if (now > expirationDate && !rememberMe) {
          console.log('Token expired and remember me not set, logging out');
          await logout();
          return;
        }
      }

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

  const forgotPassword = async (email) => {
    try {
      const response = await axios.post('/api/forgot-password', { email });

      return {
        success: true,
        message: response.data.message
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to send reset email'
      };
    }
  };

  const resetPassword = async (data) => {
    try {
      const response = await axios.post('/api/reset-password', data);

      return {
        success: true,
        message: response.data.message
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to reset password',
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
    register,
    forgotPassword,
    resetPassword
  };
});
