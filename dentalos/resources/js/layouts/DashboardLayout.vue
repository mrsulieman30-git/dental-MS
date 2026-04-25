<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.store';
import { useAppStore } from '../stores/app.store';
import { useNotificationsStore } from '../stores/notifications.store';
import { 
    LayoutDashboard, Calendar, Users, Stethoscope, 
    DollarSign, BarChart3, FlaskConical, Share2, 
    Box, MessageSquare, Settings, Bell, Search,
    Menu as MenuIcon, ChevronLeft, ChevronRight,
    LogOut, User as UserIcon, Shield
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const app = useAppStore();
const notifications = useNotificationsStore();

const sidebarCollapsed = computed(() => app.sidebarCollapsed);
const user = computed(() => auth.user);

const menuItems = [
    { label: 'Dashboard', icon: LayoutDashboard, path: '/dashboard' },
    { label: 'Schedule', icon: Calendar, path: '/schedule' },
    { label: 'Patients', icon: Users, path: '/patients' },
    { label: 'Clinical', icon: Stethoscope, path: '/clinical' },
    { label: 'Billing', icon: DollarSign, path: '/billing' },
    { label: 'Reports', icon: BarChart3, path: '/reports' },
    { label: 'Lab Cases', icon: FlaskConical, path: '/lab-cases' },
    { label: 'Referrals', icon: Share2, path: '/referrals' },
    { label: 'Inventory', icon: Box, path: '/inventory' },
    { label: 'Communications', icon: MessageSquare, path: '/communications', badge: 3 },
    { label: 'Settings', icon: Settings, path: '/settings' },
];

const logout = async () => {
    await auth.logout();
};

onMounted(() => {
    notifications.initRealtime();
});
</script>

<template>
    <div class="flex h-screen bg-slate-50 dark:bg-slate-950 overflow-hidden">
        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-[#1A3C5E] text-white flex flex-col transition-all duration-300 z-30 shadow-2xl relative',
                sidebarCollapsed ? 'w-20' : 'w-64'
            ]"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-white/10 shrink-0">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                    <Shield class="w-5 h-5 text-[#1A3C5E]" />
                </div>
                <span v-if="!sidebarCollapsed" class="ml-3 font-bold text-xl tracking-tight">DentalOS</span>
            </div>

            <!-- Location Switcher (Mock) -->
            <div class="px-4 py-4 border-b border-white/10">
                <div 
                    class="bg-white/5 rounded-xl p-2 flex items-center hover:bg-white/10 cursor-pointer transition-colors"
                    :class="{ 'justify-center': sidebarCollapsed }"
                >
                    <div class="w-8 h-8 bg-primary-400/20 rounded flex items-center justify-center shrink-0">
                        <Box class="w-4 h-4 text-primary-200" />
                    </div>
                    <div v-if="!sidebarCollapsed" class="ml-3 overflow-hidden">
                        <p class="text-xs text-primary-300 font-medium">Current Location</p>
                        <p class="text-sm font-semibold truncate">Main Street Dental</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar">
                <router-link 
                    v-for="item in menuItems" 
                    :key="item.path"
                    :to="item.path"
                    class="flex items-center px-3 py-2.5 rounded-xl transition-all group relative"
                    :class="[
                        route.path.startsWith(item.path) 
                            ? 'bg-white/15 text-white shadow-lg' 
                            : 'text-primary-200 hover:bg-white/5 hover:text-white'
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                    <span v-if="!sidebarCollapsed" class="ml-3 font-medium">{{ item.label }}</span>
                    
                    <!-- Badge -->
                    <span 
                        v-if="item.badge" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm"
                    >
                        {{ item.badge }}
                    </span>

                    <!-- Tooltip for collapsed -->
                    <div v-if="sidebarCollapsed" class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">
                        {{ item.label }}
                    </div>
                </router-link>
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-white/10 bg-black/10">
                <div 
                    class="flex items-center group cursor-pointer"
                    :class="{ 'justify-center': sidebarCollapsed }"
                >
                    <p-avatar 
                        :image="user?.avatar_url || 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y'" 
                        shape="circle" 
                        class="border-2 border-white/20 group-hover:border-white/50 transition-colors"
                    />
                    <div v-if="!sidebarCollapsed" class="ml-3 flex-1 overflow-hidden">
                        <p class="text-sm font-bold truncate">{{ user?.full_name || 'Dr. Smith' }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-primary-300 font-bold">{{ user?.role || 'Administrator' }}</p>
                    </div>
                    <button v-if="!sidebarCollapsed" @click="logout" class="text-primary-300 hover:text-white transition-colors">
                        <LogOut class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Collapse Toggle -->
            <button 
                @click="app.toggleSidebar()"
                class="absolute -right-3 top-20 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full p-1 shadow-md text-slate-500 hover:text-primary-500 transition-colors z-40 hidden md:block"
            >
                <ChevronLeft v-if="!sidebarCollapsed" class="w-4 h-4" />
                <ChevronRight v-else class="w-4 h-4" />
            </button>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8 shrink-0 z-20 shadow-sm">
                <div class="flex items-center gap-4">
                    <button class="md:hidden text-slate-500 hover:text-primary-500">
                        <MenuIcon class="w-6 h-6" />
                    </button>
                    <!-- Breadcrumbs (Mock) -->
                    <div class="hidden sm:flex items-center text-sm text-slate-500 gap-2">
                        <span class="hover:text-primary-500 cursor-pointer">App</span>
                        <span class="text-slate-300">/</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ route.name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <!-- Global Search -->
                    <div class="hidden md:flex items-center bg-slate-100 dark:bg-slate-800 rounded-full px-4 py-1.5 w-64 border border-transparent focus-within:border-primary-300 focus-within:bg-white transition-all">
                        <Search class="w-4 h-4 text-slate-400" />
                        <input type="text" placeholder="Search anything... (⌘K)" class="bg-transparent border-none focus:ring-0 text-sm ml-2 w-full outline-none" />
                    </div>

                    <!-- Notifications -->
                    <button class="relative text-slate-500 hover:text-primary-500 transition-colors group">
                        <Bell class="w-5 h-5" />
                        <span v-if="notifications.unreadCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full border-2 border-white">
                            {{ notifications.unreadCount }}
                        </span>
                    </button>

                    <!-- Date/Time -->
                    <div class="hidden lg:block text-right">
                        <p class="text-xs font-bold text-slate-900 dark:text-white uppercase">{{ new Date().toLocaleDateString('en-US', { weekday: 'long' }) }}</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest">{{ new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</p>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <div class="flex-1 overflow-y-auto p-8 bg-slate-50 dark:bg-slate-950 custom-scrollbar relative">
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>

                <!-- Global Loading Overlay -->
                <div v-if="app.isLoading" class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-[2px] flex items-center justify-center z-50">
                    <p-progress-bar mode="indeterminate" class="w-64 h-1 rounded-full shadow-sm" />
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 9999px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
}
</style>
