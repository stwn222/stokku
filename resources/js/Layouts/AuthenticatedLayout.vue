<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    ChevronDown, 
    Home, 
    Package, 
    TrendingUp,
    FileText, 
    Users,
    Menu,
    X,
    CreditCard,
    RotateCcw,
    Layers,
    Settings,
    LogOut,
    User,
    ChevronRight
} from 'lucide-vue-next';

const page = usePage();
const showingSidebar = ref(false); // Changed to false - sidebar closed by default
const expandedMenus = ref({
    barang: false,
    transaksi: false,
    laporan: false,
});

const isAdministrator = computed(() => {
    return page.props.auth?.user?.hak_akses === 'Administrator';
});

const toggleMenu = (menu) => {
    expandedMenus.value[menu] = !expandedMenus.value[menu];
};

const toggleSidebar = () => {
    showingSidebar.value = !showingSidebar.value;
};
</script>

<template>
    <div class="flex h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Sidebar Overlay for Mobile -->
        <div 
            v-if="showingSidebar"
            @click="toggleSidebar"
            class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed lg:relative h-full bg-white border-r border-gray-200 transition-all duration-300 flex flex-col z-50',
                showingSidebar ? 'w-72 shadow-2xl' : 'w-0 lg:w-16 overflow-hidden'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-gray-200">
                <div v-if="showingSidebar" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <Layers :size="20" class="text-white" />
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Stokku</h1>
                        <p class="text-xs text-gray-500">Inventory System</p>
                    </div>
                </div>
                <div v-else class="flex justify-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <Layers :size="20" class="text-white" />
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <!-- Dashboard -->
                <Link 
                    :href="route('dashboard')" 
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                        route().current('dashboard') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                    ]"
                    :title="!showingSidebar ? 'Dashboard' : ''"
                >
                    <Home :size="20" class="flex-shrink-0" />
                    <span v-if="showingSidebar" class="text-sm font-medium">Dashboard</span>
                </Link>

                <!-- MASTER Section -->
                <div v-if="showingSidebar" class="pt-4 pb-2">
                    <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                        <div class="h-px flex-1 bg-gradient-to-r from-blue-200 to-transparent"></div>
                        Master Data
                        <div class="h-px flex-1 bg-gradient-to-l from-blue-200 to-transparent"></div>
                    </p>
                </div>
                <div v-else class="pt-2">
                    <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent mx-2"></div>
                </div>

                <!-- Barang Menu -->
                <div class="space-y-1">
                    <button 
                        @click="toggleMenu('barang')"
                        :class="[
                            'w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 transition-all group',
                            expandedMenus.barang ? 'bg-gray-50' : ''
                        ]"
                        :title="!showingSidebar ? 'Barang' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <Package :size="20" class="flex-shrink-0" />
                            <span v-if="showingSidebar" class="text-sm font-medium">Barang</span>
                        </div>
                        <ChevronDown 
                            v-if="showingSidebar"
                            :size="16" 
                            :class="{ 'rotate-180': expandedMenus.barang }"
                            class="transition-transform text-gray-400"
                        />
                    </button>

                    <div 
                        v-show="expandedMenus.barang && showingSidebar"
                        class="ml-3 pl-4 space-y-1 border-l-2 border-blue-200"
                    >
                        <Link 
                            :href="route('barang.index')"
                            :class="[
                                'flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all group',
                                route().current('barang.*') ? 'text-blue-600 bg-blue-50 font-medium' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50'
                            ]"
                        >
                            <ChevronRight :size="14" class="flex-shrink-0" />
                            Data Barang
                        </Link>
                        <Link 
                            :href="route('jenis-barang.index')"
                            :class="[
                                'flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all group',
                                route().current('jenis-barang.*') ? 'text-blue-600 bg-blue-50 font-medium' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50'
                            ]"
                        >
                            <ChevronRight :size="14" class="flex-shrink-0" />
                            Jenis Barang
                        </Link>
                        <Link 
                            :href="route('satuan.index')"
                            :class="[
                                'flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all group',
                                route().current('satuan.*') ? 'text-blue-600 bg-blue-50 font-medium' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50'
                            ]"
                        >
                            <ChevronRight :size="14" class="flex-shrink-0" />
                            Satuan
                        </Link>
                    </div>
                </div>

                <!-- Payment Method - Administrator Only -->
                <Link 
                    v-if="isAdministrator"
                    :href="route('payment-method.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                        route().current('payment-method.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                    ]"
                    :title="!showingSidebar ? 'Metode Pembayaran' : ''"
                >
                    <CreditCard :size="20" class="flex-shrink-0" />
                    <span v-if="showingSidebar" class="text-sm font-medium">Metode Pembayaran</span>
                </Link>

                <!-- TRANSAKSI Section -->
                <div v-if="showingSidebar" class="pt-4 pb-2">
                    <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                        <div class="h-px flex-1 bg-gradient-to-r from-blue-200 to-transparent"></div>
                        Transaksi
                        <div class="h-px flex-1 bg-gradient-to-l from-blue-200 to-transparent"></div>
                    </p>
                </div>
                <div v-else class="pt-2">
                    <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent mx-2"></div>
                </div>

                <Link 
                    :href="route('barang-masuk.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                        route().current('barang-masuk.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                    ]"
                    :title="!showingSidebar ? 'Barang Masuk' : ''"
                >
                    <TrendingUp :size="20" class="flex-shrink-0" />
                    <span v-if="showingSidebar" class="text-sm font-medium">Barang Masuk</span>
                </Link>

                <!-- Invoice - Administrator Only -->
                <Link 
                    v-if="isAdministrator"
                    :href="route('invoice.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                        route().current('invoice.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                    ]"
                    :title="!showingSidebar ? 'Invoice' : ''"
                >
                    <FileText :size="20" class="flex-shrink-0" />
                    <span v-if="showingSidebar" class="text-sm font-medium">Invoice</span>
                </Link>

                <!-- LAPORAN Section - Administrator Only -->
                <template v-if="isAdministrator">
                    <div v-if="showingSidebar" class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                            <div class="h-px flex-1 bg-gradient-to-r from-blue-200 to-transparent"></div>
                            Laporan
                            <div class="h-px flex-1 bg-gradient-to-l from-blue-200 to-transparent"></div>
                        </p>
                    </div>
                    <div v-else class="pt-2">
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent mx-2"></div>
                    </div>

                    <Link 
                        :href="route('laporan-stok.index')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                            route().current('laporan-stok.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                        ]"
                        :title="!showingSidebar ? 'Laporan Stok' : ''"
                    >
                        <Layers :size="20" class="flex-shrink-0" />
                        <span v-if="showingSidebar" class="text-sm font-medium">Laporan Stok</span>
                    </Link>

                    <Link 
                        :href="route('laporan-barang-masuk.index')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                            route().current('laporan-barang-masuk.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                        ]"
                        :title="!showingSidebar ? 'Laporan Barang Masuk' : ''"
                    >
                        <TrendingUp :size="20" class="flex-shrink-0" />
                        <span v-if="showingSidebar" class="text-sm font-medium">Laporan Barang Masuk</span>
                    </Link>

                    <Link 
                        :href="route('laporan-return.index')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                            route().current('laporan-return.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                        ]"
                        :title="!showingSidebar ? 'Laporan Return' : ''"
                    >
                        <RotateCcw :size="20" class="flex-shrink-0" />
                        <span v-if="showingSidebar" class="text-sm font-medium">Laporan Return</span>
                    </Link>
                </template>

                <!-- PENGATURAN Section - Administrator Only -->
                <template v-if="isAdministrator">
                    <div v-if="showingSidebar" class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                            <div class="h-px flex-1 bg-gradient-to-r from-blue-200 to-transparent"></div>
                            Pengaturan
                            <div class="h-px flex-1 bg-gradient-to-l from-blue-200 to-transparent"></div>
                        </p>
                    </div>
                    <div v-else class="pt-2">
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent mx-2"></div>
                    </div>

                    <Link 
                        :href="route('user-management.index')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 transition-all group',
                            route().current('user-management.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : ''
                        ]"
                        :title="!showingSidebar ? 'Management User' : ''"
                    >
                        <Users :size="20" class="flex-shrink-0" />
                        <span v-if="showingSidebar" class="text-sm font-medium">Management User</span>
                    </Link>
                </template>
            </nav>

            <!-- Sidebar Footer -->
            <div class="border-t border-gray-200">
                <!-- Profile Settings Button -->
                <Link 
                    v-if="showingSidebar"
                    :href="route('profile.edit')"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-all border-b border-gray-200"
                >
                    <User :size="18" />
                    <span class="font-medium">Profile Settings</span>
                </Link>
                
                <!-- Logout Button -->
                <Link 
                    v-if="showingSidebar"
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-all border-b border-gray-200"
                >
                    <LogOut :size="18" />
                    <span class="font-medium">Log Out</span>
                </Link>
                
                <!-- Version Info -->
                <div v-if="showingSidebar" class="p-3 bg-gray-50">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <Settings :size="14" />
                        <span class="font-medium">v1.0.0</span>
                        <span class="text-gray-400">•</span>
                        <span>Stokku</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button 
                            @click="toggleSidebar"
                            class="text-gray-600 hover:text-blue-600 hover:bg-blue-50 p-2.5 rounded-xl transition-all"
                        >
                            <Menu v-if="!showingSidebar" :size="22" />
                            <X v-else :size="22" />
                        </button>
                        
                        <slot name="header" />
                    </div>

                    <!-- User Info Display -->
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-gray-200">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-semibold text-gray-900">{{ page.props.auth?.user?.name || 'User' }}</span>
                            <span class="text-xs text-gray-500">{{ page.props.auth?.user?.hak_akses || 'Role' }}</span>
                        </div>
                        <div v-if="page.props.auth?.user" class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg ring-2 ring-gray-100">
                            {{ page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">
                <!-- Alert Messages -->
                <div v-if="page.props.flash?.success" class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white flex-shrink-0 shadow-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-green-900">Success!</p>
                        <p class="text-sm text-green-700">{{ page.props.flash.success }}</p>
                    </div>
                </div>
                <div v-if="page.props.flash?.error" class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 rounded-2xl shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center text-white flex-shrink-0 shadow-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-red-900">Error!</p>
                        <p class="text-sm text-red-700">{{ page.props.flash.error }}</p>
                    </div>
                </div>
                
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
nav::-webkit-scrollbar {
    width: 5px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #cbd5e1, #94a3b8);
    border-radius: 10px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #94a3b8, #64748b);
}

/* Smooth transitions */
* {
    transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>