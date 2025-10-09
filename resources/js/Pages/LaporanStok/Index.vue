<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Printer, FileDown } from 'lucide-vue-next';

const props = defineProps({
    barangs: Object,
    filters: Object,
});

const perPage = ref(props.filters.per_page || 10);
const search = ref(props.filters.search || '');
const filterStok = ref(props.filters.filter_stok || 'all');

watch([perPage, search], () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(route('laporan-stok.index'), {
        per_page: perPage.value,
        search: search.value,
        filter_stok: filterStok.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleFilterStok = () => {
    applyFilters();
};

const isStokMinimum = (barang) => {
    return barang.stok <= (barang.stok_minimum || 0);
};

const isStokMaksimum = (barang) => {
    return barang.stok >= (barang.stok_maksimum || 0);
};

const shouldHighlight = (barang) => {
    if (filterStok.value === 'minimum' && isStokMinimum(barang)) {
        return true;
    }
    if (filterStok.value === 'maximum' && isStokMinimum(barang)) {
        return true;
    }
    return false;
};

// Helper function untuk cek stok rendah
const isLowStock = (barang) => {
    return barang.stok < 10;
};

const printReport = () => {
    const printWindow = window.open('', '', 'width=800,height=600');
    
    let tableRows = '';
    props.barangs.data.forEach((barang, index) => {
        let bgColor = '';
        if (shouldHighlight(barang)) {
            bgColor = 'background-color: #fef08a;'; // Yellow for min/max
        } else if (isLowStock(barang)) {
            bgColor = 'background-color: #fee2e2;'; // Red for low stock
        }
        
        const stokDisplay = isLowStock(barang) 
            ? `${barang.stok} <span style="color: #dc2626; font-weight: bold;">(Stok Rendah)</span>`
            : barang.stok;
        
        tableRows += `
            <tr style="${bgColor}">
                <td style="border: 1px solid #ddd; padding: 8px;">${index + 1}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">${barang.id_barang}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">${barang.nama_barang}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">${barang.jenis_barang.nama_jenis}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${stokDisplay}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${barang.satuan.nama_satuan}</td>
            </tr>
        `;
    });

    const filterLabel = filterStok.value === 'all' ? 'Semua Stok' : 
                       filterStok.value === 'minimum' ? 'Stok Minimum' : 'Stok Maksimum';

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Stok Barang</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                h1 {
                    text-align: center;
                    margin-bottom: 10px;
                }
                .info {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th {
                    background-color: #f3f4f6;
                    border: 1px solid #ddd;
                    padding: 10px;
                    text-align: left;
                    font-weight: bold;
                }
                td {
                    border: 1px solid #ddd;
                    padding: 8px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    text-align: right;
                }
                @media print {
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <h1>LAPORAN STOK BARANG</h1>
            <div class="info">
                Filter: ${filterLabel}<br>
                Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                })}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Jenis Barang</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
            <div class="footer">
                Dicetak oleh: Administrator
            </div>
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

const exportToExcel = async () => {
    try {
        const response = await fetch(route('laporan-stok.export-excel') + 
            `?search=${search.value}&filter_stok=${filterStok.value}`);
        const result = await response.json();
        
        if (!result.data || result.data.length === 0) {
            alert('Tidak ada data untuk diekspor');
            return;
        }

        // Create CSV content
        const headers = Object.keys(result.data[0]);
        let csvContent = headers.join(',') + '\n';
        
        result.data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header];
                // Escape commas and quotes
                return typeof value === 'string' && (value.includes(',') || value.includes('"'))
                    ? `"${value.replace(/"/g, '""')}"`
                    : value;
            });
            csvContent += values.join(',') + '\n';
        });

        // Create blob and download
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const filterLabel = filterStok.value === 'all' ? 'semua' : filterStok.value;
        const filename = `laporan_stok_${filterLabel}_${new Date().toISOString().split('T')[0]}.csv`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        alert('Terjadi kesalahan saat mengekspor data');
    }
};
</script>

<template>
    <Head title="Laporan Stok" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span>Laporan</span>
                <ChevronRight :size="16" />
                <span class="font-semibold">Stok</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <!-- Header Card -->
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800">Laporan Stok</h2>
            </div>

            <!-- Filter Section -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Data Stok</h3>
                
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stok</label>
                        <select 
                            v-model="filterStok"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >
                            <option value="all">-- Pilih --</option>
                            <option value="minimum">Stok Minimum</option>
                            <option value="maximum">Stok Maksimum</option>
                        </select>
                    </div>
                    
                    <button 
                        @click="handleFilterStok"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded transition"
                    >
                        Tampilkan
                    </button>
                    
                    <button 
                        @click="printReport"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded transition flex items-center gap-2"
                    >
                        <Printer :size="18" />
                        Cetak
                    </button>
                </div>
            </div>

            <!-- Report Section -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Laporan Stok Barang</h3>
                    <button 
                        @click="exportToExcel"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded transition flex items-center gap-2"
                    >
                        <FileDown :size="18" />
                        Export Excel
                    </button>
                </div>

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
                            v-model="search"
                            type="text"
                            class="border border-gray-300 rounded px-3 py-1 text-sm w-64"
                            placeholder="Cari barang..."
                        />
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center gap-4 text-xs mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-yellow-200 border border-yellow-400 rounded"></div>
                        <span class="text-gray-600">Stok Mencapai Batas (Min/Maks)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                        <span class="text-gray-600">Stok Rendah (&lt; 10)</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Kode Barang</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Jenis Barang</th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">Stok</th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(barang, index) in barangs.data" 
                                :key="barang.id"
                                :class="[
                                    'hover:bg-gray-50',
                                    shouldHighlight(barang) ? 'bg-yellow-200' : '',
                                    isLowStock(barang) && !shouldHighlight(barang) ? 'bg-red-100' : ''
                                ]"
                            >
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ barangs.from + index }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ barang.id_barang }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ barang.nama_barang }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">
                                    {{ barang.jenis_barang.nama_jenis }}
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-center">
                                    <span :class="[
                                        'font-semibold',
                                        isLowStock(barang) ? 'text-red-600' : 'text-gray-700'
                                    ]">
                                        {{ barang.stok }}
                                    </span>
                                    <span v-if="isLowStock(barang)" class="ml-2 text-xs bg-red-500 text-white px-2 py-0.5 rounded-full">
                                        Stok Rendah
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700 text-center">
                                    {{ barang.satuan.nama_satuan }}
                                </td>
                            </tr>
                            <tr v-if="barangs.data.length === 0">
                                <td colspan="6" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                    Tidak ada data barang
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Info -->
                <div class="mt-4 text-sm text-gray-700">
                    Menampilkan {{ barangs.from || 0 }} sampai {{ barangs.to || 0 }} dari {{ barangs.total }} data
                </div>

                <!-- Pagination Controls -->
                <div class="flex items-center justify-center gap-2 mt-4">
                    <button 
                        @click="router.get(barangs.prev_page_url)"
                        :disabled="!barangs.prev_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &lt;
                    </button>

                    <button 
                        v-for="link in barangs.links.slice(1, -1)" 
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
                        @click="router.get(barangs.next_page_url)"
                        :disabled="!barangs.next_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>