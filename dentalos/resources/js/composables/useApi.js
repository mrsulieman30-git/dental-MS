import { ref } from 'vue';
import axios from 'axios';

export function useApi() {
    const data = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const call = async (method, url, payload = null, params = null) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios({
                method,
                url,
                data: payload,
                params
            });
            data.value = response.data.data;
            return response.data;
        } catch (err) {
            error.value = err.response?.data || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        data,
        loading,
        error,
        get: (url, params) => call('get', url, null, params),
        post: (url, payload) => call('post', url, payload),
        put: (url, payload) => call('put', url, payload),
        patch: (url, payload) => call('patch', url, payload),
        delete: (url) => call('delete', url),
    };
}
