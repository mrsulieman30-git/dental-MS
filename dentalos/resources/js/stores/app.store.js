import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
    state: () => ({
        currentTenantId: null,
        currentLocationId: localStorage.getItem('current_location_id'),
        sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        isLoading: false,
        notifications: [],
    }),

    actions: {
        setLocation(id) {
            this.currentLocationId = id;
            localStorage.setItem('current_location_id', id);
        },

        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
        },

        setLoading(status) {
            this.isLoading = status;
        },

        addNotification(n) {
            const id = Date.now();
            this.notifications.push({ id, ...n });
            setTimeout(() => this.removeNotification(id), 5000);
        },

        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }
});
