import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import VueToastification from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import AutoComplete from 'primevue/autocomplete';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import ProgressBar from 'primevue/progressbar';
import Popover from 'primevue/popover';
import Skeleton from 'primevue/skeleton';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Tooltip from 'primevue/tooltip';

// Root Component
import App from './App.vue';
import router from './router';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            prefix: 'p',
            darkModeSelector: '.dark-mode',
            cssLayer: false
        }
    }
});

app.use(ToastService);
app.use(ConfirmationService);
app.use(VueToastification, { timeout: 3000, position: 'top-right' });

// Register Components
app.component('p-toast', Toast);
app.component('p-confirm-dialog', ConfirmDialog);
app.component('p-button', Button);
app.component('p-input-text', InputText);
app.component('p-data-table', DataTable);
app.component('p-column', Column);
app.component('p-avatar', Avatar);
app.component('p-tag', Tag);
app.component('p-dialog', Dialog);
app.component('p-textarea', Textarea);
app.component('p-auto-complete', AutoComplete);
app.component('p-select', Select);
app.component('p-calendar', DatePicker); // Aliased for backward compatibility in my previous code
app.component('p-input-number', InputNumber);
app.component('p-checkbox', Checkbox);
app.component('p-progress-bar', ProgressBar);
app.component('p-popover', Popover);
app.component('p-skeleton', Skeleton);
app.component('p-tab-view', TabView);
app.component('p-tab-panel', TabPanel);
app.directive('tooltip', Tooltip);

app.mount('#app');
