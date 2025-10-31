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
    User
} from 'lucide-vue-next';

const page = usePage();
const showingSidebar = ref(true);
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
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white border-r border-gray-200 transition-all duration-300 flex flex-col shadow-sm',
                showingSidebar ? 'w-64' : 'w-0 overflow-hidden'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="p-6 bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-600">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <Layers :size="22" class="text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Stokku</h1>
                        <p class="text-xs text-blue-100">Inventory Management</p>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <div v-if="page.props.auth?.user" class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                        {{ page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate text-sm">
                            {{ page.props.auth.user.name }}
                        </p>
                        <p class="text-xs px-2 py-0.5 rounded-md inline-block mt-0.5"
                           :class="isAdministrator ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700'">
                            {{ page.props.auth.user.hak_akses }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <!-- Dashboard -->
                <Link 
                    :href="route('dashboard')" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                    :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('dashboard') }"
                >
                    <Home :size="20" />
                    <span class="text-sm">Dashboard</span>
                </Link>

                <!-- MASTER Section -->
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider">
                        Master Data
                    </p>
                </div>

                <!-- Barang Menu -->
                <div class="space-y-1">
                    <button 
                        @click="toggleMenu('barang')"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 transition-all"
                        :class="{ 'bg-gray-50': expandedMenus.barang }"
                    >
                        <div class="flex items-center gap-3">
                            <Package :size="20" />
                            <span class="text-sm">Barang</span>
                        </div>
                        <ChevronDown 
                            :size="16" 
                            :class="{ 'rotate-180': expandedMenus.barang }"
                            class="transition-transform text-gray-400"
                        />
                    </button>

                    <div 
                        v-show="expandedMenus.barang"
                        class="ml-3 pl-4 space-y-1 border-l-2 border-blue-200"
                    >
                        <Link 
                            :href="route('barang.index')"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                            :class="{ 'text-blue-600 bg-blue-50 font-medium': route().current('barang.*') }"
                        >
                            <span class="w-1.5 h-1.5 bg-current rounded-full"></span>
                            Data Barang
                        </Link>
                        <Link 
                            :href="route('jenis-barang.index')"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                            :class="{ 'text-blue-600 bg-blue-50 font-medium': route().current('jenis-barang.*') }"
                        >
                            <span class="w-1.5 h-1.5 bg-current rounded-full"></span>
                            Jenis Barang
                        </Link>
                        <Link 
                            :href="route('satuan.index')"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                            :class="{ 'text-blue-600 bg-blue-50 font-medium': route().current('satuan.*') }"
                        >
                            <span class="w-1.5 h-1.5 bg-current rounded-full"></span>
                            Satuan
                        </Link>
                    </div>
                </div>

                <!-- Payment Method - Administrator Only -->
                <Link 
                    v-if="isAdministrator"
                    :href="route('payment-method.index')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                    :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('payment-method.*') }"
                >
                    <CreditCard :size="20" />
                    <span class="text-sm">Metode Pembayaran</span>
                </Link>

                <!-- TRANSAKSI Section -->
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider">
                        Transaksi
                    </p>
                </div>

                <Link 
                    :href="route('barang-masuk.index')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                    :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('barang-masuk.*') }"
                >
                    <TrendingUp :size="20" />
                    <span class="text-sm">Barang Masuk</span>
                </Link>

                <!-- Invoice - Administrator Only -->
                <Link 
                    v-if="isAdministrator"
                    :href="route('invoice.index')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                    :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('invoice.*') }"
                >
                    <FileText :size="20" />
                    <span class="text-sm">Invoice</span>
                </Link>

                <!-- LAPORAN Section - Administrator Only -->
                <template v-if="isAdministrator">
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider">
                            Laporan
                        </p>
                    </div>

                    <Link 
                        :href="route('laporan-stok.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                        :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('laporan-stok.*') }"
                    >
                        <Layers :size="20" />
                        <span class="text-sm">Laporan Stok</span>
                    </Link>

                    <Link 
                        :href="route('laporan-barang-masuk.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                        :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('laporan-barang-masuk.*') }"
                    >
                        <TrendingUp :size="20" />
                        <span class="text-sm">Laporan Barang Masuk</span>
                    </Link>

                    <Link 
                        :href="route('laporan-return.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                        :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('laporan-return.*') }"
                    >
                        <RotateCcw :size="20" />
                        <span class="text-sm">Laporan Return</span>
                    </Link>
                </template>

                <!-- PENGATURAN Section - Administrator Only -->
                <template v-if="isAdministrator">
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-blue-600 uppercase tracking-wider">
                            Pengaturan
                        </p>
                    </div>

                    <Link 
                        :href="route('user-management.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
                        :class="{ 'bg-blue-50 text-blue-600 font-medium': route().current('user-management.*') }"
                    >
                        <Users :size="20" />
                        <span class="text-sm">Management User</span>
                    </Link>
                </template>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <Settings :size="14" />
                    <span>v1.0.0 - Stokku</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 shadow-md">
                <div class="flex items-center justify-between px-6 py-3.5">
                    <div class="flex items-center gap-4">
                        <button 
                            @click="toggleSidebar"
                            class="text-white hover:bg-white/20 p-2 rounded-lg transition-all"
                        >
                            <Menu v-if="!showingSidebar" :size="22" />
                            <X v-else :size="22" />
                        </button>
                        
                        <slot name="header" />
                    </div>

                    <!-- User Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-3 text-white hover:bg-white/20 px-3 py-2 rounded-lg transition-all">
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-semibold">{{ page.props.auth?.user?.name || 'User' }}</span>
                                <span class="text-xs text-blue-100">{{ page.props.auth?.user?.hak_akses || 'Role' }}</span>
                            </div>
                            <div v-if="page.props.auth?.user" class="w-9 h-9 bg-white rounded-lg flex items-center justify-center text-blue-600 font-bold shadow-md">
                                {{ page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <ChevronDown :size="16" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-2 z-50 border border-gray-100">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ page.props.auth?.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ page.props.auth?.user?.email }}</p>
                            </div>
                            <Link 
                                :href="route('profile.edit')"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-all"
                            >
                                <User :size="16" />
                                Profile Settings
                            </Link>
                            <div class="border-t border-gray-100 my-1"></div>
                            <Link 
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-all"
                            >
                                <LogOut :size="16" />
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                <!-- Alert Messages -->
                <div v-if="page.props.flash?.success" class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">Success!</p>
                        <p class="text-sm">{{ page.props.flash.success }}</p>
                    </div>
                </div>
                <div v-if="page.props.flash?.error" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">Error!</p>
                        <p class="text-sm">{{ page.props.flash.error }}</p>
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
    width: 6px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>