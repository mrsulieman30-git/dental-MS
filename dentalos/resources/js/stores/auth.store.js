import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('auth_token'),
        permissions: [],
        tenant: null,
        isLoading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        hasPermission: (state) => (permission) => state.permissions.includes(permission),
    },

    actions: {
        async login(credentials) {
            this.isLoading = true;
            try {
                const response = await axios.post('/auth/login', credentials);
                this.token = response.data.data.token;
                this.user = response.data.data.user;
                this.permissions = response.data.data.permissions;
                
                localStorage.setItem('auth_token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                
                return response.data;
            } catch (error) {
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            try {
                await axios.post('/auth/logout');
            } finally {
                this.token = null;
                this.user = null;
                this.permissions = [];
                localStorage.removeItem('auth_token');
                delete axios.defaults.headers.common['Authorization'];
                window.location.href = '/login';
            }
        },

        async fetchUser() {
            if (!this.token) return;
            
            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const response = await axios.get('/auth/me');
                this.user = response.data.data.user;
                this.permissions = response.data.data.permissions;
                this.tenant = response.data.data.user.tenant;
            } catch (error) {
                this.logout();
            }
        }
    }
});
