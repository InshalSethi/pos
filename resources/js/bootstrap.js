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

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const responseData = error.response?.data;
        const isDeactivatedError = responseData?.error === 'ACCOUNT_INACTIVE' || 
            (responseData?.message && responseData.message.toLowerCase().includes('deactivated'));

        if (isDeactivatedError) {
            try {
                const activePinia = window.__pinia;
                if (activePinia && activePinia._s?.has('auth')) {
                    activePinia._s.get('auth').triggerDeactivation();
                }
            } catch (e) {}
            localStorage.removeItem('auth_token');
            return Promise.reject(error);
        }

        // If auth store is already deactivated, suppress auto-redirect to /login
        try {
            const activePinia = window.__pinia;
            if (activePinia && activePinia._s?.has('auth')) {
                const authStore = activePinia._s.get('auth');
                if (authStore.isDeactivated) {
                    return Promise.reject(error);
                }
            }
        } catch (e) {}

        if (error.response?.status === 401 || error.response?.status === 403) {
            const url = error.config?.url || '';
            // If it's a login attempt, let the component handle the error and show validation message
            if (url.includes('/login')) {
                return Promise.reject(error);
            }

            if (url.includes('/admin/api/') || url.includes('/admin/')) {
                localStorage.removeItem('admin_token');
                window.location.href = '/admin/login';
                return Promise.reject(error);
            }

            // If the user was logged in when this 401/403 occurred (e.g. employee account deactivated),
            // trigger the deactivation modal overlay instead of forcing an immediate page redirect to /login.
            try {
                const activePinia = window.__pinia;
                if (activePinia && activePinia._s?.has('auth')) {
                    const authStore = activePinia._s.get('auth');
                    if (authStore.isAuthenticated || authStore.isDeactivated || authStore.user) {
                        authStore.triggerDeactivation();
                        return Promise.reject(error);
                    }
                }
            } catch (e) {}

            if (error.response?.data?.redirect) {
                window.location.href = error.response.data.redirect;
                return Promise.reject(error);
            }

            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

window.axios = axios;
