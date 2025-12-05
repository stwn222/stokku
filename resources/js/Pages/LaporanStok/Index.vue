<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { DownloadIcon, PrinterIcon } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const barangs = computed(() => page.props.barangs);
const filters = computed(() => page.props.filters);

const tanggalAwal = ref(filters.value.tanggal_awal);
const tanggalAkhir = ref(filters.value.tanggal_akhir);
const filterStok = ref(filters.value.filter_stok);
const perPage = ref(filters.value.per_page);

const handleFilter = () => {
    router.get(route('laporan-stok.index'), {
        tanggal_awal: tanggalAwal.value,
        tanggal_akhir: tanggalAkhir.value,
        filter_stok: filterStok.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePrint = () => {
    const params = new URLSearchParams({
        tanggal_awal: tanggalAwal.value,
        tanggal_akhir: tanggalAkhir.value,
        filter_stok: filterStok.value,
    });
    window.open(route('laporan-stok.print') + '?' + params.toString(), '_blank');
};

const exportExcel = async () => {
    try {
        const response = await fetch(route('laporan-stok.export-excel') + 
            `?tanggal_awal=${tanggalAwal.value}&tanggal_akhir=${tanggalAkhir.value}&filter_stok=${filterStok.value}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error('Export gagal');
        
        const result = await response.json();
        
        const csvContent = [
    ['Kode Barang', 'Nama Barang', 'Jenis Barang', 'Stok Awal', 'Pembelian', 'Penjualan', 'Return', 'Stok Akhir', 'Satuan'].join(','),
    ...result.data.map(row => [
        row['Kode Barang'],
        `"${row['Nama Barang']}"`,
        `"${row['Jenis Barang']}"`,
        row['Stok Awal'],
        row['Pembelian'],
        row['Penjualan'],
        row['Return'] || 0,
        row['Stok Akhir'],
        row['Satuan']
    ].join(','))
].join('\n');
        
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `laporan_stok_${tanggalAwal.value}_${tanggalAkhir.value}.csv`;
        link.click();
    } catch (error) {
        alert('Gagal export data: ' + error.message);
    }
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-lg sm:text-2xl font-bold text-white">Laporan Stok Barang</h1>
            </div>
        </template>

        <div class="flex items-center gap-2 text-white mb-4 sm:mb-6 text-xs sm:text-base">
            <span class="hover:text-blue-100 cursor-pointer" @click="() => $inertia.visit(route('dashboard'))">Home</span>
            <span>></span>
            <span>Laporan Stok</span>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg p-3 sm:p-6 mb-4 sm:mb-6">
            <h2 class="text-sm sm:text-lg font-semibold mb-3 sm:mb-4">Filter Data</h2>
            <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-5 sm:gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Awal</label>
                    <input
                        v-model="tanggalAwal"
                        type="date"
                        class="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Akhir</label>
                    <input
                        v-model="tanggalAkhir"
                        type="date"
                        class="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Filter Stok</label>
                    <select
                        v-model="filterStok"
                        class="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm"
                    >
                        <option value="all">Semua Stok</option>
                        <option value="minimum">Stok Minimum</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Per Halaman</label>
                    <select
                        v-model="perPage"
                        class="w-full px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button
                        @click="handleFilter"
                        class="flex-1 px-3 sm:px-6 py-1.5 sm:py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium text-xs sm:text-sm"
                    >
                        Tampilkan
                    </button>
                    <button
                        @click="handlePrint"
                        class="p-1.5 sm:p-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition"
                        title="Print"
                    >
                        <PrinterIcon :size="18" class="sm:w-5 sm:h-5" />
                    </button>
                    <button
                        @click="exportExcel"
                        class="p-1.5 sm:p-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition"
                        title="Export Excel"
                    >
                        <DownloadIcon :size="18" class="sm:w-5 sm:h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-lg overflow-hidden shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 border-b whitespace-nowrap">No</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Kode Barang</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Nama Barang</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Jenis</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Stok Awal</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Pembelian</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Penjualan</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Return</th>
                            <th class="px-2 sm:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-700 border-b whitespace-nowrap">Stok Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!barangs.data || barangs.data.length === 0">
                            <td colspan="9" class="px-3 sm:px-6 py-6 sm:py-8 text-center text-gray-500 text-xs sm:text-sm">Tidak ada data</td>
                        </tr>
                        <tr v-for="(barang, index) in barangs.data" :key="barang.id" class="border-b hover:bg-gray-50">
                            <td class="px-2 sm:px-6 py-2 text-xs text-gray-900">
                                {{ (barangs.current_page - 1) * barangs.per_page + index + 1 }}
                            </td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-gray-900 whitespace-nowrap">{{ barang.id_barang }}</td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-gray-900">{{ barang.nama_barang }}</td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-gray-900 whitespace-nowrap">{{ barang.jenis_barang?.nama_jenis }}</td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-gray-900 text-right whitespace-nowrap">{{ formatNumber(barang.stok_awal) }}</td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-green-600 text-right font-medium whitespace-nowrap">
                                {{ formatNumber(barang.jumlah_pembelian) }}
                            </td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-red-600 text-right font-medium whitespace-nowrap">
                                {{ formatNumber(barang.jumlah_penjualan) }}
                            </td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-orange-600 text-right font-medium whitespace-nowrap">
                                {{ formatNumber(barang.jumlah_return) }}
                            </td>
                            <td class="px-2 sm:px-6 py-2 text-xs text-gray-900 text-right font-semibold whitespace-nowrap">
                                {{ formatNumber(barang.stok_akhir) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="barangs.last_page > 1" class="px-3 sm:px-6 py-3 bg-gray-50 flex flex-col sm:flex-row justify-between items-center border-t gap-2 sm:gap-3">
                <div class="text-xs text-gray-600 text-center sm:text-left">
                    Menampilkan {{ barangs.from }} - {{ barangs.to }} dari {{ barangs.total }} data
                </div>
                <div class="flex gap-1 flex-wrap justify-center">
                    <component
                        :is="link.url ? 'a' : 'span'"
                        v-for="link in barangs.links"
                        :key="link.label"
                        :href="link.url"
                        @click.prevent="link.url && router.visit(link.url)"
                        v-html="link.label"
                        class="px-2 sm:px-3 py-1 border border-gray-300 rounded-full text-xs cursor-pointer min-w-[28px] text-center"
                        :class="{
                            'bg-blue-500 text-white border-blue-500': link.active,
                            'hover:bg-gray-100': link.url,
                            'opacity-50 cursor-not-allowed': !link.url
                        }"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>