<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { RotateCcwIcon } from 'lucide-vue-next';
import { PlusIcon, MinusIcon, Edit2Icon, Trash2Icon, XIcon, PrinterIcon, Check, ChevronsUpDown, AlertTriangle } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from '@headlessui/vue';

const page = usePage();
const selectedFilter = ref(page.props.filters?.tipe_invoice || '');
const invoices = ref(page.props.invoices);

const initialBarangList = page.props.barangList || [];
const paymentMethods = ref(page.props.paymentMethods || []);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showPrintMenu = ref({});
const currentStep = ref(1);
const currentEditStep = ref(1);
const editingInvoice = ref(null);
const loadingBarang = ref(false);

const createForm = useForm({
    tipe_invoice: 'MJU',
    nama_client: '',
    tanggal: new Date().toISOString().split('T')[0],
    alamat_client: '',
    diskon: 0,
    ppn: false,
    payment_method_id: null,
    details: [],
});

const editForm = useForm({
    nama_client: '',
    tanggal: '',
    alamat_client: '',
    diskon: 0,
    ppn: false,
    payment_method_id: null,
    details: [],
});

const selectedBarang = ref(null);
const selectedQty = ref(1);
const selectedBarangEdit = ref(null);
const selectedQtyEdit = ref(1);
const barangList = ref(initialBarangList);
const nextMJUNumber = ref(1);
const nextBIPNumber = ref(1);

const barangQueryCreate = ref('');
const barangQueryEdit = ref('');
const comboboxButtonRefCreate = ref(null);
const comboboxButtonRefEdit = ref(null);

const filteredBarangCreate = computed(() =>
    barangQueryCreate.value === ''
        ? barangList.value
        : barangList.value.filter((barang) =>
            barang.nama_barang
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(barangQueryCreate.value.toLowerCase().replace(/\s+/g, '')) ||
            barang.id_barang
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(barangQueryCreate.value.toLowerCase().replace(/\s+/g, ''))
        )
);

const filteredBarangEdit = computed(() =>
    barangQueryEdit.value === ''
        ? barangList.value
        : barangList.value.filter((barang) =>
            barang.nama_barang
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(barangQueryEdit.value.toLowerCase().replace(/\s+/g, '')) ||
            barang.id_barang
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(barangQueryEdit.value.toLowerCase().replace(/\s+/g, ''))
        )
);

const selectedBarangInfo = computed(() => {
    if (!selectedBarang.value) return null;
    return barangList.value.find(b => b.id === selectedBarang.value);
});

const selectedBarangEditInfo = computed(() => {
    if (!selectedBarangEdit.value) return null;
    return barangList.value.find(b => b.id === selectedBarangEdit.value);
});

const getAvailableStockCreate = (barangId) => {
    const barang = barangList.value.find(b => b.id === barangId);
    if (!barang) return 0;
    
    const usedInDetails = createForm.details
        .filter(d => d.barang_id === barangId)
        .reduce((sum, d) => sum + d.qty, 0);
    
    return barang.stok - usedInDetails;
};

const getAvailableStockEdit = (barangId) => {
    const barang = barangList.value.find(b => b.id === barangId);
    if (!barang) return 0;
    
    const usedInDetails = editForm.details
        .filter(d => d.barang_id === barangId)
        .reduce((sum, d) => sum + d.qty, 0);
    
    return barang.stok - usedInDetails;
};

watch(() => page.props.invoices, (newInvoices) => {
    invoices.value = newInvoices;
});

const currentInvoiceNumber = computed(() => {
    const number = createForm.tipe_invoice === 'MJU' ? nextMJUNumber.value : nextBIPNumber.value;
    const date = new Date(createForm.tanggal);
    const formattedDate = `${date.getDate().toString().padStart(2, '0')}.${(date.getMonth() + 1).toString().padStart(2, '0')}.${date.getFullYear()}`;
    return `${createForm.tipe_invoice}/${number}.${formattedDate}`;
});

const subtotalCreate = computed(() => {
    return createForm.details.reduce((sum, item) => {
        const qty = Number(item.qty) || 0;
        const harga = Number(item.harga) || 0;
        return sum + (qty * harga);
    }, 0);
});

const diskonAmountCreate = computed(() => {
    const diskon = Number(createForm.diskon) || 0;
    return (subtotalCreate.value * diskon) / 100;
});

const afterDiskonCreate = computed(() => {
    return subtotalCreate.value - diskonAmountCreate.value;
});

const ppnAmountCreate = computed(() => {
    return createForm.ppn ? (afterDiskonCreate.value * 11) / 100 : 0;
});

const totalCreate = computed(() => {
    return afterDiskonCreate.value + ppnAmountCreate.value;
});

const subtotalEdit = computed(() => {
    return editForm.details.reduce((sum, item) => {
        const qty = Number(item.qty) || 0;
        const harga = Number(item.harga) || 0;
        return sum + (qty * harga);
    }, 0);
});

const diskonAmountEdit = computed(() => {
    const diskon = Number(editForm.diskon) || 0;
    return (subtotalEdit.value * diskon) / 100;
});

const afterDiskonEdit = computed(() => {
    return subtotalEdit.value - diskonAmountEdit.value;
});

const ppnAmountEdit = computed(() => {
    return editForm.ppn ? (afterDiskonEdit.value * 11) / 100 : 0;
});

const totalEdit = computed(() => {
    return afterDiskonEdit.value + ppnAmountEdit.value;
});

const loadBarangList = async () => {
    if (initialBarangList && initialBarangList.length > 0) {
        barangList.value = initialBarangList;
        return;
    }
    
    if (loadingBarang.value) {
        return;
    }
    
    loadingBarang.value = true;
    
    try {
        const response = await fetch('/api/barang', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (Array.isArray(data)) {
            barangList.value = data;
        } else {
            throw new Error('Data yang diterima bukan array');
        }
        
    } catch (error) {
        if (initialBarangList && initialBarangList.length > 0) {
            barangList.value = initialBarangList;
        } else {
            alert('Gagal memuat data barang: ' + error.message);
            barangList.value = [];
        }
    } finally {
        loadingBarang.value = false;
    }
};

const loadNextNumbers = async () => {
    try {
        const [responseMJU, responseBIP] = await Promise.all([
            fetch('/api/invoice-next-number/MJU', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }),
            fetch('/api/invoice-next-number/BIP', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
        ]);
        
        if (responseMJU.ok) {
            const dataMJU = await responseMJU.json();
            nextMJUNumber.value = dataMJU.next_number || 1;
        }
        
        if (responseBIP.ok) {
            const dataBIP = await responseBIP.json();
            nextBIPNumber.value = dataBIP.next_number || 1;
        }
    } catch (err) {
        nextMJUNumber.value = 1;
        nextBIPNumber.value = 1;
    }
};

const openCreateModal = async () => {
    try {
        showCreateModal.value = true;
        
        if (barangList.value.length === 0) {
            await Promise.all([
                loadBarangList(),
                loadNextNumbers()
            ]);
        } else {
            await loadNextNumbers();
        }
        
        createForm.reset();
        createForm.tipe_invoice = 'MJU';
        createForm.tanggal = new Date().toISOString().split('T')[0];
        createForm.payment_method_id = paymentMethods.value.length > 0 ? paymentMethods.value[0].id : null;
        createForm.details = [];
        selectedBarang.value = null;
        selectedQty.value = 1;
        barangQueryCreate.value = '';
        currentStep.value = 1;
    } catch (error) {
        alert('Gagal membuka modal: ' + error.message);
        showCreateModal.value = false;
    }
};

const openEditModal = async (invoice) => {
    try {
        if (barangList.value.length === 0) {
            await loadBarangList();
        }
        
        editingInvoice.value = invoice;
        editForm.nama_client = invoice.nama_client;
        editForm.tanggal = invoice.tanggal;
        editForm.alamat_client = invoice.alamat_client;
        editForm.diskon = invoice.diskon;
        editForm.ppn = invoice.ppn;
        editForm.payment_method_id = invoice.payment_method_id;
        editForm.details = invoice.details.map(d => ({
            barang_id: d.barang_id,
            id_barang: d.barang?.id_barang || '',
            nama_barang: d.barang?.nama_barang || '',
            qty: d.qty,
            harga: Number(d.harga),
        }));
        
        selectedBarangEdit.value = null;
        selectedQtyEdit.value = 1;
        barangQueryEdit.value = '';
        currentEditStep.value = 1;
        showEditModal.value = true;
    } catch (error) {
        alert('Gagal membuka modal edit: ' + error.message);
    }
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    currentStep.value = 1;
    selectedBarang.value = null;
    selectedQty.value = 1;
    barangQueryCreate.value = '';
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    editingInvoice.value = null;
    currentEditStep.value = 1;
    selectedBarangEdit.value = null;
    selectedQtyEdit.value = 1;
    barangQueryEdit.value = '';
};

const addBarangCreate = () => {
    if (!selectedBarang.value || selectedQty.value < 1) {
        alert('Pilih barang dan masukkan jumlah yang valid');
        return;
    }

    const barang = barangList.value.find(b => b.id === selectedBarang.value);
    if (!barang) {
        alert('Barang tidak ditemukan');
        return;
    }

    const availableStock = getAvailableStockCreate(selectedBarang.value);
    if (selectedQty.value > availableStock) {
        alert(`Stok tidak mencukupi! Stok tersedia: ${availableStock}`);
        return;
    }

    const existingDetail = createForm.details.find(d => d.barang_id === selectedBarang.value);

    if (existingDetail) {
        existingDetail.qty += parseInt(selectedQty.value);
    } else {
        createForm.details.push({
            barang_id: barang.id,
            id_barang: barang.id_barang,
            nama_barang: barang.nama_barang,
            qty: parseInt(selectedQty.value),
            harga: Number(barang.harga_jual) || 0,
        });
    }

    selectedBarang.value = null;
    selectedQty.value = 1;
    barangQueryCreate.value = '';
};

const updateQtyCreate = (index, newQty) => {
    if (newQty < 1) return;
    
    const detail = createForm.details[index];
    const barang = barangList.value.find(b => b.id === detail.barang_id);
    if (!barang) return;
    
    const otherDetailsQty = createForm.details
        .filter((d, i) => i !== index && d.barang_id === detail.barang_id)
        .reduce((sum, d) => sum + d.qty, 0);
    
    const availableStock = barang.stok - otherDetailsQty;
    
    if (newQty > availableStock) {
        alert(`Stok tidak mencukupi! Stok tersedia: ${availableStock}`);
        return;
    }
    
    createForm.details[index].qty = newQty;
};

const removeBarangCreate = (index) => {
    createForm.details.splice(index, 1);
};

const nextStepCreate = () => {
    if (currentStep.value === 1) {
        if (!createForm.nama_client || !createForm.alamat_client || !createForm.payment_method_id) {
            alert('Harap lengkapi data client dan metode pembayaran terlebih dahulu');
            return;
        }
    }

    if (currentStep.value === 2) {
        if (createForm.details.length === 0) {
            alert('Tambahkan minimal satu barang');
            return;
        }
    }

    if (currentStep.value < 3) {
        currentStep.value++;
    }
};

const prevStepCreate = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submitCreate = () => {
    if (createForm.details.length === 0) {
        alert('Tambahkan minimal satu barang');
        return;
    }

    if (!createForm.payment_method_id) {
        alert('Pilih metode pembayaran');
        return;
    }

    createForm.post(route('invoice.store'), {
        onSuccess: () => {
            closeCreateModal();
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join('\n');
            alert(errorMessage);
        },
        preserveState: false,
        preserveScroll: false,
    });
};

const addBarangEdit = () => {
    if (!selectedBarangEdit.value || selectedQtyEdit.value < 1) {
        alert('Pilih barang dan masukkan jumlah yang valid');
        return;
    }

    const barang = barangList.value.find(b => b.id === selectedBarangEdit.value);
    if (!barang) {
        alert('Barang tidak ditemukan');
        return;
    }

    const availableStock = getAvailableStockEdit(selectedBarangEdit.value);
    if (selectedQtyEdit.value > availableStock) {
        alert(`Stok tidak mencukupi! Stok tersedia: ${availableStock}`);
        return;
    }

    const existingDetail = editForm.details.find(d => d.barang_id === selectedBarangEdit.value);

    if (existingDetail) {
        existingDetail.qty += parseInt(selectedQtyEdit.value);
    } else {
        editForm.details.push({
            barang_id: barang.id,
            id_barang: barang.id_barang,
            nama_barang: barang.nama_barang,
            qty: parseInt(selectedQtyEdit.value),
            harga: Number(barang.harga_jual) || 0,
        });
    }

    selectedBarangEdit.value = null;
    selectedQtyEdit.value = 1;
    barangQueryEdit.value = '';
};

const removeBarangEdit = (index) => {
    editForm.details.splice(index, 1);
};

const updateQtyEdit = (index, newQty) => {
    if (newQty < 1) return;
    
    const detail = editForm.details[index];
    const barang = barangList.value.find(b => b.id === detail.barang_id);
    if (!barang) return;
    
    const otherDetailsQty = editForm.details
        .filter((d, i) => i !== index && d.barang_id === detail.barang_id)
        .reduce((sum, d) => sum + d.qty, 0);
    
    const availableStock = barang.stok - otherDetailsQty;
    
    if (newQty > availableStock) {
        alert(`Stok tidak mencukupi! Stok tersedia: ${availableStock}`);
        return;
    }
    
    editForm.details[index].qty = newQty;
};

const nextStepEdit = () => {
    if (currentEditStep.value === 1) {
        if (!editForm.nama_client || !editForm.alamat_client || !editForm.payment_method_id) {
            alert('Harap lengkapi data client dan metode pembayaran terlebih dahulu');
            return;
        }
    }

    if (currentEditStep.value === 2) {
        if (editForm.details.length === 0) {
            alert('Tambahkan minimal satu barang');
            return;
        }
    }

    if (currentEditStep.value < 3) {
        currentEditStep.value++;
    }
};

const prevStepEdit = () => {
    if (currentEditStep.value > 1) {
        currentEditStep.value--;
    }
};

const submitEdit = () => {
    if (editForm.details.length === 0) {
        alert('Tambahkan minimal satu barang');
        return;
    }

    if (!editForm.payment_method_id) {
        alert('Pilih metode pembayaran');
        return;
    }

    editForm.put(route('invoice.update', editingInvoice.value.id), {
        onSuccess: () => {
            closeEditModal();
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join('\n');
            alert(errorMessage);
        },
        preserveState: false,
        preserveScroll: false,
    });
};

const handleFilter = () => {
    const params = selectedFilter.value ? { tipe_invoice: selectedFilter.value } : {};
    router.get(route('invoice.index'), params, { 
        preserveState: false,
        preserveScroll: true,
    });
};

const handlePrint = () => {
    const params = selectedFilter.value ? `?tipe_invoice=${selectedFilter.value}` : '';
    window.open(route('invoice.print') + params, '_blank');
};

const togglePrintMenu = (invoiceId) => {
    showPrintMenu.value[invoiceId] = !showPrintMenu.value[invoiceId];
};

const printSingleInvoice = (invoiceId, size = 'a4') => {
    if (size === 'a5') {
        window.open(route('invoice.print-single-a5', invoiceId), '_blank');
    } else {
        window.open(route('invoice.print-single', invoiceId), '_blank');
    }
    showPrintMenu.value[invoiceId] = false;
};

const deleteInvoice = (invoiceId, invoiceNumber) => {
    if (confirm(`Apakah Anda yakin ingin menghapus invoice ${invoiceNumber}?`)) {
        router.delete(route('invoice.destroy', invoiceId), {
            preserveState: false,
            preserveScroll: false,
        });
    }
};

const handleComboboxFocusCreate = () => {
    if (comboboxButtonRefCreate.value) {
        comboboxButtonRefCreate.value.click();
    }
};

const handleComboboxFocusEdit = () => {
    if (comboboxButtonRefEdit.value) {
        comboboxButtonRefEdit.value.click();
    }
};

const showReturnModal = ref(false);
const returningInvoice = ref(null);

const returnForm = useForm({
    invoice_id: null,
    barang_id: null,
    jumlah: 1,
    alasan: '',
});

const getMaxReturnForBarang = (barangId) => {
    if (!returningInvoice.value) return 0;
    
    const detail = returningInvoice.value.details.find(d => d.barang_id === barangId);
    if (!detail) return 0;
    
    const totalReturned = returningInvoice.value.returns?.filter(r => r.barang_id === barangId)
        .reduce((sum, r) => sum + r.jumlah, 0) || 0;
    
    return detail.qty - totalReturned;
};

const maxReturnQty = computed(() => {
    if (!returnForm.barang_id || !returningInvoice.value) return 0;
    return getMaxReturnForBarang(returnForm.barang_id);
});

const openReturnModal = (invoice) => {
    returningInvoice.value = invoice;
    returnForm.invoice_id = invoice.id;
    returnForm.barang_id = null;
    returnForm.jumlah = 1;
    returnForm.alasan = '';
    showReturnModal.value = true;
};

const closeReturnModal = () => {
    showReturnModal.value = false;
    returnForm.reset();
    returningInvoice.value = null;
};

const submitReturn = () => {
    if (!returnForm.barang_id) {
        alert('Pilih barang yang akan di-return');
        return;
    }

    if (returnForm.jumlah < 1 || returnForm.jumlah > maxReturnQty.value) {
        alert(`Jumlah return tidak valid. Maksimal: ${maxReturnQty.value}`);
        return;
    }

    returnForm.post(route('return-barang.store'), {
        onSuccess: () => {
            closeReturnModal();
            router.reload({ only: ['invoices'] });
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join('\n');
            alert(errorMessage);
        },
        preserveState: false,
        preserveScroll: false,
    });
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const dayName = days[date.getDay()];
    const day = String(date.getDate()).padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    
    return `${dayName}, ${day} ${month} ${year}`;
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4V5h12v10z" />
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-white">Invoice</h1>
            </div>
        </template>

        <div class="flex items-center gap-2 text-white mb-4 sm:mb-6 text-sm sm:text-base">
            <span class="hover:text-blue-100 cursor-pointer" @click="() => $inertia.visit(route('dashboard'))">Home</span>
            <span>></span>
            <span>Invoice</span>
        </div>

        <div class="bg-white rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
            <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Filter Data Invoice</h2>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 sm:gap-4">
                <div class="flex-1">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tipe Invoice</label>
                    <select
                        v-model="selectedFilter"
                        class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">-- Semua Tipe --</option>
                        <option value="MJU">MJU</option>
                        <option value="BIP">BIP</option>
                    </select>
                </div>
                
                <div class="w-full sm:w-auto">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tampilkan per halaman</label>
                    <select
                        v-model.number="perPage"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
                
                <button
                    @click="handleFilter"
                    class="w-full sm:w-auto px-6 sm:px-8 py-2 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                >
                    Tampilkan
                </button>
            </div>
        </div>

        <div class="flex justify-end mb-3 sm:mb-4">
            <button
                @click="openCreateModal"
                class="flex items-center gap-2 px-4 sm:px-6 py-2 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
            >
                <PlusIcon :size="18" class="sm:w-5 sm:h-5" />
                Entri Data
            </button>
        </div>

        <div class="bg-white rounded-lg overflow-hidden shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b">No</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b">Nama Customer</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b">Nomor Invoice</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b">Tanggal</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b">Tipe</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b">Metode Pembayaran</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-700 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="invoices.data && invoices.data.length === 0"><td colspan="7" class="px-3 sm:px-6 py-4 text-center text-gray-500 text-sm">Tidak ada data invoice</td>
                    </tr>
                    <tr v-for="(invoice, index) in invoices.data" :key="invoice.id" class="border-b hover:bg-gray-50">
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900">
                            {{ (invoices.current_page - 1) * invoices.per_page + index + 1 }}
                        </td>
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900">{{ invoice.nama_client }}</td>
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900">{{ invoice.nomor_invoice }}</td>
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900">
                            {{ formatDate(invoice.tanggal) }}
                        </td>
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm">
                            <span
                                :class="{
                                    'px-2 sm:px-3 py-1 rounded-full text-xs font-semibold': true,
                                    'bg-blue-100 text-blue-700': invoice.tipe_invoice === 'MJU',
                                    'bg-orange-100 text-orange-700': invoice.tipe_invoice === 'BIP',
                                }"
                            >
                                {{ invoice.tipe_invoice }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900">
                            {{ invoice.payment_method?.nama_metode || '-' }}
                        </td>
                        <td class="px-3 sm:px-6 py-2 sm:py-3 text-center">
                            <div class="flex justify-center gap-1 sm:gap-2">
                                <div class="relative">
                                    <button 
                                        @click="openReturnModal(invoice)" 
                                        class="p-1.5 sm:p-2 text-purple-500 hover:bg-purple-50 rounded-lg transition" 
                                        title="Return Barang"
                                    >
                                        <RotateCcwIcon :size="16" class="sm:w-[18px] sm:h-[18px]" />
                                    </button>
                                    <button @click="togglePrintMenu(invoice.id)" class="p-1.5 sm:p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition">
                                        <PrinterIcon :size="16" class="sm:w-[18px] sm:h-[18px]" />
                                    </button>
                                    <div v-if="showPrintMenu[invoice.id]" class="absolute right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 min-w-[100px] sm:min-w-[120px]">
                                        <button @click="printSingleInvoice(invoice.id, 'a4')" class="block w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm hover:bg-gray-100">Print A4</button>
                                        <button @click="printSingleInvoice(invoice.id, 'a5')" class="block w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm hover:bg-gray-100">Print A5</button>
                                    </div>
                                </div>
                                <button @click="openEditModal(invoice)" class="p-1.5 sm:p-2 text-green-500 hover:bg-green-50 rounded-lg transition">
                                    <Edit2Icon :size="16" class="sm:w-[18px] sm:h-[18px]" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="invoices.last_page > 1" class="px-3 sm:px-6 py-3 sm:py-4 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-3 border-t">
            <div class="text-xs sm:text-sm text-gray-600">
                Menampilkan {{ invoices.from }} sampai {{ invoices.to }} dari {{ invoices.total }} data
            </div>
            <div class="flex flex-wrap gap-1 sm:gap-2 justify-center">
                <component
                    :is="link.url ? 'a' : 'span'"
                    v-for="link in invoices.links"
                    :key="link.label"
                    :href="link.url"
                    @click.prevent="link.url && router.visit(link.url)"
                    v-html="link.label"
                    class="px-2 sm:px-3 py-1 border border-gray-300 rounded-full text-xs sm:text-sm cursor-pointer"
                    :class="{ 'bg-blue-500 text-white': link.active, 'hover:bg-gray-100': link.url, 'opacity-50 cursor-not-allowed': !link.url }"
                />
            </div>
        </div>
    </div>

    <!-- CREATE MODAL -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto px-2 sm:px-4" @click.self="closeCreateModal">
        <div class="flex items-center justify-center min-h-screen pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCreateModal"></div>

            <div class="inline-block w-full max-w-4xl my-4 sm:my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Entri Invoice Baru - Step {{ currentStep }} of 3</h3>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                        <XIcon :size="20" class="sm:w-6 sm:h-6" />
                    </button>
                </div>

                <div class="px-4 sm:px-6 py-3 sm:py-4 max-h-[60vh] sm:max-h-[70vh] overflow-y-auto">
                    <div v-show="currentStep === 1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Nama Customer:</label>
                                <input v-model="createForm.nama_client" type="text" placeholder="Masukan Nama Customer..." class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Tanggal:</label>
                                <input v-model="createForm.tanggal" type="date" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Nomor Invoice:</label>
                                <input :value="currentInvoiceNumber" type="text" readonly class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-100" />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Metode Pembayaran:</label>
                                <select v-model="createForm.payment_method_id" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option :value="null">-- Pilih Metode --</option>
                                    <option v-for="method in paymentMethods" :key="method.id" :value="method.id">{{ method.nama_metode }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 sm:mb-6">
                            <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Alamat:</label>
                            <input v-model="createForm.alamat_client" type="text" placeholder="Masukan Alamat Customer..." class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Diskon (%):</label>
                                <input v-model.number="createForm.diskon" type="number" placeholder="(Opsional)" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">PPN:</label>
                                <select v-model="createForm.ppn" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option :value="false">Tidak</option>
                                    <option :value="true">Ya (11%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Tipe Invoice</label>
                                <select v-model="createForm.tipe_invoice" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="MJU">MJU</option>
                                    <option value="BIP">BIP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div v-show="currentStep === 2">
                        <div v-if="loadingBarang" class="text-center py-4">
                            <p class="text-gray-500 text-sm sm:text-base">Loading data barang...</p>
                        </div>

                        <div v-else>
                            <div class="mb-4 max-h-48 sm:max-h-60 overflow-y-auto">
                                <table class="w-full border-collapse">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-2 sm:px-3 py-2 text-left text-xs font-semibold text-gray-700 border-b">Nama Barang</th>
                                            <th class="px-2 sm:px-3 py-2 text-center text-xs font-semibold text-gray-700 border-b">Qty</th>
                                            <th class="px-2 sm:px-3 py-2 text-right text-xs font-semibold text-gray-700 border-b">Harga</th>
                                            <th class="px-2 sm:px-3 py-2 text-center text-xs font-semibold text-gray-700 border-b">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="createForm.details.length === 0">
                                            <td colspan="4" class="px-2 sm:px-3 py-3 text-center text-gray-500 text-xs sm:text-sm">Belum ada barang</td>
                                        </tr>
                                        <tr v-for="(detail, index) in createForm.details" :key="index" class="border-b hover:bg-gray-50">
                                            <td class="px-2 sm:px-3 py-2 text-xs sm:text-sm text-gray-900">{{ detail.nama_barang }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button" @click="updateQtyCreate(index, detail.qty - 1)" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                        <MinusIcon :size="12" class="sm:w-[14px] sm:h-[14px]" />
                                                    </button>
                                                    <input :value="detail.qty" type="number" min="1" @change="(e) => updateQtyCreate(index, parseInt(e.target.value))" class="w-10 sm:w-12 px-1 py-1 border border-gray-300 rounded text-center text-xs sm:text-sm" />
                                                    <button type="button" @click="updateQtyCreate(index, detail.qty + 1)" class="p-1 text-blue-500 hover:bg-blue-50 rounded">
                                                        <PlusIcon :size="12" class="sm:w-[14px] sm:h-[14px]" />
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-2 sm:px-3 py-2 text-right text-xs sm:text-sm text-gray-900">Rp {{ Number(detail.harga).toLocaleString('id-ID') }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center">
                                                <button type="button" @click="removeBarangCreate(index)" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                    <Trash2Icon :size="14" class="sm:w-4 sm:h-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 pt-4 border-t">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Pilih Barang</label>
                                    <select v-model="selectedBarang" class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option :value="null">-- Pilih Barang --</option>
                                        <option v-for="barang in barangList" :key="barang.id" :value="barang.id">
                                            {{ barang.nama_barang }} ({{ barang.id_barang }}) - Stok: {{ getAvailableStockCreate(barang.id) }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Qty</label>
                                    <input 
                                        v-model.number="selectedQty" 
                                        type="number" 
                                        min="1" 
                                        :max="selectedBarangInfo ? getAvailableStockCreate(selectedBarangInfo.id) : 999999"
                                        placeholder="1" 
                                        class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                    />
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-3 mt-4">
                                <div v-if="selectedBarangInfo" class="flex-1 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="text-xs sm:text-sm">
                                        <div class="font-semibold text-gray-900">{{ selectedBarangInfo.nama_barang }}</div>
                                        <div class="text-gray-600 mt-1">ID: {{ selectedBarangInfo.id_barang }}</div>
                                        <div class="text-gray-600">Harga: Rp {{ Number(selectedBarangInfo.harga_jual).toLocaleString('id-ID') }}</div>
                                        <div class="flex items-center gap-1 mt-1" :class="getAvailableStockCreate(selectedBarangInfo.id) < 10 ? 'text-red-600' : 'text-green-600'">
                                            <AlertTriangle v-if="getAvailableStockCreate(selectedBarangInfo.id) < 10" :size="12" class="sm:w-[14px] sm:h-[14px]" />
                                            Stok Tersedia: {{ getAvailableStockCreate(selectedBarangInfo.id) }}
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="addBarangCreate" 
                                    :disabled="barangList.length === 0 || !selectedBarang"
                                    class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium flex items-center justify-center gap-2 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <PlusIcon :size="14" class="sm:w-4 sm:h-4" />
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-show="currentStep === 3">
                        <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-6 pb-4 sm:pb-6 border-b">
                            <div class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">Subtotal:</span>
                                <span class="font-semibold">Rp {{ subtotalCreate.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">Diskon ({{ createForm.diskon }}%):</span>
                                <span class="font-semibold text-red-600">- Rp {{ diskonAmountCreate.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">Setelah Diskon:</span>
                                <span class="font-semibold">Rp {{ afterDiskonCreate.toLocaleString('id-ID') }}</span>
                            </div>
                            <div v-if="createForm.ppn" class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">PPN (11%):</span>
                                <span class="font-semibold text-orange-600">+ Rp {{ ppnAmountCreate.toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between text-lg sm:text-xl font-bold text-gray-900">
                            <span>TOTAL:</span>
                            <span class="text-blue-600">Rp {{ totalCreate.toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 border-t bg-gray-50">
                    <button v-if="currentStep > 1" type="button" @click="prevStepCreate" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium">Kembali</button>
                    <button v-if="currentStep < 3" type="button" @click="nextStepCreate" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium">Selanjutnya</button>
                    <button v-if="currentStep === 3" type="button" @click="submitCreate" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium">Simpan</button>
                    <button type="button" @click="closeCreateModal" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- RETURN MODAL -->
    <div v-if="showReturnModal" class="fixed inset-0 z-50 overflow-y-auto px-2 sm:px-4" @click.self="closeReturnModal">
        <div class="flex items-center justify-center min-h-screen pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeReturnModal"></div>

            <div class="inline-block w-full max-w-2xl my-4 sm:my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Return Barang - Invoice {{ returningInvoice?.nomor_invoice }}</h3>
                    <button @click="closeReturnModal" class="text-gray-400 hover:text-gray-600">
                        <XIcon :size="20" class="sm:w-6 sm:h-6" />
                    </button>
                </div>

                <div class="px-4 sm:px-6 py-3 sm:py-4">
                    <div class="mb-4">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Pilih Barang yang akan di-return</label>
                        <select 
                            v-model="returnForm.barang_id" 
                            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="returnForm.jumlah = 1"
                        >
                            <option :value="null">-- Pilih Barang --</option>
                            <option 
                                v-for="detail in returningInvoice?.details" 
                                :key="detail.barang_id" 
                                :value="detail.barang_id"
                                :disabled="getMaxReturnForBarang(detail.barang_id) <= 0"
                            >
                                {{ detail.barang?.nama_barang }} - Tersedia untuk return: {{ getMaxReturnForBarang(detail.barang_id) }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4" v-if="returnForm.barang_id">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Jumlah Return (Max: {{ maxReturnQty }})
                        </label>
                        <input 
                            v-model.number="returnForm.jumlah" 
                            type="number" 
                            min="1" 
                            :max="maxReturnQty"
                            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Alasan Return (Opsional)</label>
                        <textarea 
                            v-model="returnForm.alasan" 
                            rows="3"
                            placeholder="Masukkan alasan return..."
                            class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 border-t bg-gray-50">
                    <button 
                        type="button" 
                        @click="submitReturn"
                        :disabled="!returnForm.barang_id || returnForm.jumlah < 1 || returnForm.jumlah > maxReturnQty"
                        class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Proses Return
                    </button>
                    <button 
                        type="button" 
                        @click="closeReturnModal" 
                        class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto px-2 sm:px-4" @click.self="closeEditModal">
        <div class="flex items-center justify-center min-h-screen pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeEditModal"></div>

            <div class="inline-block w-full max-w-4xl my-4 sm:my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Edit Invoice - Step {{ currentEditStep }} of 3</h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <XIcon :size="20" class="sm:w-6 sm:h-6" />
</button>
</div><div class="px-4 sm:px-6 py-3 sm:py-4 max-h-[60vh] sm:max-h-[70vh] overflow-y-auto">
                    <div v-show="currentEditStep === 1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Nama Customer:</label>
                                <input v-model="editForm.nama_client" type="text" placeholder="Masukan Nama Client..." class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Tanggal:</label>
                                <input v-model="editForm.tanggal" type="date" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Nomor Invoice:</label>
                                <input :value="editingInvoice?.nomor_invoice" type="text" readonly class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-100" />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Metode Pembayaran:</label>
                                <select v-model="editForm.payment_method_id" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option :value="null">-- Pilih Metode --</option>
                                    <option v-for="method in paymentMethods" :key="method.id" :value="method.id">{{ method.nama_metode }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 sm:mb-6">
                            <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Alamat Client:</label>
                            <input v-model="editForm.alamat_client" type="text" placeholder="Masukan Alamat Client..." class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">Diskon (%):</label>
                                <input v-model.number="editForm.diskon" type="number" placeholder="(Opsional)" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-900 mb-2">PPN:</label>
                                <select v-model="editForm.ppn" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option :value="false">Tidak</option>
                                    <option :value="true">Ya (11%)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div v-show="currentEditStep === 2">
                        <div v-if="loadingBarang" class="text-center py-4">
                            <p class="text-gray-500 text-sm sm:text-base">Loading data barang...</p>
                        </div>

                        <div v-else>
                            <div class="mb-4 max-h-48 sm:max-h-60 overflow-y-auto">
                                <table class="w-full border-collapse">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-2 sm:px-3 py-2 text-left text-xs font-semibold text-gray-700 border-b">Nama Barang</th>
                                            <th class="px-2 sm:px-3 py-2 text-center text-xs font-semibold text-gray-700 border-b">Qty</th>
                                            <th class="px-2 sm:px-3 py-2 text-right text-xs font-semibold text-gray-700 border-b">Harga</th>
                                            <th class="px-2 sm:px-3 py-2 text-center text-xs font-semibold text-gray-700 border-b">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="editForm.details.length === 0">
                                            <td colspan="4" class="px-2 sm:px-3 py-3 text-center text-gray-500 text-xs sm:text-sm">Belum ada barang</td>
                                        </tr>
                                        <tr v-for="(detail, index) in editForm.details" :key="index" class="border-b hover:bg-gray-50">
                                            <td class="px-2 sm:px-3 py-2 text-xs sm:text-sm text-gray-900">{{ detail.nama_barang }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button" @click="updateQtyEdit(index, detail.qty - 1)" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                        <MinusIcon :size="12" class="sm:w-[14px] sm:h-[14px]" />
                                                    </button>
                                                    <input :value="detail.qty" type="number" min="1" @change="(e) => updateQtyEdit(index, parseInt(e.target.value))" class="w-10 sm:w-12 px-1 py-1 border border-gray-300 rounded text-center text-xs sm:text-sm" />
                                                    <button type="button" @click="updateQtyEdit(index, detail.qty + 1)" class="p-1 text-blue-500 hover:bg-blue-50 rounded">
                                                        <PlusIcon :size="12" class="sm:w-[14px] sm:h-[14px]" />
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-2 sm:px-3 py-2 text-right text-xs sm:text-sm text-gray-900">Rp {{ Number(detail.harga).toLocaleString('id-ID') }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center">
                                                <button type="button" @click="removeBarangEdit(index)" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                    <Trash2Icon :size="14" class="sm:w-4 sm:h-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 pt-4 border-t">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Pilih Barang</label>
                                    <select v-model="selectedBarangEdit" class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option :value="null">-- Pilih Barang --</option>
                                        <option v-for="barang in barangList" :key="barang.id" :value="barang.id">
                                            {{ barang.nama_barang }} ({{ barang.id_barang }}) - Stok: {{ getAvailableStockEdit(barang.id) }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Qty</label>
                                    <input 
                                        v-model.number="selectedQtyEdit" 
                                        type="number" 
                                        min="1" 
                                        :max="selectedBarangEditInfo ? getAvailableStockEdit(selectedBarangEditInfo.id) : 999999"
                                        placeholder="1" 
                                        class="w-full px-2 sm:px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                    />
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-3 mt-4">
                                <div v-if="selectedBarangEditInfo" class="flex-1 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="text-xs sm:text-sm">
                                        <div class="font-semibold text-gray-900">{{ selectedBarangEditInfo.nama_barang }}</div>
                                        <div class="text-gray-600 mt-1">ID: {{ selectedBarangEditInfo.id_barang }}</div>
                                        <div class="text-gray-600">Harga: Rp {{ Number(selectedBarangEditInfo.harga_jual).toLocaleString('id-ID') }}</div>
                                        <div class="flex items-center gap-1 mt-1" :class="getAvailableStockEdit(selectedBarangEditInfo.id) < 10 ? 'text-red-600' : 'text-green-600'">
                                            <AlertTriangle v-if="getAvailableStockEdit(selectedBarangEditInfo.id) < 10" :size="12" class="sm:w-[14px] sm:h-[14px]" />
                                            Stok Tersedia: {{ getAvailableStockEdit(selectedBarangEditInfo.id) }}
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="addBarangEdit" 
                                    :disabled="barangList.length === 0 || !selectedBarangEdit"
                                    class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium flex items-center justify-center gap-2 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <PlusIcon :size="14" class="sm:w-4 sm:h-4" />
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-show="currentEditStep === 3">
                        <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-6 pb-4 sm:pb-6 border-b">
                            <div class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">Subtotal:</span>
                                <span class="font-semibold">Rp {{ subtotalEdit.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">Diskon ({{ editForm.diskon }}%):</span>
                                <span class="font-semibold text-red-600">- Rp {{ diskonAmountEdit.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">Setelah Diskon:</span>
                                <span class="font-semibold">Rp {{ afterDiskonEdit.toLocaleString('id-ID') }}</span>
                            </div>
                            <div v-if="editForm.ppn" class="flex justify-between text-gray-700 text-sm sm:text-base">
                                <span class="font-medium">PPN (11%):</span>
                                <span class="font-semibold text-orange-600">+ Rp {{ ppnAmountEdit.toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between text-lg sm:text-xl font-bold text-gray-900">
                            <span>TOTAL:</span>
                            <span class="text-blue-600">Rp {{ totalEdit.toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 border-t bg-gray-50">
                    <button v-if="currentEditStep > 1" type="button" @click="prevStepEdit" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium">Kembali</button>
                    <button v-if="currentEditStep < 3" type="button" @click="nextStepEdit" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium">Selanjutnya</button>
                    <button v-if="currentEditStep === 3" type="button" @click="submitEdit" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium">Simpan</button>
                    <button type="button" @click="closeEditModal" class="w-full sm:w-auto px-6 sm:px-8 py-2 sm:py-2.5 text-sm sm:text-base bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium">Batal</button>
                </div>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
</template>