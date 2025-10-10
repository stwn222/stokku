<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Plus, Minus, Trash2 } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    invoice: Object,
    barangList: Array,
});

const form = useForm({
    nama_client: props.invoice.nama_client,
    nomor_client: props.invoice.nomor_client,
    tanggal: props.invoice.tanggal,
    alamat_client: props.invoice.alamat_client,
    diskon: props.invoice.diskon,
    ppn: props.invoice.ppn,
    details: props.invoice.details.map(d => ({
        barang_id: d.barang_id,
        id_barang: d.barang.id_barang,
        nama_barang: d.barang.nama_barang,
        qty: d.qty,
        harga: Number(d.harga),
    })),
});

const selectedBarang = ref('');
const selectedQty = ref(1);

// Computed untuk kalkulasi
const subtotal = computed(() => {
    return form.details.reduce((sum, item) => {
        const qty = Number(item.qty) || 0;
        const harga = Number(item.harga) || 0;
        return sum + (qty * harga);
    }, 0);
});

const diskonAmount = computed(() => {
    const diskon = Number(form.diskon) || 0;
    return (subtotal.value * diskon) / 100;
});

const afterDiskon = computed(() => {
    return subtotal.value - diskonAmount.value;
});

const ppnAmount = computed(() => {
    return form.ppn ? (afterDiskon.value * 11) / 100 : 0;
});

const total = computed(() => {
    return afterDiskon.value + ppnAmount.value;
});

// Format tanggal untuk display
const formatTanggal = (tanggal) => {
    const date = new Date(tanggal);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '.');
};

// Tambah barang
const addBarang = () => {
    if (!selectedBarang.value || selectedQty.value < 1) {
        alert('Pilih barang dan masukkan jumlah yang valid');
        return;
    }

    const barang = props.barangList.find(b => b.id == selectedBarang.value);
    const existingDetail = form.details.find(d => d.barang_id == selectedBarang.value);
    
    if (existingDetail) {
        existingDetail.qty += parseInt(selectedQty.value);
    } else {
        form.details.push({
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

// Hapus barang
const removeBarang = (index) => {
    form.details.splice(index, 1);
};

// Update qty
const updateQty = (index, newQty) => {
    if (newQty > 0) {
        form.details[index].qty = newQty;
    }
};

// Submit form
const submit = () => {
    if (form.details.length === 0) {
        alert('Tambahkan minimal satu barang');
        return;
    }

    form.put(route('invoice.update', props.invoice.id), {
        onSuccess: () => {
            alert('Invoice berhasil diperbarui');
        },
    });
};

// Batal
const handleCancel = () => {
    window.location.href = route('invoice.index');
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

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-white mb-6">
            <a href="#" @click="() => $inertia.visit(route('dashboard'))" class="hover:text-blue-100">Home</a>
            <span>></span>
            <a href="#" @click="() => $inertia.visit(route('invoice.index'))" class="hover:text-blue-100">Invoice</a>
            <span>></span>
            <span>Edit</span>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- DATA CLIENT -->
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Edit Invoice - Data Client</h2>
                
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Client</label>
                        <input 
                            v-model="form.nama_client"
                            type="text"
                            placeholder="Masukan Nama User..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Client</label>
                        <input 
                            v-model="form.nomor_client"
                            type="text"
                            placeholder="-- Pilih --"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input 
                            v-model="form.tanggal"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Invoice</label>
                        <input 
                            type="text"
                            :value="invoice.nomor_invoice"
                            disabled
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                        />
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Client</label>
                    <textarea 
                        v-model="form.alamat_client"
                        placeholder="Masukan Alamat..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        rows="3"
                    ></textarea>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diskon</label>
                        <div class="flex items-center">
                            <input 
                                v-model.number="form.diskon"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <span class="ml-2 text-gray-700 font-medium">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">PPN</label>
                        <select 
                            v-model="form.ppn"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option :value="false">-- Pilih --</option>
                            <option :value="true">Ya (11%)</option>
                            <option :value="false">Tidak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Invoice</label>
                        <input 
                            type="text"
                            :value="invoice.tipe_invoice"
                            disabled
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                        />
                    </div>
                </div>
            </div>

            <!-- PILIH BARANG -->
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Pilih Barang</h2>
                
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                        <select 
                            v-model="selectedBarang"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">-- Pilih --</option>
                            <option v-for="barang in barangList" :key="barang.id" :value="barang.id">
                                {{ barang.nama_barang }} ({{ barang.id_barang }})
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div class="flex items-end">
                        <button 
                            type="button"
                            @click="addBarang"
                            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-medium flex items-center justify-center gap-2"
                        >
                            <Plus :size="20" />
                            Tambah
                        </button>
                    </div>
                </div>

                <!-- Tabel Detail Barang -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">No</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">Kode</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">Nama Barang</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border-b">Qty</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700 border-b">Harga</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700 border-b">Total</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="form.details.length === 0">
                                <td colspan="7" class="px-4 py-4 text-center text-gray-500">Belum ada barang</td>
                            </tr>
                            <tr v-for="(detail, index) in form.details" :key="index" class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ index + 1 }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ detail.id_barang }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ detail.nama_barang }}</td>
                                <td class="px-4 py-2 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
                                            type="button"
                                            @click="updateQty(index, detail.qty - 1)"
                                            class="p-1 text-red-500 hover:bg-red-50 rounded transition"
                                        >
                                            <Minus :size="16" />
                                        </button>
                                        <input 
                                            :value="detail.qty"
                                            type="number"
                                            min="1"
                                            @change="(e) => updateQty(index, parseInt(e.target.value))"
                                            class="w-12 px-2 py-1 border border-gray-300 rounded text-center"
                                        />
                                        <button 
                                            type="button"
                                            @click="updateQty(index, detail.qty + 1)"
                                            class="p-1 text-blue-500 hover:bg-blue-50 rounded transition"
                                        >
                                            <Plus :size="16" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-gray-900">
                                    Rp {{ Number(detail.harga).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900">
                                    Rp {{ (detail.qty * Number(detail.harga)).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button 
                                        type="button"
                                        @click="removeBarang(index)"
                                        class="p-1 text-red-500 hover:bg-red-50 rounded transition"
                                    >
                                        <Trash2 :size="18" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SUMMARY -->
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Summary</h2>
                
                <div class="space-y-3 mb-6 pb-6 border-b">
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal:</span>
                        <span class="font-semibold">Rp {{ subtotal.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="form.diskon > 0" class="flex justify-between text-gray-700">
                        <span>Diskon ({{ form.diskon }}%):</span>
                        <span class="font-semibold text-red-600">- Rp {{ diskonAmount.toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Setelah Diskon:</span>
                        <span class="font-semibold">Rp {{ afterDiskon.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="form.ppn" class="flex justify-between text-gray-700">
                        <span>PPN (11%):</span>
                        <span class="font-semibold text-orange-600">+ Rp {{ ppnAmount.toLocaleString('id-ID') }}</span>
                    </div>
                </div>

                <div class="flex justify-between text-xl font-bold text-gray-900 mb-6">
                    <span>Total:</span>
                    <span class="text-blue-600">Rp {{ total.toLocaleString('id-ID') }}</span>
                </div>

                <div class="flex gap-4">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 px-6 py-3 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-semibold disabled:opacity-50"
                    >
                        Perbarui
                    </button>
                    <button 
                        type="button"
                        @click="handleCancel"
                        class="flex-1 px-6 py-3 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-semibold"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>