<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Pencil, Trash2, Plus, X } from 'lucide-vue-next';

const props = defineProps({
    barangKeluars: Object,
    barangs: Array,
    nextKodeTransaksi: String,
    tanggalHariIni: String,
    filters: Object,
});

const showModal = ref(false);
const isEdit = ref(false);
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

const form = useForm({
    id: null,
    kode_transaksi: props.nextKodeTransaksi,
    barang_id: '',
    tanggal: props.tanggalHariIni,
    jumlah: 1,
    harga_jual: 0,
    is_ppn: false,
    keterangan: '',
});

const selectedBarang = ref(null);

const formatRupiah = (angka) => {
    if (!angka) return 'Rp 0';
    const number = parseFloat(angka);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(number);
};

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} - ${hours}:${minutes}`;
};

const subtotal = computed(() => {
    return form.jumlah * form.harga_jual;
});

const ppnAmount = computed(() => {
    if (form.is_ppn) {
        return subtotal.value * 0.11;
    }
    return 0;
});

const total = computed(() => {
    return subtotal.value + ppnAmount.value;
});

watch(() => form.barang_id, (newValue) => {
    if (newValue) {
        const barang = props.barangs.find(b => b.id == newValue);
        if (barang) {
            selectedBarang.value = barang;
            form.harga_jual = barang.harga_jual;
        }
    } else {
        selectedBarang.value = null;
        form.harga_jual = 0;
    }
});

watch([search, perPage], () => {
    router.get(route('barang-keluar.index'), {
        search: search.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const openModal = (barangKeluar = null) => {
    if (barangKeluar) {
        isEdit.value = true;
        form.id = barangKeluar.id;
        form.kode_transaksi = barangKeluar.kode_transaksi;
        form.barang_id = barangKeluar.barang_id;
        form.tanggal = barangKeluar.tanggal;
        form.jumlah = barangKeluar.jumlah;
        form.harga_jual = barangKeluar.harga_jual;
        form.is_ppn = barangKeluar.is_ppn == 1;
        form.keterangan = barangKeluar.keterangan || '';
    } else {
        isEdit.value = false;
        form.reset();
        form.kode_transaksi = props.nextKodeTransaksi;
        form.tanggal = props.tanggalHariIni;
        form.jumlah = 1;
        form.is_ppn = false;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
    selectedBarang.value = null;
};

const submit = () => {
    const options = {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    };
    if (isEdit.value) {
        form.put(route('barang-keluar.update', form.id), options);
    } else {
        form.post(route('barang-keluar.store'), options);
    }
};

const deleteBarangKeluar = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('barang-keluar.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Barang Keluar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span>Barang Keluar</span>
                <ChevronRight :size="16" />
                <span class="font-semibold">Data</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Data Barang Keluar</h2>
                    <button
                        @click="openModal()"
                        class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition"
                    >
                        <Plus :size="18" />
                        Entri Data
                    </button>
                </div>
            </div>

            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700">Tampilkan</label>
                        <select v-model="perPage" class="border border-gray-300 rounded px-2 py-1 text-sm">
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
                            placeholder="Cari transaksi..."
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Kode Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Kode Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Harga Jual</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">PPN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in barangKeluars.data" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ barangKeluars.from + index }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.kode_transaksi }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang?.id_barang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang?.nama_barang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatDateTime(item.created_at) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.jumlah }} {{ item.barang?.satuan?.nama_satuan }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatRupiah(item.harga_jual) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatRupiah(item.ppn) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border font-medium">{{ formatRupiah(item.total) }}</td>
                            <td class="px-4 py-3 text-sm border">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openModal(item)" class="p-1.5 bg-green-500 hover:bg-green-600 text-white rounded-full transition" title="Edit">
                                        <Pencil :size="14" />
                                    </button>
                                    <button @click="deleteBarangKeluar(item.id)" class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition" title="Hapus">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="barangKeluars.data.length === 0">
                            <td colspan="10" class="px-4 py-8 text-center text-gray-500 border">
                                Tidak ada data barang keluar
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ barangKeluars.from || 0 }} sampai {{ barangKeluars.to || 0 }} dari {{ barangKeluars.total || 0 }} data
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="router.get(barangKeluars.prev_page_url)"
                        :disabled="!barangKeluars.prev_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &lt;
                    </button>

                    <template v-for="link in barangKeluars.links.slice(1, -1)" :key="link.label">
                        <button
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
                    </template>

                    <button
                        @click="router.get(barangKeluars.next_page_url)"
                        :disabled="!barangKeluars.next_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Edit' : 'Entri' }} Barang Keluar</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                        <X :size="24" />
                    </button>
                </div>

                <form @submit.prevent="submit">
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Transaksi :</label>
                                <input v-model="form.kode_transaksi" type="text" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Barang :</label>
                                <input :value="selectedBarang?.id_barang || ''" type="text" readonly placeholder="-- Pilih Barang Dulu --" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang :</label>
                                <select v-model="form.barang_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" :class="{ 'border-red-500': form.errors.barang_id }">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="barang in barangs" :key="barang.id" :value="barang.id">
                                        {{ barang.nama_barang }} (Stok: {{ barang.stok }})
                                    </option>
                                </select>
                                <p v-if="form.errors.barang_id" class="mt-1 text-sm text-red-600">{{ form.errors.barang_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal :</label>
                                <input v-model="form.tanggal" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" :class="{ 'border-red-500': form.errors.tanggal }" />
                                <p v-if="form.errors.tanggal" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah :</label>
                                <input v-model.number="form.jumlah" type="number" min="1" placeholder="Masukan Jumlah Barang..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm" :class="{ 'border-red-500': form.errors.jumlah }" />
                                <p v-if="form.errors.jumlah" class="mt-1 text-sm text-red-600">{{ form.errors.jumlah }}</p>
                                <p v-if="selectedBarang" class="mt-1 text-xs text-gray-500">Stok: {{ selectedBarang.stok }} {{ selectedBarang.satuan?.nama_satuan }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Jual :</label>
                                <input :value="formatRupiah(form.harga_jual)" type="text" readonly placeholder="Pilih barang terlebih dahulu" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" />
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">PPN :</label>
                                <div class="flex items-center">
                                    <input v-model="form.is_ppn" type="checkbox" id="ppn" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                    <label for="ppn" class="ml-2 text-sm text-gray-700">Ya, transaksi ini dikenakan PPN 11%</label>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan :</label>
                                <textarea v-model="form.keterangan" rows="3" placeholder="Keterangan tambahan (opsional)" class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
                            </div>
                        </div>

                        <div v-if="form.barang_id" class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Subtotal:</span>
                                    <p class="font-semibold text-lg">{{ formatRupiah(subtotal) }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">PPN (11%):</span>
                                    <p class="font-semibold text-lg">{{ formatRupiah(ppnAmount) }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Total:</span>
                                    <p class="font-bold text-blue-600 text-xl">{{ formatRupiah(total) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                        <button type="button" @click="closeModal" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>