<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Printer } from 'lucide-vue-next';

const props = defineProps({
    barangMasuks: Object,
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
    router.get(route('laporan-barang-masuk.index'), {
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
    props.barangMasuks.data.forEach((item, index) => {
        tableRows += `
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">${index + 1}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.kode_transaksi}</td>
                <td style="border: 1px solid #000; padding: 8px;">${formatDate(item.tanggal)}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.barang.nama_barang}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">${item.jumlah}</td>
                <td style="border: 1px solid #000; padding: 8px;">${item.barang.satuan.nama_satuan}</td>
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
            <title>Laporan Barang Masuk</title>
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
                    font-size: 20px;
                    text-transform: uppercase;
                }
                .periode {
                    text-align: center;
                    margin-bottom: 15px;
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
                .signature {
                    text-align: right;
                    margin-top: 40px;
                }
                .signature-label {
                    font-weight: bold;
                    margin-bottom: 60px;
                }
                .signature-line {
                    border-top: 1px solid #000;
                    width: 200px;
                    margin-left: auto;
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
            <h1>LAPORAN BARANG MASUK</h1>
            <div class="periode">${periodeText}</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th style="text-align: center;">Jumlah Masuk</th>
                        <th>Satuan</th>
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
    <Head title="Laporan Barang Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" class="hidden sm:block" />
                <Home :size="16" class="sm:hidden" />
                <ChevronRight :size="16" class="hidden sm:block" />
                <ChevronRight :size="14" class="sm:hidden" />
                <span class="text-xs sm:text-sm md:text-base">Laporan</span>
                <ChevronRight :size="16" class="hidden sm:block" />
                <ChevronRight :size="14" class="sm:hidden" />
                <span class="font-semibold text-xs sm:text-sm md:text-base">Barang Masuk</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-3 sm:p-4 md:p-6">
                <h2 class="text-base sm:text-lg md:text-xl font-bold text-gray-800">Laporan Barang Masuk</h2>
            </div>

            <div class="p-3 sm:p-4 md:p-6 border-b border-gray-200">
                <h3 class="text-sm sm:text-base md:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Filter Data Barang Masuk</h3>
                
                <div class="flex flex-col gap-3 sm:gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Awal</label>
                            <input 
                                v-model="tanggalAwal"
                                type="date"
                                class="w-full border border-gray-300 rounded px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Tanggal Akhir</label>
                            <input 
                                v-model="tanggalAkhir"
                                type="date"
                                class="w-full border border-gray-300 rounded px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-2">
                        <button 
                            @click="tampilkanData"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded transition text-xs sm:text-sm md:text-base"
                        >
                            Tampilkan
                        </button>
                        
                        <button 
                            @click="cetakLaporan"
                            class="bg-green-500 hover:bg-green-600 text-white px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded transition flex items-center justify-center gap-1 sm:gap-2 text-xs sm:text-sm md:text-base"
                        >
                            <Printer :size="14" class="sm:w-4 sm:h-4 md:w-[18px] md:h-[18px]" />
                            <span>Cetak</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-3 sm:p-4 md:p-6">
                <h3 class="text-sm sm:text-base md:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Laporan Barang Masuk</h3>

                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 sm:mb-4 gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-xs sm:text-sm text-gray-700 whitespace-nowrap">Tampilkan</label>
                        <select 
                            v-model="perPage"
                            class="border border-gray-300 rounded px-2 py-1 text-xs sm:text-sm"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs sm:text-sm text-gray-700">data</span>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-xs sm:text-sm text-gray-700 whitespace-nowrap">Cari:</label>
                        <input 
                            v-model="search"
                            type="text"
                            class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm w-full sm:w-48 md:w-64"
                            placeholder="Cari data..."
                        />
                    </div>
                </div>

                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 whitespace-nowrap">No</th>
                                        <th class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 whitespace-nowrap">Kode</th>
                                        <th class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 whitespace-nowrap">Tanggal</th>
                                        <th class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700">Nama Barang</th>
                                        <th class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center text-xs font-semibold text-gray-700 whitespace-nowrap">Jumlah</th>
                                        <th class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center text-xs font-semibold text-gray-700 whitespace-nowrap">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="(item, index) in barangMasuks.data" 
                                        :key="item.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-xs text-gray-700">
                                            {{ barangMasuks.from + index }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-xs text-gray-700 whitespace-nowrap">
                                            {{ item.kode_transaksi }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-xs text-gray-700 whitespace-nowrap">
                                            {{ formatDate(item.tanggal) }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-xs text-gray-700">
                                            {{ item.barang.nama_barang }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-xs text-gray-700 text-center">
                                            {{ item.jumlah }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-xs text-gray-700 text-center">
                                            {{ item.barang.satuan.nama_satuan }}
                                        </td>
                                    </tr>
                                    <tr v-if="barangMasuks.data.length === 0">
                                        <td colspan="6" class="border border-gray-300 px-2 sm:px-3 md:px-4 py-6 sm:py-8 text-center text-gray-500 text-xs sm:text-sm">
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
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-700 text-center sm:text-left px-3 sm:px-0">
                    Menampilkan {{ barangMasuks.from || 0 }} sampai {{ barangMasuks.to || 0 }} dari {{ barangMasuks.total || 0 }} data
                </div>

                <div class="flex items-center justify-center gap-1 sm:gap-2 mt-3 sm:mt-4 flex-wrap px-3 sm:px-0">
                    <button 
                        @click="router.get(barangMasuks.prev_page_url)"
                        :disabled="!barangMasuks.prev_page_url"
                        class="px-2 sm:px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm"
                    >
                        &lt;
                    </button>

                    <button 
                        v-for="link in barangMasuks.links.slice(1, -1)" 
                        :key="link.label"
                        @click="link.url ? router.get(link.url) : null"
                        :class="[
                            'px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm',
                            link.active 
                                ? 'bg-blue-500 text-white border-blue-500' 
                                : 'border-gray-300 hover:bg-gray-100'
                        ]"
                    >
                        {{ link.label }}
                    </button>

                    <button 
                        @click="router.get(barangMasuks.next_page_url)"
                        :disabled="!barangMasuks.next_page_url"
                        class="px-2 sm:px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>