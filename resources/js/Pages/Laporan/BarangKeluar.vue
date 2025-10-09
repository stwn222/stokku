<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, FileText, Printer } from 'lucide-vue-next';

const props = defineProps({
    barangKeluars: Object,
    filters: Object,
});

const perPage = ref(props.filters?.per_page || 10);
const search = ref(props.filters?.search || '');
const tanggalAwal = ref(props.filters?.tanggal_awal || '');
const tanggalAkhir = ref(props.filters?.tanggal_akhir || '');

watch([perPage, search], () => {
    applyFilter();
});

const applyFilter = () => {
    router.get(route('laporan-barang-keluar.index'), {
        per_page: perPage.value,
        search: search.value,
        tanggal_awal: tanggalAwal.value,
        tanggal_akhir: tanggalAkhir.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const tampilkanData = () => {
    if (!tanggalAwal.value || !tanggalAkhir.value) {
        alert('Mohon pilih tanggal awal dan tanggal akhir');
        return;
    }
    
    if (tanggalAwal.value > tanggalAkhir.value) {
        alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
        return;
    }
    
    applyFilter();
};

const cetakLaporan = () => {
    window.print();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Laporan Barang Keluar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span>Laporan</span>
                <ChevronRight :size="16" />
                <span class="font-semibold">Barang Keluar</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-md p-6 print:hidden">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Filter Data Barang Keluar</h3>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Awal</label>
                        <input 
                            v-model="tanggalAwal"
                            type="date"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                        <input 
                            v-model="tanggalAkhir"
                            type="date"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button 
                        @click="tampilkanData"
                        class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition"
                    >
                        Tampilkan
                    </button>
                    <button 
                        @click="cetakLaporan"
                        class="flex items-center gap-2 px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition"
                    >
                        <Printer :size="18" />
                        Cetak
                    </button>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-lg shadow-md">
                <!-- Print Header -->
                <div class="hidden print:block p-8 text-center border-b border-gray-300">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">LAPORAN BARANG KELUAR</h1>
                    <p class="text-sm text-gray-600">
                        <span v-if="tanggalAwal && tanggalAkhir">
                            Periode: {{ formatDate(tanggalAwal) }} - {{ formatDate(tanggalAkhir) }}
                        </span>
                        <span v-else>Semua Data</span>
                    </p>
                </div>

                <div class="border-b border-gray-200 p-6 print:hidden">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">Laporan Barang Keluar</h2>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-200 print:hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700">Tampilkan</label>
                            <select 
                                v-model="perPage"
                                class="border border-gray-300 rounded px-2 py-1 text-sm"
                            >
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-sm text-gray-700">data</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700">Cari:</label>
                            <input 
                                v-model="search"
                                type="text"
                                class="border border-gray-300 rounded px-3 py-1 text-sm w-64"
                                placeholder="Cari data..."
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Kode Transaksi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Jumlah Keluar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(item, index) in barangKeluars.data" 
                                :key="item.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 text-sm text-gray-700 border">
                                    {{ barangKeluars.from + index }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.kode_transaksi }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatDate(item.tanggal) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang.nama_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.jumlah }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang.satuan.nama_satuan }}</td>
                            </tr>
                            <tr v-if="barangKeluars.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 border">
                                    <span v-if="!tanggalAwal || !tanggalAkhir">
                                        Silakan pilih tanggal untuk menampilkan data
                                    </span>
                                    <span v-else>
                                        Tidak ada data pada periode yang dipilih
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-6 flex items-center justify-between border-t border-gray-200 print:hidden">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ barangKeluars.from || 0 }} sampai {{ barangKeluars.to || 0 }} dari {{ barangKeluars.total || 0 }} data
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            @click="router.get(barangKeluars.prev_page_url)"
                            :disabled="!barangKeluars.prev_page_url"
                            class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            &lt;
                        </button>

                        <template v-for="link in barangKeluars.links.slice(1, -1)" :key="link.label">
                            <button 
                                @click="link.url ? router.get(link.url) : null"
                                :class="[
                                    'px-3 py-1 border rounded',
                                    link.active 
                                        ? 'bg-blue-500 text-white border-blue-500' 
                                        : 'border-gray-300 hover:bg-gray-100'
                                ]"
                            >
                                {{ link.label }}
                            </button>
                        </template>

                        <button 
                            @click="router.get(barangKeluars.next_page_url)"
                            :disabled="!barangKeluars.next_page_url"
                            class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            &gt;
                        </button>
                    </div>
                </div>

                <!-- Print Footer -->
                <div class="hidden print:block p-8 text-right border-t border-gray-300">
                    <p class="text-sm text-gray-600 mb-8">
                        Dicetak pada: {{ new Date().toLocaleDateString('id-ID', { 
                            day: '2-digit', 
                            month: 'long', 
                            year: 'numeric' 
                        }) }}
                    </p>
                    <div class="mt-16">
                        <p class="text-sm font-semibold text-gray-800">Administrator</p>
                        <div class="mt-16 border-t border-gray-800 w-48 ml-auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 1cm;
    }
    
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .print\:hidden {
        display: none !important;
    }
    
    .print\:block {
        display: block !important;
    }
    
    table {
        page-break-inside: auto;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    thead {
        display: table-header-group;
    }
}
</style>