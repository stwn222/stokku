<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { PlusIcon, MinusIcon, Edit2Icon, Trash2Icon, XIcon } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const selectedFilter = ref(page.props.filters?.tipe_invoice || '');
const invoices = ref(page.props.invoices);

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const currentStep = ref(1);
const editingInvoice = ref(null);

// Form untuk Create
const createForm = useForm({
    tipe_invoice: 'MJU',
    nama_client: '',
    nomor_client: '',
    tanggal: new Date().toISOString().split('T')[0],
    alamat_client: '',
    diskon: 0,
    ppn: false,
    details: [],
});

// Form untuk Edit
const editForm = useForm({
    nama_client: '',
    nomor_client: '',
    tanggal: '',
    alamat_client: '',
    diskon: 0,
    ppn: false,
    details: [],
});

const selectedBarang = ref('');
const selectedQty = ref(1);
const barangList = ref([]);
const nextMJUNumber = ref(1);
const nextBIPNumber = ref(1);

// Computed untuk Create
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

// Computed untuk Edit
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

// Methods untuk Modal
const openCreateModal = async () => {
    try {
        const response = await fetch(route('invoice.create'));
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Ambil data dari halaman create
        const pageData = window.Inertia?.page?.props || page.props;
        
        // Load barang list
        await loadBarangList();
        
        createForm.reset();
        createForm.tipe_invoice = 'MJU';
        createForm.tanggal = new Date().toISOString().split('T')[0];
        createForm.details = [];
        currentStep.value = 1;
        showCreateModal.value = true;
    } catch (error) {
        console.error('Error:', error);
        window.location.href = route('invoice.create');
    }
};

const openEditModal = async (invoice) => {
    try {
        await loadBarangList();
        
        editingInvoice.value = invoice;
        editForm.nama_client = invoice.nama_client;
        editForm.nomor_client = invoice.nomor_client;
        editForm.tanggal = invoice.tanggal;
        editForm.alamat_client = invoice.alamat_client;
        editForm.diskon = invoice.diskon;
        editForm.ppn = invoice.ppn;
        editForm.details = invoice.details.map(d => ({
            barang_id: d.barang_id,
            id_barang: d.barang?.id_barang || '',
            nama_barang: d.barang?.nama_barang || '',
            qty: d.qty,
            harga: Number(d.harga),
        }));
        
        showEditModal.value = true;
    } catch (error) {
        console.error('Error:', error);
        window.location.href = route('invoice.edit', invoice.id);
    }
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    currentStep.value = 1;
    selectedBarang.value = '';
    selectedQty.value = 1;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    editingInvoice.value = null;
    selectedBarang.value = '';
    selectedQty.value = 1;
};

const loadBarangList = async () => {
    try {
        const response = await axios.get('/api/barang');
        barangList.value = response.data;
    } catch (error) {
        const response = await fetch(route('invoice.create'));
        const text = await response.text();
        barangList.value = [];
    }
};

// Methods untuk Create
const addBarangCreate = () => {
    if (!selectedBarang.value || selectedQty.value < 1) {
        alert('Pilih barang dan masukkan jumlah yang valid');
        return;
    }

    const barang = barangList.value.find(b => b.id == selectedBarang.value);
    const existingDetail = createForm.details.find(d => d.barang_id == selectedBarang.value);

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

    selectedBarang.value = '';
    selectedQty.value = 1;
};

const updateQtyCreate = (index, newQty) => {
    if (newQty > 0) {
        createForm.details[index].qty = newQty;
    }
};

const nextStepCreate = () => {
    if (currentStep.value === 1) {
        if (!createForm.nama_client || !createForm.nomor_client || !createForm.alamat_client) {
            alert('Harap lengkapi data client terlebih dahulu');
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

    createForm.post(route('invoice.store'), {
        onSuccess: () => {
            closeCreateModal();
            router.reload();
        },
    });
};

// Methods untuk Edit
const addBarangEdit = () => {
    if (!selectedBarang.value || selectedQty.value < 1) {
        alert('Pilih barang dan masukkan jumlah yang valid');
        return;
    }

    const barang = barangList.value.find(b => b.id == selectedBarang.value);
    const existingDetail = editForm.details.find(d => d.barang_id == selectedBarang.value);

    if (existingDetail) {
        existingDetail.qty += parseInt(selectedQty.value);
    } else {
        editForm.details.push({
            barang_id: barang.id,
            id_barang: barang.id_barang,
            nama_barang: barang.nama_barang,
            qty: parseInt(selectedQty.value),
            harga: Number(barang.harga_jual) || 0,
        });
    }

    selectedBarang.value = '';
    selectedQty.value = 1;
};

const removeBarangEdit = (index) => {
    editForm.details.splice(index, 1);
};

const updateQtyEdit = (index, newQty) => {
    if (newQty > 0) {
        editForm.details[index].qty = newQty;
    }
};

const submitEdit = () => {
    if (editForm.details.length === 0) {
        alert('Tambahkan minimal satu barang');
        return;
    }

    editForm.put(route('invoice.update', editingInvoice.value.id), {
        onSuccess: () => {
            closeEditModal();
            router.reload();
        },
    });
};

// Methods untuk Index
const handleFilter = () => {
    const params = selectedFilter.value ? { tipe_invoice: selectedFilter.value } : {};
    router.get(route('invoice.index', params), {}, { preserveState: true });
};

const handlePrint = () => {
    const params = selectedFilter.value ? { tipe_invoice: selectedFilter.value } : {};
    window.open(route('invoice.print', params), '_blank');
};

const deleteInvoice = (invoiceId, invoiceNumber) => {
    if (confirm(`Apakah Anda yakin ingin menghapus invoice ${invoiceNumber}?`)) {
        router.delete(route('invoice.destroy', invoiceId));
    }
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
                <h1 class="text-2xl font-bold text-white">Invoice</h1>
            </div>
        </template>

        <div class="flex items-center gap-2 text-white mb-6">
            <span class="hover:text-blue-100 cursor-pointer" @click="() => $inertia.visit(route('dashboard'))">Home</span>
            <span>></span>
            <span>Invoice</span>
        </div>

        <div class="bg-white rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Filter Data Invoice</h2>
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Invoice</label>
                    <select
                        v-model="selectedFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">-- Semua Tipe --</option>
                        <option value="MJU">MJU</option>
                        <option value="BIP">BIP</option>
                    </select>
                </div>
                <button
                    @click="handleFilter"
                    class="px-8 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                >
                    Tampilkan
                </button>
                <button
                    @click="handlePrint"
                    class="px-8 py-2 bg-green-500 text-white rounded-full hover:bg-green-600 transition font-medium"
                >
                    Cetak
                </button>
            </div>
        </div>

        <div class="flex justify-end mb-4">
            <button
                @click="openCreateModal"
                class="flex items-center gap-2 px-6 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
            >
                <PlusIcon :size="20" />
                Entri Data
            </button>
        </div>

        <div class="bg-white rounded-lg overflow-hidden shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Nama Client</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Nomor Invoice</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Tanggal</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 border-b">Tipe</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="invoices.data && invoices.data.length === 0">
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data invoice</td>
                        </tr>
                        <tr v-for="(invoice, index) in invoices.data" :key="invoice.id" class="border-b hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ (invoices.current_page - 1) * invoices.per_page + index + 1 }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ invoice.nama_client }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ invoice.nomor_invoice }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ new Date(invoice.tanggal).toLocaleDateString('id-ID') }}</td>
                            <td class="px-6 py-3 text-sm">
                                <span
                                    :class="{
                                        'px-3 py-1 rounded-full text-xs font-semibold': true,
                                        'bg-blue-100 text-blue-700': invoice.tipe_invoice === 'MJU',
                                        'bg-orange-100 text-orange-700': invoice.tipe_invoice === 'BIP',
                                    }"
                                >
                                    {{ invoice.tipe_invoice }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="openEditModal(invoice)" class="p-2 text-green-500 hover:bg-green-50 rounded-lg transition">
                                        <Edit2Icon :size="18" />
                                    </button>
                                    <button @click="deleteInvoice(invoice.id, invoice.nomor_invoice)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                        <Trash2Icon :size="18" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="invoices.last_page > 1" class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ invoices.from }} sampai {{ invoices.to }} dari {{ invoices.total }} data
                </div>
                <div class="flex gap-2">
                    <component
                        :is="link.url ? 'a' : 'span'"
                        v-for="link in invoices.links"
                        :key="link.label"
                        :href="link.url"
                        @click.prevent="link.url && router.visit(link.url)"
                        v-html="link.label"
                        class="px-3 py-1 border border-gray-300 rounded-full text-sm cursor-pointer"
                        :class="{ 'bg-blue-500 text-white': link.active, 'hover:bg-gray-100': link.url, 'opacity-50 cursor-not-allowed': !link.url }"
                    />
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeCreateModal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCreateModal"></div>

                <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Entri Invoice Baru</h3>
                        <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                            <XIcon :size="24" />
                        </button>
                    </div>

                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <!-- STEP 1: Data Client -->
                        <div v-show="currentStep === 1">
                            <div class="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Nama Client:</label>
                                    <input
                                        v-model="createForm.nama_client"
                                        type="text"
                                        placeholder="Masukan Nama Client..."
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Nomor Client:</label>
                                    <input
                                        v-model="createForm.nomor_client"
                                        type="text"
                                        placeholder="Masukan Nomor Client..."
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Tanggal:</label>
                                    <input
                                        v-model="createForm.tanggal"
                                        type="date"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Nomor Invoice:</label>
                                    <input
                                        :value="currentInvoiceNumber"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100"
                                    />
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Alamat Client:</label>
                                <input
                                    v-model="createForm.alamat_client"
                                    type="text"
                                    placeholder="Masukan Alamat Client..."
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div class="grid grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Diskon (%):</label>
                                    <input
                                        v-model.number="createForm.diskon"
                                        type="number"
                                        placeholder="(Opsional)"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">PPN:</label>
                                    <select
                                        v-model="createForm.ppn"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option :value="false">Tidak</option>
                                        <option :value="true">Ya (11%)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Tipe Invoice</label>
                                    <select
                                        v-model="createForm.tipe_invoice"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="MJU">MJU</option>
                                        <option value="BIP">BIP</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: Pilih Barang -->
                        <div v-show="currentStep === 2">
                            <div class="space-y-4 mb-6">
                                <div v-for="(detail, index) in createForm.details" :key="index" class="grid grid-cols-2 gap-6 items-end">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-900 mb-2">Nama Barang:</label>
                                        <input
                                            :value="detail.nama_barang"
                                            type="text"
                                            disabled
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50"
                                        />
                                    </div>
                                    <div class="flex items-end gap-4">
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-900 mb-2">Qty:</label>
                                            <input
                                                :value="detail.qty"
                                                type="text"
                                                disabled
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            @click="updateQtyCreate(index, detail.qty + 1)"
                                            class="w-10 h-10 flex items-center justify-center border-2 border-gray-900 rounded-full hover:bg-gray-100 transition"
                                        >
                                            <PlusIcon :size="20" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="updateQtyCreate(index, detail.qty - 1)"
                                            class="w-10 h-10 flex items-center justify-center border-2 border-gray-900 rounded-full hover:bg-gray-100 transition"
                                        >
                                            <MinusIcon :size="20" />
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6 items-end pt-4 border-t">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-900 mb-2">Nama Barang:</label>
                                        <select
                                            v-model="selectedBarang"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option value="">Pilih Barang...</option>
                                            <option v-for="barang in barangList" :key="barang.id" :value="barang.id">
                                                {{ barang.nama_barang }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="flex items-end gap-4">
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-900 mb-2">Qty:</label>
                                            <input
                                                v-model.number="selectedQty"
                                                type="number"
                                                min="1"
                                                placeholder="1"
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            @click="addBarangCreate"
                                            class="w-10 h-10 flex items-center justify-center border-2 border-gray-900 rounded-full hover:bg-gray-100 transition"
                                        >
                                            <PlusIcon :size="20" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: Summary -->
                        <div v-show="currentStep === 3">
                            <div class="space-y-3 mb-6 pb-6 border-b">
                                <div class="flex justify-between text-gray-700">
                                    <span class="font-medium">Subtotal:</span>
                                    <span class="font-semibold">Rp {{ subtotalCreate.toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-700">
                                    <span class="font-medium">Diskon ({{ createForm.diskon }}%):</span>
                                    <span class="font-semibold text-red-600">- Rp {{ diskonAmountCreate.toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-700">
                                    <span class="font-medium">Setelah Diskon:</span>
                                    <span class="font-semibold">Rp {{ afterDiskonCreate.toLocaleString('id-ID') }}</span>
                                </div>
                                <div v-if="createForm.ppn" class="flex justify-between text-gray-700">
                                    <span class="font-medium">PPN (11%):</span>
                                    <span class="font-semibold text-orange-600">+ Rp {{ ppnAmountCreate.toLocaleString('id-ID') }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between text-xl font-bold text-gray-900">
                                <span>TOTAL:</span>
                                <span class="text-blue-600">Rp {{ totalCreate.toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 px-6 py-4 border-t bg-gray-50">
                        <button
                            v-if="currentStep > 1"
                            type="button"
                            @click="prevStepCreate"
                            class="px-8 py-2.5 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium"
                        >
                            Kembali
                        </button>
                        <button
                            v-if="currentStep < 3"
                            type="button"
                            @click="nextStepCreate"
                            class="px-8 py-2.5 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                        >
                            Selanjutnya
                        </button>
                        <button
                            v-if="currentStep === 3"
                            type="button"
                            @click="submitCreate"
                            class="px-8 py-2.5 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                        >
                            Simpan
                        </button>
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="px-8 py-2.5 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeEditModal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeEditModal"></div>

                <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Invoice</h3>
                        <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                            <XIcon :size="24" />
                        </button>
                    </div>

                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <!-- Data Client -->
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-900 mb-4">Data Client</h4>
                            
                            <div class="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Client</label>
                                    <input 
                                        v-model="editForm.nama_client"
                                        type="text"
                                        placeholder="Masukan Nama Client..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Client</label>
                                    <input 
                                        v-model="editForm.nomor_client"
                                        type="text"
                                        placeholder="Masukan Nomor Client..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                    <input 
                                        v-model="editForm.tanggal"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Invoice</label>
                                    <input 
                                        type="text"
                                        :value="editingInvoice?.nomor_invoice"
                                        disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                                    />
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Client</label>
                                <textarea 
                                    v-model="editForm.alamat_client"
                                    placeholder="Masukan Alamat..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Diskon (%)</label>
                                    <input 
                                        v-model.number="editForm.diskon"
                                        type="number"
                                        min="0"
                                        max="100"
                                        placeholder="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">PPN</label>
                                    <select 
                                        v-model="editForm.ppn"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option :value="false">Tidak</option>
                                        <option :value="true">Ya (11%)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Invoice</label>
                                    <input 
                                        type="text"
                                        :value="editingInvoice?.tipe_invoice"
                                        disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Barang -->
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-900 mb-4">Daftar Barang</h4>
                            
                            <!-- Daftar barang yang sudah dipilih -->
                            <div class="mb-4 max-h-60 overflow-y-auto">
                                <table class="w-full border-collapse">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 border-b">Nama Barang</th>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 border-b">Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 border-b">Harga</th>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 border-b">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="editForm.details.length === 0">
                                            <td colspan="4" class="px-3 py-3 text-center text-gray-500 text-sm">Belum ada barang</td>
                                        </tr>
                                        <tr v-for="(detail, index) in editForm.details" :key="index" class="border-b hover:bg-gray-50">
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ detail.nama_barang }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button 
                                                        type="button"
                                                        @click="updateQtyEdit(index, detail.qty - 1)"
                                                        class="p-1 text-red-500 hover:bg-red-50 rounded"
                                                    >
                                                        <MinusIcon :size="14" />
                                                    </button>
                                                    <input 
                                                        :value="detail.qty"
                                                        type="number"
                                                        min="1"
                                                        @change="(e) => updateQtyEdit(index, parseInt(e.target.value))"
                                                        class="w-12 px-1 py-1 border border-gray-300 rounded text-center text-sm"
                                                    />
                                                    <button 
                                                        type="button"
                                                        @click="updateQtyEdit(index, detail.qty + 1)"
                                                        class="p-1 text-blue-500 hover:bg-blue-50 rounded"
                                                    >
                                                        <PlusIcon :size="14" />
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right text-sm text-gray-900">
                                                Rp {{ Number(detail.harga).toLocaleString('id-ID') }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button 
                                                    type="button"
                                                    @click="removeBarangEdit(index)"
                                                    class="p-1 text-red-500 hover:bg-red-50 rounded"
                                                >
                                                    <Trash2Icon :size="16" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Form tambah barang -->
                            <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                                    <select 
                                        v-model="selectedBarang"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">-- Pilih --</option>
                                        <option v-for="barang in barangList" :key="barang.id" :value="barang.id">
                                            {{ barang.nama_barang }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Qty</label>
                                    <input 
                                        v-model.number="selectedQty"
                                        type="number"
                                        min="1"
                                        placeholder="1"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <button 
                                        type="button"
                                        @click="addBarangEdit"
                                        class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium flex items-center justify-center gap-2 text-sm"
                                    >
                                        <PlusIcon :size="16" />
                                        Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-900 mb-4">Summary</h4>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-gray-700 text-sm">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold">Rp {{ subtotalEdit.toLocaleString('id-ID') }}</span>
                                </div>
                                <div v-if="editForm.diskon > 0" class="flex justify-between text-gray-700 text-sm">
                                    <span>Diskon ({{ editForm.diskon }}%):</span>
                                    <span class="font-semibold text-red-600">- Rp {{ diskonAmountEdit.toLocaleString('id-ID') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-700 text-sm">
                                    <span>Setelah Diskon:</span>
                                    <span class="font-semibold">Rp {{ afterDiskonEdit.toLocaleString('id-ID') }}</span>
                                </div>
                                <div v-if="editForm.ppn" class="flex justify-between text-gray-700 text-sm">
                                    <span>PPN (11%):</span>
                                    <span class="font-semibold text-orange-600">+ Rp {{ ppnAmountEdit.toLocaleString('id-ID') }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between text-lg font-bold text-gray-900 pt-3 border-t">
                                <span>Total:</span>
                                <span class="text-blue-600">Rp {{ totalEdit.toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 px-6 py-4 border-t bg-gray-50">
                        <button 
                            type="button"
                            @click="submitEdit"
                            :disabled="editForm.processing"
                            class="flex-1 px-6 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-semibold disabled:opacity-50"
                        >
                            Perbarui
                        </button>
                        <button 
                            type="button"
                            @click="closeEditModal"
                            class="flex-1 px-6 py-2 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-semibold"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>