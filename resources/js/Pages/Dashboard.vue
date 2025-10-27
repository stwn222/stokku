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

// 1. Buat ref untuk menampung nilai input pencarian
const searchQuery = ref('');

// 2. Buat computed property untuk memfilter 'recentBarangMasuk'
const filteredBarangMasuk = computed(() => {
    if (!searchQuery.value) {
        return props.recentBarangMasuk; // Kembalikan semua jika search kosong
    }

    const query = searchQuery.value.toLowerCase();

    return props.recentBarangMasuk.filter(item => {
        // Cek null/undefined dengan aman menggunakan optional chaining (?.)
        const namaBarang = item.barang?.nama_barang?.toLowerCase() || '';
        const idBarang = item.barang?.id_barang?.toLowerCase() || '';
        const userName = item.user?.name?.toLowerCase() || '';

        // Kembalikan true jika salah satu kriteria cocok
        return namaBarang.includes(query) ||
            idBarang.includes(query) ||
            userName.includes(query);
    });
});

// 3. Buat computed property untuk memfilter 'recentBarangKeluar'
const filteredBarangKeluar = computed(() => {
    if (!searchQuery.value) {
        return props.recentBarangKeluar; // Kembalikan semua jika search kosong
    }

    const query = searchQuery.value.toLowerCase();

    return props.recentBarangKeluar.filter(item => {
        const namaBarang = item.barang?.nama_barang?.toLowerCase() || '';
        const idBarang = item.barang?.id_barang?.toLowerCase() || '';
        const userName = item.user?.name?.toLowerCase() || '';

        return namaBarang.includes(query) ||
            idBarang.includes(query) ||
            userName.includes(query);
    });
});

const formatTanggal = (tanggalString) => {
    if (!tanggalString) return '-'; // Kembalikan strip jika tanggal tidak ada

    const date = new Date(tanggalString);

    // Ambil komponen tanggal
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() dimulai dari 0, jadi +1
    const year = date.getFullYear();

    // Ambil komponen waktu
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}/${month}/${year} - ${hours}:${minutes}`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-2xl font-bold">Dashboard</span>
            </div>
        </template>

        <!-- Statistics Cards - Top Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Data Barang -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Data Barang</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ stats.totalBarang }}</h3>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <Package :size="32" class="text-purple-600" />
                    </div>
                </div>
            </div>

            <!-- Data Barang Masuk -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Data Barang Masuk</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ stats.totalBarangMasuk }}</h3>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <TrendingUp :size="32" class="text-green-600" />
                    </div>
                </div>
            </div>

            <!-- Data Barang Keluar -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Data Barang Keluar</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ stats.totalBarangKeluar }}</h3>
                    </div>
                    <div class="bg-orange-100 p-4 rounded-full">
                        <TrendingDown :size="32" class="text-orange-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards - Bottom Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Data Jenis Barang -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Data Jenis Barang</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ stats.totalJenisBarang }}</h3>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <Layers :size="32" class="text-yellow-600" />
                    </div>
                </div>
            </div>

            <!-- Data Satuan -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Data Satuan</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ stats.totalSatuan }}</h3>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <Box :size="32" class="text-blue-600" />
                    </div>
                </div>
            </div>

            <!-- Data User -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Data User</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ stats.totalUser }}</h3>
                    </div>
                    <div class="bg-emerald-100 p-4 rounded-full">
                        <Users :size="32" class="text-emerald-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-6">
                <div class="flex items-center gap-3">
                    <div class="bg-gray-800 p-2 rounded-full">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Aktivitas Terbaru</h2>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="text-sm text-gray-700">Tampilkan</label>
                    <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <option value="4">4</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <span class="text-sm text-gray-700">data</span>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700">Cari:</label>
                        <input 
                            type="text"
                            class="border border-gray-300 rounded px-3 py-1 text-sm w-64"
                            placeholder="Cari..."
                            v-model="searchQuery"
                        />
                    </div>
                </div>

                <!-- Barang Masuk Table -->
                <div class="mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Kode Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Barang Masuk</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in filteredBarangMasuk" :key="index" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang?.id_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang?.nama_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatTanggal(item.tanggal) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">
                                        <span class="text-green-600 font-semibold">{{ item.jumlah }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.user?.name || '-' }}</td>
                                </tr>
                                <tr v-if="!filteredBarangMasuk || filteredBarangMasuk.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 border">
                                        {{ searchQuery ? 'Tidak ada data yang sesuai dengan pencarian' : 'Tidak ada data barang masuk' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Barang Keluar Table -->
                <div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Kode Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Barang Keluar</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in filteredBarangKeluar" :key="index" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang?.id_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang?.nama_barang || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatTanggal(item.tanggal) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">
                                        <span class="text-orange-600 font-semibold">{{ item.jumlah }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.user?.name || '-' }}</td>
                                </tr>
                                <tr v-if="!filteredBarangKeluar || filteredBarangKeluar.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 border">
                                        {{ searchQuery ? 'Tidak ada data yang sesuai dengan pencarian' : 'Tidak ada data barang keluar' }}
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