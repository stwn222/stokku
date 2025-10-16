<script setup>
import { computed, onMounted } from 'vue';

const props = defineProps({
    invoice: Object,
});

const subtotal = computed(() => {
    return props.invoice.details.reduce((sum, detail) => {
        return sum + (detail.qty * detail.harga);
    }, 0);
});

const diskonAmount = computed(() => {
    return (subtotal.value * props.invoice.diskon) / 100;
});

const afterDiskon = computed(() => {
    return subtotal.value - diskonAmount.value;
});

const ppnAmount = computed(() => {
    return props.invoice.ppn ? (afterDiskon.value * 11) / 100 : 0;
});

const total = computed(() => {
    return afterDiskon.value + ppnAmount.value;
});

const terbilang = (angka) => {
    const bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    
    if (angka < 12) return bilangan[angka];
    if (angka < 20) return bilangan[angka - 10] + ' Belas';
    if (angka < 100) return bilangan[Math.floor(angka / 10)] + ' Puluh ' + bilangan[angka % 10];
    if (angka < 200) return 'Seratus ' + terbilang(angka - 100);
    if (angka < 1000) return bilangan[Math.floor(angka / 100)] + ' Ratus ' + terbilang(angka % 100);
    if (angka < 2000) return 'Seribu ' + terbilang(angka - 1000);
    if (angka < 1000000) return terbilang(Math.floor(angka / 1000)) + ' Ribu ' + terbilang(angka % 1000);
    if (angka < 1000000000) return terbilang(Math.floor(angka / 1000000)) + ' Juta ' + terbilang(angka % 1000000);
    return '';
};

const formatTerbilang = computed(() => {
    const rupiah = Math.floor(total.value);
    return '( ' + terbilang(rupiah).trim() + ' Rupiah )';
});

const logoPath = computed(() => {
    return props.invoice.tipe_invoice === 'BIP' 
        ? '/images/logo-bip.png'
        : '/images/logo-mju.png';
});

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <div class="print-container">
        <div class="invoice-page">
            <!-- Header Box -->
            <div class="header-box">
                <!-- Left: Company Info -->
                <div class="company-section">
                    <div class="company-name" v-if="invoice.tipe_invoice === 'BIP'">PT. Bhakti Inti Pratama</div>
                    <div class="company-name" v-else>PT. Media Jaya Utama</div>
                    <div class="company-address">
                        <div>Alamat :</div>
                        <div>Jl. Abdul Rahman Saleh No.8A,</div>
                        <div>Husein Sastranegara, Kec.</div>
                        <div>Cicendo, Kota Bandung, Jawa</div>
                        <div>Barat 40174</div>
                    </div>
                </div>

                <!-- Center: Invoice Title -->
                <div class="title-section">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">
                        <span>Nomor Invoice {{ invoice.nomor_invoice }}</span>
                        <span class="divider">-</span>
                        <span>Tanggal : {{ new Date(invoice.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
                    </div>
                </div>
            </div>

            <!-- Kepada & Payment -->
            <div class="info-grid">
                <div class="kepada-box">
                    <div class="info-title">KEPADA</div>
                    <div class="info-content">
                        <div class="client-name">Tuan: {{ invoice.nama_client }}</div>
                        <div class="client-address">{{ invoice.alamat_client }}</div>
                    </div>
                </div>

                <div class="payment-box">
                    <div class="info-title">Payment Method</div>
                    <div class="info-content">
                        <div class="payment-line">
                            <span class="pay-label">Account</span>
                            <span class="pay-value">: 008 0101 003732</span>
                        </div>
                        <div class="payment-line">
                            <span class="pay-label">A/c Name</span>
                            <span class="pay-value" v-if="invoice.tipe_invoice === 'BIP'">: PT. BHAKTI INTI PRATAMA</span>
                            <span class="pay-value" v-else>: PT. MEDIA JAYA UTAMA</span>
                        </div>
                        <div class="payment-line">
                            <span class="pay-label">Bank Details</span>
                            <span class="pay-value">: Bank BJB Syariah</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Watermark -->
            <div class="watermark">
                <img :src="logoPath" :alt="invoice.tipe_invoice" />
            </div>

            <!-- Invoice Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th class="th-qty">QTY</th>
                        <th class="th-desc">DESKRIPSI</th>
                        <th class="th-price">HARGA UNIT</th>
                        <th class="th-total">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="detail in invoice.details" :key="detail.id">
                        <td class="td-center">{{ detail.qty }}</td>
                        <td class="td-left">{{ detail.barang?.nama_barang || '-' }}</td>
                        <td class="td-right">{{ Number(detail.harga).toLocaleString('id-ID') }}</td>
                        <td class="td-right">{{ (detail.qty * detail.harga).toLocaleString('id-ID') }}</td>
                    </tr>
                    <tr v-for="i in Math.max(0, 10 - invoice.details.length)" :key="'empty-' + i" class="empty-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>

            <!-- Footer: Tanda Terima & Summary -->
            <div class="footer-grid">
                <div class="terima-box">
                    <div class="terima-header">TANDA TERIMA</div>
                    <div class="terima-body">
                        <div class="terbilang">{{ formatTerbilang }}</div>
                    </div>
                </div>

                <div class="summary-box">
                    <div class="sum-row">
                        <span class="sum-label">DISKON</span>
                        <span class="sum-value">{{ diskonAmount.toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="sum-row">
                        <span class="sum-label">PPN 11%</span>
                        <span class="sum-value">{{ ppnAmount.toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="sum-row sum-total">
                        <span class="sum-label">TOTAL HARGA</span>
                        <span class="sum-value">{{ total.toLocaleString('id-ID') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4;
    margin: 0;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.print-container {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    background: white;
    font-family: Arial, Helvetica, sans-serif;
}

.invoice-page {
    width: 100%;
    height: 297mm;
    padding: 15mm 15mm 10mm 15mm;
    position: relative;
}

/* HEADER BOX */
.header-box {
    border: 2px solid #000;
    padding: 12px 15px;
    margin-bottom: 12px;
}

.company-section {
    margin-bottom: 8px;
}

.company-name {
    font-size: 11.5px;
    font-weight: bold;
    margin-bottom: 3px;
}

.company-address {
    font-size: 9px;
    line-height: 1.3;
}

.title-section {
    text-align: center;
    margin-top: 5px;
}

.invoice-title {
    font-size: 40px;
    font-weight: bold;
    letter-spacing: 10px;
    line-height: 1;
    margin-bottom: 3px;
}

.invoice-meta {
    font-size: 9.5px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}

.divider {
    font-weight: bold;
}

/* INFO GRID */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.info-title {
    font-size: 10px;
    font-weight: bold;
    margin-bottom: 4px;
}

.info-content {
    font-size: 9px;
    line-height: 1.4;
}

.client-name {
    font-weight: bold;
    margin-bottom: 2px;
}

.payment-line {
    display: flex;
    gap: 4px;
}

.pay-label {
    min-width: 75px;
}

/* WATERMARK */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 400px;
    height: 400px;
    opacity: 0.08;
    z-index: 1;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.watermark img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* TABLE */
.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    position: relative;
    z-index: 2;
    background: white;
}

.invoice-table th,
.invoice-table td {
    border: 1px solid #000;
    padding: 6px 8px;
    background: white;
}

.invoice-table th {
    font-size: 10px;
    font-weight: bold;
    text-align: center;
}

.th-qty {
    width: 8%;
}

.th-desc {
    width: 50%;
}

.th-price {
    width: 21%;
}

.th-total {
    width: 21%;
}

.invoice-table td {
    font-size: 9.5px;
}

.td-center {
    text-align: center;
}

.td-left {
    text-align: left;
}

.td-right {
    text-align: right;
}

.empty-row td {
    height: 22px;
}

/* FOOTER GRID */
.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
    position: relative;
    z-index: 2;
}

.terima-box {
    border: 2px solid #000;
    background: white;
}

.terima-header {
    text-align: center;
    font-size: 10px;
    font-weight: bold;
    padding: 6px;
    border-bottom: 2px solid #000;
    background: white;
}

.terima-body {
    padding: 12px 15px;
    min-height: 75px;
    display: flex;
    align-items: center;
    background: white;
}

.terbilang {
    font-size: 9px;
    font-style: italic;
    line-height: 1.3;
}

.summary-box {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.sum-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    border: 1px solid #000;
    background: white;
    font-size: 10px;
}

.sum-label {
    font-weight: normal;
}

.sum-value {
    text-align: right;
    font-weight: normal;
}

.sum-total {
    border: 2px solid #000;
    font-weight: bold;
    font-size: 11px;
}

/* PRINT STYLES */
@media print {
    body {
        margin: 0;
        padding: 0;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }

    .print-container {
        margin: 0;
        width: 210mm;
    }

    .invoice-page {
        page-break-after: avoid;
    }

    .watermark {
        opacity: 0.06;
    }
}
</style>