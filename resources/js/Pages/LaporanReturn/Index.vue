<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { DownloadIcon, PrinterIcon } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const returns = computed(() => page.props.returns);
const filters = computed(() => page.props.filters);

const startDate = ref(filters.value.start_date);
const endDate = ref(filters.value.end_date);

const handleFilter = () => {
    router.get(route('laporan-return.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePrint = () => {
    const params = new URLSearchParams({
        start_date: startDate.value,
        end_date: endDate.value,
    });
    window.open(route('laporan-return.print') + '?' + params.toString(), '_blank');
};

const exportExcel = async () => {
    try {
        const response = await fetch(route('laporan-return.export-excel') + 
            `?start_date=${startDate.value}&end_date=${endDate.value}`, {
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
            ['Tanggal Return', 'No. Invoice', 'Kode Barang', 'Nama Barang', 'Jenis Barang', 'Jumlah', 'Alasan', 'User'].join(','),
            ...result.data.map(row => [
                row['Tanggal Return'],
                row['No. Invoice'],
                row['Kode Barang'],
                `"${row['Nama Barang']}"`,
                `"${row['Jenis Barang']}"`,
                row['Jumlah'],
                `"${row['Alasan']}"`,
                `"${row['User']}"`
            ].join(','))
        ].join('\n');
        
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `laporan_return_${startDate.value}_${endDate.value}.csv`;
        link.click();
    } catch (error) {
        alert('Gagal export data: ' + error.message);
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID');
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Laporan Return Barang</h1>
            </div>
        </template>

        <div class="flex items-center gap-2 text-white mb-6">
            <span class="hover:text-blue-100 cursor-pointer" @click="() => $inertia.visit(route('dashboard'))">Home</span>
            <span>></span>
            <span>Laporan Return</span>
        </div>

        <div class="bg-white rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Filter Data</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Awal</label>
                    <input
                        v-model="startDate"
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input
                        v-model="endDate"
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                <div class="flex items-end gap-2">
                    <button
                        @click="handleFilter"
                        class="flex-1 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium"
                    >
                        Tampilkan
                    </button>
                    <button
                        @click="handlePrint"
                        class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition"
                        title="Print"
                    >
                        <PrinterIcon :size="20" />
                    </button>
                    <button
                        @click="exportExcel"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition"
                        title="Export Excel"
                    >
                        <DownloadIcon :size="20" />
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg overflow-hidden shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Tanggal Return</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">No. Invoice</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Kode Barang</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Nama Barang</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700 border-b">Jumlah</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Alasan</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!returns.data || returns.data.length === 0">
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada data return</td>
                        </tr>
                        <tr v-for="(returnItem, index) in returns.data" :key="returnItem.id" class="border-b hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ (returns.current_page - 1) * returns.per_page + index + 1 }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ formatDate(returnItem.tanggal_return) }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ returnItem.invoice?.nomor_invoice || '-' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ returnItem.barang?.id_barang || '-' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ returnItem.barang?.nama_barang || '-' }}
                            </td>
                            <td class="px-6 py-3 text-center text-sm text-orange-600 font-semibold">
                                {{ returnItem.jumlah }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ returnItem.alasan || '-' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">
                                {{ returnItem.user?.name || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="returns.last_page > 1" class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ returns.from }} sampai {{ returns.to }} dari {{ returns.total }} data
                </div>
                <div class="flex gap-2">
                    <component
                        :is="link.url ? 'a' : 'span'"
                        v-for="link in returns.links"
                        :key="link.label"
                        :href="link.url"
                        @click.prevent="link.url && router.visit(link.url)"
                        v-html="link.label"
                        class="px-3 py-1 border border-gray-300 rounded-full text-sm cursor-pointer"
                        :class="{
                            'bg-blue-500 text-white': link.active,
                            'hover:bg-gray-100': link.url,
                            'opacity-50 cursor-not-allowed': !link.url
                        }"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>