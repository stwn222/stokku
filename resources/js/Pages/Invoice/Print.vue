<script setup>
import { Print, X } from 'lucide-vue-next';

defineProps({
    invoices: Array,
});

const handlePrint = () => {
    window.print();
};

const handleClose = () => {
    window.close();
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8 print:bg-white print:p-0">
        <!-- Print Toolbar -->
        <div class="mb-6 flex gap-4 print:hidden">
            <button 
                @click="handlePrint"
                class="flex items-center gap-2 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium"
            >
                <Print :size="20" />
                Cetak
            </button>
            <button 
                @click="handleClose"
                class="flex items-center gap-2 px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-medium"
            >
                <X :size="20" />
                Tutup
            </button>
        </div>

        <!-- Invoice Documents -->
        <div class="space-y-8">
            <div v-for="(invoice, idx) in invoices" :key="invoice.id" class="bg-white p-8 rounded-lg shadow-md print:shadow-none print:rounded-none print:p-0 print:break-after-page">
                
                <!-- Header -->
                <div class="mb-8 pb-4 border-b-2 border-gray-300">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">INVOICE</h1>
                            <p class="text-gray-600 mt-1">Stokku Management System</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-700"><strong>Nomor Invoice:</strong></p>
                            <p class="text-lg font-bold text-blue-600">{{ invoice.nomor_invoice }}</p>
                            <p class="text-sm text-gray-700 mt-2"><strong>Tanggal:</strong></p>
                            <p class="text-gray-900">{{ new Date(invoice.tanggal).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                            <p class="text-sm text-gray-700 mt-2"><strong>Tipe:</strong></p>
                            <p class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                :class="{
                                    'bg-blue-100 text-blue-700': invoice.tipe_invoice === 'MJU',
                                    'bg-orange-100 text-orange-700': invoice.tipe_invoice === 'BIP',
                                }">
                                {{ invoice.tipe_invoice }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bill To Section -->
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 uppercase mb-2">Tagihan Untuk:</h3>
                        <div class="text-gray-900">
                            <p class="font-semibold">{{ invoice.nama_client }}</p>
                            <p class="text-sm mt-1">{{ invoice.alamat_client }}</p>
                            <p class="text-sm mt-1">No. Telepon: {{ invoice.nomor_client }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 uppercase mb-2">Dari:</h3>
                        <div class="text-gray-900">
                            <p class="font-semibold">{{ invoice.user.name }}</p>
                            <p class="text-sm mt-1">PT. Stokku Indonesia</p>
                            <p class="text-sm">Jakarta, Indonesia</p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <h3 class="text-sm font-bold text-gray-700 uppercase mb-3">Detail Invoice:</h3>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200 border border-gray-300">
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">No</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Kode Barang</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Qty</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Harga Satuan</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(detail, index) in invoice.details" :key="detail.id" class="border border-gray-300 hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ index + 1 }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ detail.barang.id_barang }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ detail.barang.nama_barang }}</td>
                                <td class="px-4 py-2 text-center text-sm text-gray-900">{{ detail.qty }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-900">Rp {{ Number(detail.harga).toLocaleString('id-ID') }}</td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900">
                                    Rp {{ (detail.qty * Number(detail.harga)).toLocaleString('id-ID') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Section -->
                <div class="flex justify-end mb-8">
                    <div class="w-80 space-y-2 bg-gray-50 p-4 rounded-lg border border-gray-300">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal:</span>
                            <span class="font-semibold">Rp {{ invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0).toLocaleString('id-ID') }}</span>
                        </div>
                        <div v-if="Number(invoice.diskon) > 0" class="flex justify-between text-gray-700 pb-2 border-b border-gray-300">
                            <span>Diskon ({{ invoice.diskon }}%):</span>
                            <span class="font-semibold text-red-600">
                                - Rp {{ (invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) * invoice.diskon / 100).toLocaleString('id-ID') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Setelah Diskon:</span>
                            <span class="font-semibold">
                                Rp {{ (invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) - invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) * invoice.diskon / 100).toLocaleString('id-ID') }}
                            </span>
                        </div>
                        <div v-if="invoice.ppn" class="flex justify-between text-gray-700 pb-2 border-b border-gray-300">
                            <span>PPN (11%):</span>
                            <span class="font-semibold text-orange-600">
                                + Rp {{ ((invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) - invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) * invoice.diskon / 100) * 11 / 100).toLocaleString('id-ID') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t-2 border-gray-300">
                            <span>TOTAL:</span>
                            <span class="text-blue-600">
                                Rp {{ (invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) - invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) * invoice.diskon / 100 + (invoice.ppn ? (invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) - invoice.details.reduce((sum, d) => sum + (d.qty * Number(d.harga)), 0) * invoice.diskon / 100) * 11 / 100 : 0)).toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t-2 border-gray-300 pt-4 text-center text-gray-600 text-xs">
                    <p>Terima kasih atas pembelian Anda!</p>
                    <p>Untuk pertanyaan, silakan hubungi: contact@stokku.com</p>
                    <p class="mt-2">Dokumen ini dicetak pada {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body {
        margin: 0;
        padding: 0;
        background: white;
    }
}
</style>