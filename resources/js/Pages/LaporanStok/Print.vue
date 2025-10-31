<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const barangs = computed(() => page.props.barangs || []);
const filters = computed(() => page.props.filters || {});

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const printPage = () => {
    window.print();
};

// Auto print when page loads
if (typeof window !== 'undefined') {
    window.onload = () => {
        setTimeout(() => {
            window.print();
        }, 500);
    };
}
</script>

<template>
    <div class="print-container">
        <div class="print-header">
            <h1>LAPORAN STOK BARANG</h1>
            <p class="period">Periode: {{ formatDate(filters.tanggal_awal) }} - {{ formatDate(filters.tanggal_akhir) }}</p>
            <p class="print-date">Dicetak pada: {{ new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</p>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Stok Awal</th>
                    <th>Pembelian</th>
                    <th>Penjualan</th>
                    <th>Return</th>
                    <th>Stok Akhir</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="barangs.length === 0">
                    <td colspan="9" style="text-align: center;">Tidak ada data</td>
                </tr>
                <tr v-for="(barang, index) in barangs" :key="barang.id">
                    <td style="text-align: center;">{{ index + 1 }}</td>
                    <td>{{ barang.id_barang }}</td>
                    <td>{{ barang.nama_barang }}</td>
                    <td>{{ barang.jenis_barang?.nama_jenis }}</td>
                    <td style="text-align: right;">{{ formatNumber(barang.stok_awal) }}</td>
                    <td style="text-align: right;">{{ formatNumber(barang.jumlah_pembelian) }}</td>
                    <td style="text-align: right;">{{ formatNumber(barang.jumlah_penjualan) }}</td>
                    <td style="text-align: right;">{{ formatNumber(barang.jumlah_return) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ formatNumber(barang.stok_akhir) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="print-footer">
            <p>Total Data: {{ barangs.length }} barang</p>
        </div>

        <button class="no-print print-button" @click="printPage">
            Cetak Ulang
        </button>
    </div>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.print-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    font-family: Arial, sans-serif;
}

.print-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #333;
}

.print-header h1 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.print-header .period {
    font-size: 14px;
    margin-bottom: 5px;
    color: #666;
}

.print-header .print-date {
    font-size: 12px;
    color: #999;
}

.print-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.print-table th,
.print-table td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 12px;
}

.print-table th {
    background-color: #f5f5f5;
    font-weight: bold;
    text-align: left;
    color: #333;
}

.print-table tbody tr:nth-child(even) {
    background-color: #fafafa;
}

.print-table tbody tr:hover {
    background-color: #f0f0f0;
}

.print-footer {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 2px solid #ddd;
    text-align: right;
    font-size: 12px;
    color: #666;
}

.print-button {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 12px 24px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.print-button:hover {
    background-color: #2563eb;
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

@media print {
    .no-print {
        display: none !important;
    }

    .print-container {
        padding: 0;
    }

    .print-table th,
    .print-table td {
        padding: 6px;
        font-size: 11px;
    }

    .print-header h1 {
        font-size: 20px;
    }

    @page {
        margin: 1cm;
    }
}
</style>