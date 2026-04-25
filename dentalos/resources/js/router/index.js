import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth.store';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        // Auth Routes
        {
            path: '/login',
            name: 'login',
            component: () => import('../views/auth/Login.vue'),
            meta: { requiresAuth: false }
        },
        {
            path: '/forgot-password',
            name: 'forgot-password',
            component: () => import('../views/auth/ForgotPassword.vue'),
            meta: { requiresAuth: false }
        },
        {
            path: '/reset-password/:token',
            name: 'reset-password',
            component: () => import('../views/auth/ResetPassword.vue'),
            meta: { requiresAuth: false }
        },

        // Patient Portal Routes
        {
            path: '/portal',
            component: () => import('../layouts/PortalLayout.vue'),
            meta: { requiresAuth: true, isPortal: true },
            children: [
                { path: '', name: 'portal.dashboard', component: () => import('../views/portal/Dashboard.vue') },
                { path: 'appointments', name: 'portal.appointments', component: () => import('../views/portal/Appointments.vue') },
                { path: 'billing', name: 'portal.billing', component: () => import('../views/portal/Billing.vue') },
                { path: 'records', name: 'portal.records', component: () => import('../views/portal/Records.vue') },
                { path: 'messages', name: 'portal.messages', component: () => import('../views/portal/Messages.vue') },
                { path: 'forms', name: 'portal.forms', component: () => import('../views/portal/Forms.vue') },
                { path: 'profile', name: 'portal.profile', component: () => import('../views/portal/Profile.vue') },
            ]
        },

        // Main App Routes
        {
            path: '/',
            component: () => import('../layouts/DashboardLayout.vue'),
            meta: { requiresAuth: true },
            children: [
                { path: '', redirect: '/dashboard' },
                { path: 'dashboard', name: 'dashboard', component: () => import('../views/Dashboard.vue') },
                { path: 'schedule', name: 'schedule', component: () => import('../views/schedule/ScheduleView.vue') },
                { path: 'schedule/appointment/:id', name: 'appointment.detail', component: () => import('../views/schedule/AppointmentDetail.vue') },
                
                // Patients Module
                { path: 'patients', name: 'patients.index', component: () => import('../views/patients/PatientList.vue') },
                { path: 'patients/new', name: 'patients.create', component: () => import('../views/patients/NewPatient.vue') },
                { 
                    path: 'patients/:id', 
                    component: () => import('../views/patients/PatientHub.vue'),
                    children: [
                        { path: '', name: 'patient.overview', component: () => import('../views/patients/tabs/Overview.vue') },
                        { path: 'chart', name: 'patient.chart', component: () => import('../views/patients/tabs/Chart.vue') },
                        { path: 'perio', name: 'patient.perio', component: () => import('../views/patients/tabs/Perio.vue') },
                        { path: 'treatment-plans', name: 'patient.treatment-plans', component: () => import('../views/patients/tabs/TreatmentPlans.vue') },
                        { path: 'imaging', name: 'patient.imaging', component: () => import('../views/patients/tabs/Imaging.vue') },
                        { path: 'notes', name: 'patient.notes', component: () => import('../views/patients/tabs/Notes.vue') },
                        { path: 'insurance', name: 'patient.insurance', component: () => import('../views/patients/tabs/Insurance.vue') },
                        { path: 'billing', name: 'patient.billing', component: () => import('../views/patients/tabs/Billing.vue') },
                        { path: 'recalls', name: 'patient.recalls', component: () => import('../views/patients/tabs/Recalls.vue') },
                        { path: 'prescriptions', name: 'patient.prescriptions', component: () => import('../views/patients/tabs/Prescriptions.vue') },
                        { path: 'lab-cases', name: 'patient.lab-cases', component: () => import('../views/patients/tabs/LabCases.vue') },
                        { path: 'referrals', name: 'patient.referrals', component: () => import('../views/patients/tabs/Referrals.vue') },
                        { path: 'forms', name: 'patient.forms', component: () => import('../views/patients/tabs/Forms.vue') },
                        { path: 'history', name: 'patient.history', component: () => import('../views/patients/tabs/History.vue') },
                        { path: 'medical-history', name: 'patient.medical-history', component: () => import('../views/patients/tabs/MedicalHistory.vue') },
                    ]
                },

                // Clinical
                { path: 'clinical/chair/:appointmentId', name: 'clinical.chair', component: () => import('../views/clinical/ChairSide.vue') },

                // Billing
                { path: 'billing', name: 'billing.dashboard', component: () => import('../views/billing/BillingDashboard.vue') },
                { path: 'billing/claims', name: 'billing.claims', component: () => import('../views/billing/ClaimsView.vue') },
                { path: 'billing/era', name: 'billing.era', component: () => import('../views/billing/EraView.vue') },
                { path: 'billing/aging', name: 'billing.aging', component: () => import('../views/billing/ArAgingView.vue') },
                { path: 'billing/statements', name: 'billing.statements', component: () => import('../views/billing/StatementsView.vue') },

                // Other Modules
                { path: 'reports', name: 'reports.hub', component: () => import('../views/reports/ReportsHub.vue') },
                { path: 'reports/:type', name: 'reports.view', component: () => import('../views/reports/ReportView.vue') },
                { path: 'lab-cases', name: 'lab-cases.index', component: () => import('../views/lab/LabCases.vue') },
                { path: 'referrals', name: 'referrals.index', component: () => import('../views/referrals/ReferralsView.vue') },
                { path: 'inventory', name: 'inventory.index', component: () => import('../views/inventory/InventoryView.vue') },
                
                // Communications
                { path: 'communications', name: 'communications.hub', component: () => import('../views/communications/CommunicationsHub.vue') },
                { path: 'communications/inbox', name: 'communications.inbox', component: () => import('../views/communications/InboxView.vue') },
                { path: 'communications/campaigns', name: 'communications.campaigns', component: () => import('../views/communications/CampaignsView.vue') },
                { path: 'communications/templates', name: 'communications.templates', component: () => import('../views/communications/TemplatesView.vue') },

                // Settings
                { path: 'settings', name: 'settings.hub', component: () => import('../views/settings/SettingsHub.vue') },
                { path: 'settings/:section', name: 'settings.section', component: () => import('../views/settings/SettingsSection.vue') },
            ]
        }
    ]
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const auth = useAuthStore();
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth !== false);

    if (requiresAuth && !auth.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.name === 'login' && auth.isAuthenticated) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
