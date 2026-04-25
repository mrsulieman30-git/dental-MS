import { defineStore } from 'pinia';
import { useAuthStore } from './auth.store';

export const useNotificationsStore = defineStore('notifications', {
    state: () => ({
        unreadCount: 0,
        items: [],
    }),

    actions: {
        initRealtime() {
            const auth = useAuthStore();
            if (!auth.user || !window.Echo) return;

            window.Echo.private(`tenant.${auth.user.tenant_id}`)
                .listen('.appointment.created', (e) => {
                    this.addNotification({
                        title: 'New Appointment',
                        message: `New appointment scheduled for ${e.appointment.patient.full_name}`,
                        type: 'info'
                    });
                })
                .listen('.patient.checked_in', (e) => {
                    this.addNotification({
                        title: 'Patient Arrived',
                        message: `${e.appointment.patient.full_name} has checked in.`,
                        type: 'success'
                    });
                });
        },

        addNotification(n) {
            this.items.unshift({ id: Date.now(), ...n, read: false });
            this.unreadCount++;
        },

        markAsRead(id) {
            const item = this.items.find(i => i.id === id);
            if (item && !item.read) {
                item.read = true;
                this.unreadCount--;
            }
        },

        markAllRead() {
            this.items.forEach(i => i.read = true);
            this.unreadCount = 0;
        }
    }
});
