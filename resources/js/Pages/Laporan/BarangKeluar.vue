<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Printer } from 'lucide-vue-next';

const props = defineProps({
    barangKeluars: Object,
    filters: Object,
});

const perPage = ref(props.filters?.per_page || 10);
const search = ref(props.filters?.search || '');
const tanggalAwal = ref(props.filters?.tanggal_awal || '');
const tanggalAkhir = ref(props.filters?.tanggal_akhir || '');

// Untuk filter lokal (frontend) pada tabel
const localSearch = ref('');

// Computed property untuk filter data di frontend
const filteredData = computed(() => {
    if (!localSearch.value) {
        return props.barangKeluars.data;
    }

    const query = localSearch.value.toLowerCase();
    
    return props.barangKeluars.data.filter(item => {
        const kodeTransaksi = item.kode_transaksi?.toLowerCase() || '';
        const namaBarang = item.barang?.nama_barang?.toLowerCase() || '';
        const keterangan = item.keterangan?.toLowerCase() || '';
        const satuan = item.barang?.satuan?.nama_satuan?.toLowerCase() || '';
        
        return kodeTransaksi.includes(query) ||
               namaBarang.includes(query) ||
               keterangan.includes(query) ||
               satuan.includes(query);
    });
});

watch([perPage], () => {
    applyFilter();
});

const applyFilter = () => {
    router.get(route('laporan-barang-keluar.index'), {
        per_page: perPage.value,
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const cetakLaporan = () => {
    const printWindow = window.open('', '', 'width=800,height=600');
    
    let tableRows = '';
    filteredData.value.forEach((item, index) => {
        tableRows += `
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">${index + 1}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.kode_transaksi}</td>
                <td style="border: 1px solid #000; padding: 8px;">${formatDate(item.tanggal)}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.barang.nama_barang}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">${item.jumlah}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.barang.satuan.nama_satuan}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.keterangan}</td>
            </tr>
        `;
    });

    const periodeText = tanggalAwal.value && tanggalAkhir.value 
        ? `Periode: ${formatDate(tanggalAwal.value)} - ${formatDate(tanggalAkhir.value)}`
        : 'Semua Data';

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Barang Keluar</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 15mm;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                }
                h1 {
                    text-align: center;
                    margin: 0 0 5px 0;
                    font-size: 12px;
                    text-transform: uppercase;
                }
                .periode {
                    text-align: center;
                    margin-bottom: 5px;
                    font-size: 12px;
                    padding-bottom: 15px;
                    border-bottom: 3px solid #000;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th {
                    background-color: #e5e7eb;
                    border: 1px solid #000;
                    padding: 8px;
                    text-align: left;
                    font-size: 11px;
                    font-weight: bold;
                }
                td {
                    border: 1px solid #000;
                    padding: 8px;
                    font-size: 11px;
                }
                .footer {
                    font-size: 11px;
                }
                .print-date {
                    margin-bottom: 5px;
                }
            </style>
        </head>
        <body>
            <div class="footer">
                <div class="print-date">
                    Dicetak pada: ${new Date().toLocaleDateString('id-ID', { 
                        day: '2-digit', 
                        month: 'long', 
                        year: 'numeric' 
                    })}
                </div>
            </div>
            <h1>LAPORAN BARANG KELUAR</h1>
            <div class="periode">${periodeText}</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th style="text-align: center;">Jumlah Keluar</th>
                        <th>Satuan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
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

        <div class="bg-white rounded-lg shadow-md">
            <!-- Header Card -->
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800">Laporan Barang Keluar</h2>
            </div>

            <!-- Filter Section -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Data Barang Keluar</h3>
                
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Awal</label>
                        <input 
                            v-model="tanggalAwal"
                            type="date"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        />
                    </div>

                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                        <input 
                            v-model="tanggalAkhir"
                            type="date"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        />
                    </div>

                    <button 
                        @click="tampilkanData"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded transition"
                    >
                        Tampilkan
                    </button>
                    
                    <button 
                        @click="cetakLaporan"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded transition flex items-center gap-2"
                    >
                        <Printer :size="18" />
                        Cetak
                    </button>
                </div>
            </div>

            <!-- Report Section -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Laporan Barang Keluar</h3>

                <!-- Data Controls -->
                <div class="flex items-center justify-between mb-4">
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
                            v-model="localSearch"
                            type="text"
                            class="border border-gray-300 rounded px-3 py-1 text-sm w-64"
                            placeholder="Cari data..."
                        />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Kode Transaksi</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">Jumlah Keluar</th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">Satuan</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(item, index) in filteredData" 
                                :key="item.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ index + 1 }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ item.kode_transaksi }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ formatDate(item.tanggal) }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ item.barang.nama_barang }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700 text-center">
                                    {{ item.jumlah }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700 text-center">
                                    {{ item.barang.satuan.nama_satuan }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ item.keterangan }}
                                </td>
                            </tr>
                            <tr v-if="filteredData.length === 0">
                                <td colspan="7" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                    <span v-if="localSearch">
                                        Tidak ada data yang sesuai dengan pencarian
                                    </span>
                                    <span v-else-if="!tanggalAwal || !tanggalAkhir">
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

                <!-- Pagination Info -->
                <div class="mt-4 text-sm text-gray-700">
                    Menampilkan {{ filteredData.length }} dari {{ barangKeluars.total || 0 }} data
                    <span v-if="localSearch" class="text-blue-600">(difilter dari pencarian)</span>
                </div>

                <!-- Pagination Controls -->
                <div class="flex items-center justify-center gap-2 mt-4">
                    <button 
                        @click="router.get(barangKeluars.prev_page_url)"
                        :disabled="!barangKeluars.prev_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &lt;
                    </button>

                    <button 
                        v-for="link in barangKeluars.links.slice(1, -1)" 
                        :key="link.label"
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

                    <button 
                        @click="router.get(barangKeluars.next_page_url)"
                        :disabled="!barangKeluars.next_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>