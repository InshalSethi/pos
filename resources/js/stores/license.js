import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useLicenseStore = defineStore('license', () => {
    const isLicenseActive = ref(false);
    const licenseData = ref(null);
    const isChecking = ref(false);
    const adminName = ref('Administrator');
    const adminEmail = ref('admin@example.com');

    const checkLicenseStatus = async () => {
        isChecking.value = true;
        try {
            const response = await axios.get('/api/license/status');
            licenseData.value = response.data.license || null;
            isLicenseActive.value = response.data.status === 'active';
            
            if (response.data.admin_name) adminName.value = response.data.admin_name;
            if (response.data.admin_email) adminEmail.value = response.data.admin_email;

            return { valid: isLicenseActive.value, status: response.data.status, data: licenseData.value };
        } catch (error) {
            isLicenseActive.value = false;
            if (error.response && error.response.status === 403) {
                licenseData.value = error.response.data.license || null;
                if (error.response.data.admin_name) adminName.value = error.response.data.admin_name;
                if (error.response.data.admin_email) adminEmail.value = error.response.data.admin_email;
                return { valid: false, status: error.response.data.status, data: licenseData.value };
            }
            return { valid: false, status: 'error', message: 'Could not connect to local server' };
        } finally {
            isChecking.value = false;
        }
    };

    const activateLicense = async (licenseKey, deviceId) => {
        try {
            const response = await axios.post('/api/license/activate', {
                license_key: licenseKey,
                device_id: deviceId
            });
            isLicenseActive.value = true;
            licenseData.value = response.data.license;
            return { success: true, data: response.data };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Activation failed.'
            };
        }
    };

    const checkStatus = checkLicenseStatus;
    const isLoaded = computed(() => licenseData.value !== null);
    const isActive = computed(() => isLicenseActive.value);
    return {
        isLicenseActive,
        licenseData,
        isChecking,
        adminName,
        adminEmail,
        checkLicenseStatus,
        checkStatus,
        isLoaded,
        isActive,
        activateLicense
    };
});
