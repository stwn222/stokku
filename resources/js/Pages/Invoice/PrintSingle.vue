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

const companyName = computed(() => {
    return props.invoice.tipe_invoice === 'BIP'
        ? 'PT. Bhakti Inti Pratama'
        : 'CV. Media Jaya Utama';
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
            <!-- Header dengan alamat dan INVOICE sejajar -->
            <div class="header-section">
                <div class="company-info">
                    <div class="company-name">{{ companyName }}</div>
                    <div class="company-address">
                        <div>Alamat :</div>
                        <div>Jl. Abdul Rahman Saleh No.8A,</div>
                        <div>Husein Sastranegara, Kec.</div>
                        <div>Cicendo, Kota Bandung, Jawa</div>
                        <div>Barat 40174</div>
                    </div>
                </div>
                <div class="invoice-title-section">
                    <div class="invoice-title">INVOICE</div>
                </div>
            </div>

            <!-- Garis pembatas pertama -->
            <div class="divider-line"></div>

            <!-- Nomor Invoice dan Tanggal di tengah -->
            <div class="invoice-meta-center">
                <span>Nomor Invoice {{ invoice.nomor_invoice }}</span>
                <span class="meta-divider">-</span>
                <span>Tanggal : {{ new Date(invoice.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
            </div>

            <!-- Info Grid: KEPADA & Payment Method -->
            <div class="info-grid">
                <div class="kepada-section">
                    <div class="section-title">KEPADA</div>
                    <div class="section-content">
                        <div class="client-name">Tuan: {{ invoice.nama_client }}</div>
                        <div class="client-address">{{ invoice.alamat_client }}</div>
                    </div>
                </div>

                <div class="payment-section">
                    <div class="section-title">Payment Method</div>
                    <div class="section-content">
                        <div class="payment-line">
                            <span class="pay-label">Account</span>
                            <span class="pay-value"><strong>: 008 0101 003732</strong></span>
                        </div>
                        <div class="payment-line">
                            <span class="pay-label">A/c Name</span>
                            <span class="pay-value">: CV. Media Jaya Utama</span>
                        </div>
                        <div class="payment-line">
                            <span class="pay-label">Bank Details</span>
                            <span class="pay-value">: Bank BJB Syariah</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Garis pembatas kedua -->
            <div class="divider-line"></div>

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
                <div class="terima-section">
                    <div class="terima-title">TANDA TERIMA</div>
                    <div class="terima-content">
                        <div class="signature-area">
                            <div class="signature-line">
                                <span>(...................................)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-section">
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
                    <div class="terbilang-text">{{ formatTerbilang }}</div>
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

/* HEADER SECTION - Alamat dan INVOICE sejajar */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.company-info {
    flex: 1;
}

.company-name {
    font-size: 11.5px;
    font-weight: bold;
    margin-bottom: 4px;
}

.company-address {
    font-size: 9px;
    line-height: 1.4;
}

.invoice-title-section {
    flex-shrink: 0;
    text-align: right;
}

.invoice-title {
    font-size: 50px;
    font-weight: bold;
    letter-spacing: 12px;
    line-height: 1;
}

/* GARIS PEMBATAS */
.divider-line {
    width: 100%;
    height: 2px;
    background-color: #000;
    margin: 8px 0;
}

/* NOMOR INVOICE DAN TANGGAL DI TENGAH */
.invoice-meta-center {
    text-align: start;
    font-size: 10px;
}

.meta-divider {
    margin: 0 8px;
    font-weight: bold;
}

/* INFO GRID */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin: 15px 0;
    position: relative;
    z-index: 2;
}

.section-title {
    font-size: 10px;
    font-weight: bold;
    margin-bottom: 6px;
}

.section-content {
    font-size: 9px;
    line-height: 1.5;
}

.client-name {
    font-weight: bold;
    margin-bottom: 3px;
}

.payment-line {
    display: flex;
    gap: 4px;
    margin-bottom: 2px;
}

.pay-label {
    min-width: 75px;
}

.pay-value strong {
    font-weight: bold;
}

/* WATERMARK */
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 450px;
    height: 450px;
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

.terima-section {
    background: white;
}

.terima-title {
    font-size: 10px;
    font-weight: bold;
    margin-bottom: 8px;
    margin-left: 4px;
}

.terima-content {
    background: white;
    min-height: 80px;
}

.terbilang-text {
    font-size: 9px;
    text-align: center;
    font-style: italic;
    line-height: 1.4;
    margin-bottom: 25px;
}

.signature-area {
    margin-top: 30px;
}

.signature-line {
    line-height: 80px;
    text-align: left;
    font-size: 9px;
}

.summary-section {
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
        opacity: 0.08;
    }
}
</style>