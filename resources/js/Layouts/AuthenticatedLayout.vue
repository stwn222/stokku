<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    ChevronDown, 
    Home, 
    Package, 
    TrendingUp, 
    TrendingDown, 
    FileText, 
    Users,
    Menu,
    X
} from 'lucide-vue-next';

const page = usePage();
const showingSidebar = ref(true);
const expandedMenus = ref({
    barang: false,
});

const toggleMenu = (menu) => {
    expandedMenus.value[menu] = !expandedMenus.value[menu];
};

const toggleSidebar = () => {
    showingSidebar.value = !showingSidebar.value;
};
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside 
            :class="[
                'bg-white border-r border-gray-200 transition-all duration-300 flex flex-col',
                showingSidebar ? 'w-64' : 'w-0'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center gap-2 text-white">
                    <div class="w-2 h-8 bg-white rounded"></div>
                    <h1 class="text-xl font-bold">Stokku</h1>
                </div>
            </div>

            <!-- User Info -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                            {{ page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto p-4">
                <!-- Dashboard -->
                <Link 
                    :href="route('dashboard')" 
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                    :class="{ 'bg-gray-100': route().current('dashboard') }"
                >
                    <Home :size="20" />
                    <span class="font-medium">Dashboard</span>
                </Link>

                <!-- MASTER Section -->
                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Master
                    </p>
                </div>

                <!-- Barang Menu (Expandable) -->
                <div class="mb-2">
                    <button 
                        @click="toggleMenu('barang')"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-white bg-blue-500 hover:bg-blue-600 transition"
                    >
                        <div class="flex items-center gap-3">
                            <Package :size="20" />
                            <span class="font-medium">Barang</span>
                        </div>
                        <ChevronDown 
                            :size="18" 
                            :class="{ 'rotate-180': expandedMenus.barang }"
                            class="transition-transform"
                        />
                    </button>

                    <!-- Submenu -->
                    <div 
                        v-show="expandedMenus.barang"
                        class="ml-4 mt-1 space-y-1 border-l-2 border-gray-200"
                    >
                        <Link 
                            :href="route('barang.index')"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-r-lg transition"
                        >
                            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                            Data Barang
                        </Link>
                        <Link 
                            :href="route('jenis-barang.index')"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-r-lg transition"
                        >
                            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                            Jenis Barang
                        </Link>
                        <Link 
                            :href="route('satuan.index')"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-r-lg transition"
                        >
                            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                            Satuan
                        </Link>
                    </div>
                </div>

                <!-- TRANSAKSI Section -->
                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Transaksi
                    </p>
                </div>

                <Link 
                    :href="route('barang-masuk.index')"
                    :class="{ 'bg-gray-100': route().current('barang-masuk.*') }"
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                >
                    <TrendingUp :size="20" />
                    <span class="font-medium">Barang Masuk</span>
                </Link>

                <Link 
                    :href="route('barang-keluar.index')"
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                >
                    <TrendingDown :size="20" />
                    <span class="font-medium">Barang Keluar</span>
                </Link>

                <!-- LAPORAN Section -->
                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Laporan
                    </p>
                </div>

                <Link 
                    :href="route('laporan-stok.index')"
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                >
                    <FileText :size="20" />
                    <span class="font-medium">Laporan Stok</span>
                </Link>

                <Link 
                    href="#"
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                >
                    <FileText :size="20" />
                    <span class="font-medium">Laporan Barang Masuk</span>
                </Link>

                <Link 
                    href="#"
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                >
                    <FileText :size="20" />
                    <span class="font-medium">Laporan Barang Keluar</span>
                </Link>

                <!-- PENGATURAN Section -->
                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Pengaturan
                    </p>
                </div>

                <Link 
                    href="#"
                    class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                >
                    <Users :size="20" />
                    <span class="font-medium">Management User</span>
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-gradient-to-r from-blue-500 to-blue-600 shadow-md">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button 
                            @click="toggleSidebar"
                            class="text-white hover:bg-blue-600 p-2 rounded-lg transition"
                        >
                            <Menu v-if="!showingSidebar" :size="24" />
                            <X v-else :size="24" />
                        </button>
                        
                        <slot name="header" />
                    </div>

                    <!-- User Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 text-white hover:bg-blue-600 px-4 py-2 rounded-lg transition">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-blue-600 font-semibold text-sm">
                                {{ page.props.auth.user.name.charAt(0) }}
                            </div>
                            <ChevronDown :size="18" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <Link 
                                :href="route('profile.edit')"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Profile
                            </Link>
                            <Link 
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>