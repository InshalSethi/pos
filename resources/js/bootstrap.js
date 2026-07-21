import axios from 'axios';

// Configure Axios for Laravel Sanctum
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Set base URL
axios.defaults.baseURL = window.location.origin;

// Add request interceptor to include auth token and active company context
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        let companyId = localStorage.getItem('current_company_id') || localStorage.getItem('company_id');

        try {
            // Lazy load Pinia auth store if active
            const activePinia = window.__pinia;
            if (activePinia && activePinia.state.value?.auth?.user?.current_company_id) {
                companyId = activePinia.state.value.auth.user.current_company_id;
            }
        } catch (e) {
            // Pinia store not initialized yet
        }

        if (!companyId || companyId === 'undefined' || companyId === 'null') {
            companyId = 1;
        }

        if (companyId && companyId !== 'undefined' && companyId !== 'null') {
            config.headers['X-Company-ID'] = companyId;
            config.headers['X-Workspace-ID'] = companyId;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add response interceptor to handle auth errors
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const url = error.config?.url || '';
            // If it's a login attempt, let the component handle the error and show validation message
            if (url.includes('/login')) {
                return Promise.reject(error);
            }

            if (url.includes('/admin/api/') || url.includes('/admin/')) {
                localStorage.removeItem('admin_token');
                window.location.href = '/admin/login';
            } else {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }
        if (error.response?.status === 403) {
            if (error.response.data?.redirect) {
                window.location.href = error.response.data.redirect;
                return Promise.reject(error);
            }
        }
        return Promise.reject(error);
    }
);

window.axios = axios;
