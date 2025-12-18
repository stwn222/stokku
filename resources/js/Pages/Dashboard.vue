<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Package, TrendingUp, TrendingDown, Users, Layers, Box } from 'lucide-vue-next';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalBarang: 0,
            totalBarangMasuk: 0,
            totalBarangKeluar: 0,
            totalJenisBarang: 0,
            totalSatuan: 0,
            totalUser: 0,
        })
    },
    recentBarangMasuk: {
        type: Array,
        default: () => []
    },
    recentBarangKeluar: {
        type: Array,
        default: () => []
    },
});

const searchQuery = ref('');

const filteredBarangMasuk = computed(() => {
    if (!searchQuery.value) return props.recentBarangMasuk;
    const query = searchQuery.value.toLowerCase();
    return props.recentBarangMasuk.filter(item => {
        const namaBarang = item.barang?.nama_barang?.toLowerCase() || '';
        const idBarang = item.barang?.id_barang?.toLowerCase() || '';
        const userName = item.user?.name?.toLowerCase() || '';
        return namaBarang.includes(query) || idBarang.includes(query) || userName.includes(query);
    });
});

const filteredBarangKeluar = computed(() => {
    if (!searchQuery.value) return props.recentBarangKeluar;
    const query = searchQuery.value.toLowerCase();
    return props.recentBarangKeluar.filter(item => {
        const namaBarang = item.barang?.nama_barang?.toLowerCase() || '';
        const idBarang = item.barang?.id_barang?.toLowerCase() || '';
        const userName = item.user?.name?.toLowerCase() || '';
        return namaBarang.includes(query) || idBarang.includes(query) || userName.includes(query);
    });
});

const formatTanggal = (tanggalString) => {
    if (!tanggalString) return '-';
    const date = new Date(tanggalString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year}, ${hours}:${minutes}:${seconds}`;
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-lg md:text-2xl font-bold">Dashboard</span>
            </div>
        </template>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
            <!-- Data Barang -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-2">Total Barang</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ formatNumber(stats.totalBarang) }}</h3>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <Package :size="32" class="text-purple-600" />
                    </div>
                </div>
            </div>

            <!-- Data Barang Masuk -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-2">Total Barang Masuk</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ formatNumber(stats.totalBarangMasuk) }}</h3>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <TrendingUp :size="32" class="text-green-600" />
                    </div>
                </div>
            </div>

            <!-- Data Barang Keluar -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-2">Total Barang Keluar</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ formatNumber(stats.totalBarangKeluar) }}</h3>
                    </div>
                    <div class="bg-orange-100 p-4 rounded-full">
                        <TrendingDown :size="32" class="text-orange-600" />
                    </div>
                </div>
            </div>

            <!-- Data Jenis Barang -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-2">Jenis Barang</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ formatNumber(stats.totalJenisBarang) }}</h3>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <Layers :size="32" class="text-yellow-600" />
                    </div>
                </div>
            </div>

            <!-- Data Satuan -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-2">Satuan</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ formatNumber(stats.totalSatuan) }}</h3>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <Box :size="32" class="text-blue-600" />
                    </div>
                </div>
            </div>

            <!-- Data User -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-emerald-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-2">Total User</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ formatNumber(stats.totalUser) }}</h3>
                    </div>
                    <div class="bg-emerald-100 p-4 rounded-full">
                        <Users :size="32" class="text-emerald-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Aktivitas Terbaru</h2>
                </div>
            </div>

            <div class="p-6">
                <!-- Search -->
                <div class="mb-6">
                    <div class="relative max-w-md">
                        <input 
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Cari barang, kode, atau user..."
                            v-model="searchQuery"
                        />
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Barang Masuk -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <TrendingUp :size="20" class="text-green-600" />
                        <h3 class="text-lg font-semibold text-gray-800">Barang Masuk Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full min-w-[800px]">
                            <thead class="bg-gradient-to-r from-green-50 to-green-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Kode Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Nama Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Waktu</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">User</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in filteredBarangMasuk" :key="index" class="hover:bg-green-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ item.barang?.id_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ item.barang?.nama_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ formatTanggal(item.created_at) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                            +{{ item.jumlah }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ item.user?.name || '-' }}</td>
                                </tr>
                                <tr v-if="!filteredBarangMasuk || filteredBarangMasuk.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p>{{ searchQuery ? 'Tidak ada data yang sesuai' : 'Belum ada data barang masuk' }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Barang Keluar -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <TrendingDown :size="20" class="text-orange-600" />
                        <h3 class="text-lg font-semibold text-gray-800">Barang Keluar Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full min-w-[800px]">
                            <thead class="bg-gradient-to-r from-orange-50 to-orange-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Kode Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Nama Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Waktu</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700">Jumlah</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">User</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in filteredBarangKeluar" :key="index" class="hover:bg-orange-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ item.barang?.id_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ item.barang?.nama_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ formatTanggal(item.created_at) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">
                                            -{{ item.jumlah }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ item.user?.name || '-' }}</td>
                                </tr>
                                <tr v-if="!filteredBarangKeluar || filteredBarangKeluar.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p>{{ searchQuery ? 'Tidak ada data yang sesuai' : 'Belum ada data barang keluar' }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>