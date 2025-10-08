<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, TrendingUp, Pencil, Trash2, Plus, X } from 'lucide-vue-next';

const props = defineProps({
    barangMasuks: Object,
    barangs: Array,
    nextKodeTransaksi: String,
    tanggalHariIni: String,
    filters: Object,
});

const perPage = ref(props.filters?.per_page || 10);
const search = ref(props.filters?.search || '');
const showModal = ref(false);
const modalMode = ref('create');

const form = ref({
    id: null,
    kode_transaksi: props.nextKodeTransaksi,
    barang_id: '',
    tanggal: props.tanggalHariIni,
    jumlah: 1,
    harga_beli: 0,
    is_ppn: false,
    vendor: '',
    keterangan: '',
});

watch([perPage, search], () => {
    router.get(route('barang-masuk.index'), {
        per_page: perPage.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const selectedBarang = computed(() => {
    if (!form.value.barang_id) return null;
    return props.barangs.find(b => b.id == form.value.barang_id);
});

const ppnAmount = computed(() => {
    if (!form.value.is_ppn) return 0;
    return form.value.harga_beli * form.value.jumlah * 0.11;
});

const totalAmount = computed(() => {
    const subtotal = form.value.harga_beli * form.value.jumlah;
    return subtotal + ppnAmount.value;
});

const openCreateModal = () => {
    modalMode.value = 'create';
    form.value = {
        id: null,
        kode_transaksi: props.nextKodeTransaksi,
        barang_id: '',
        tanggal: props.tanggalHariIni,
        jumlah: 1,
        harga_beli: 0,
        is_ppn: false,
        vendor: '',
        keterangan: '',
    };
    showModal.value = true;
};

const openEditModal = (barangMasuk) => {
    modalMode.value = 'edit';
    form.value = {
        id: barangMasuk.id,
        kode_transaksi: barangMasuk.kode_transaksi,
        barang_id: barangMasuk.barang_id,
        tanggal: barangMasuk.tanggal,
        jumlah: barangMasuk.jumlah,
        harga_beli: barangMasuk.harga_beli,
        is_ppn: barangMasuk.is_ppn,
        vendor: barangMasuk.vendor,
        keterangan: barangMasuk.keterangan || '',
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        router.post(route('barang-masuk.store'), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        router.put(route('barang-masuk.update', form.value.id), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deleteBarangMasuk = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        router.delete(route('barang-masuk.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Barang Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span>Barang Masuk</span>
                <ChevronRight :size="16" />
                <span class="font-semibold">Data</span>
            </div>
        </template>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Data Barang Masuk</h2>
                    <button 
                        @click="openCreateModal"
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Harga Beli</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">PPN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Vendor</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(item, index) in barangMasuks.data" 
                            :key="item.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-sm text-gray-700 border">
                                {{ barangMasuks.from + index }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.kode_transaksi }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang.id_barang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.barang.nama_barang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatDate(item.tanggal) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.jumlah }} {{ item.barang.satuan.nama_satuan }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatCurrency(item.harga_beli) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatCurrency(item.ppn) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ item.vendor }}</td>
                            <td class="px-4 py-3 text-sm border">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(item)"
                                        class="p-1.5 bg-green-500 hover:bg-green-600 text-white rounded-full transition"
                                        title="Edit"
                                    >
                                        <Pencil :size="14" />
                                    </button>
                                    <button 
                                        @click="deleteBarangMasuk(item.id)"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition"
                                        title="Hapus"
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="barangMasuks.data.length === 0">
                            <td colspan="10" class="px-4 py-8 text-center text-gray-500 border">
                                Tidak ada data barang masuk
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ barangMasuks.from || 0 }} sampai {{ barangMasuks.to || 0 }} dari {{ barangMasuks.total || 0 }} data
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        @click="router.get(barangMasuks.prev_page_url)"
                        :disabled="!barangMasuks.prev_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &lt;
                    </button>

                    <template v-for="link in barangMasuks.links.slice(1, -1)" :key="link.label">
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
                        @click="router.get(barangMasuks.next_page_url)"
                        :disabled="!barangMasuks.next_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Popup -->
        <div 
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeModal"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ modalMode === 'create' ? 'Entri Barang Masuk' : 'Edit Barang Masuk' }}
                    </h3>
                    <button 
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <X :size="24" />
                    </button>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="submitForm" class="p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Kode Transaksi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Transaksi :</label>
                            <input 
                                v-model="form.kode_transaksi"
                                type="text"
                                readonly
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50"
                            />
                        </div>

                        <!-- Kode Barang -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Barang :</label>
                            <input 
                                :value="selectedBarang?.id_barang || ''"
                                type="text"
                                readonly
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50"
                                placeholder="Pilih barang terlebih dahulu"
                            />
                        </div>

                        <!-- Nama Barang -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang :</label>
                            <select 
                                v-model="form.barang_id"
                                required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            >
                                <option value="">-- Pilih --</option>
                                <option v-for="barang in barangs" :key="barang.id" :value="barang.id">
                                    {{ barang.nama_barang }} ({{ barang.id_barang }})
                                </option>
                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal :</label>
                            <input 
                                v-model="form.tanggal"
                                type="date"
                                required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            />
                        </div>

                        <!-- Jumlah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah :</label>
                            <input 
                                v-model="form.jumlah"
                                type="number"
                                required
                                min="1"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            />
                        </div>

                        <!-- Harga Beli -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga Beli :</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-sm text-gray-500">Rp</span>
                                <input 
                                    v-model="form.harga_beli"
                                    type="number"
                                    required
                                    min="0"
                                    step="0.01"
                                    class="w-full border border-gray-300 rounded pl-10 pr-3 py-2 text-sm"
                                    placeholder="0"
                                />
                            </div>
                        </div>

                        <!-- Vendor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Vendor :</label>
                            <input 
                                v-model="form.vendor"
                                type="text"
                                required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                                placeholder="Masukan Vendor Barang..."
                            />
                        </div>

                        <!-- PPN Checkbox & Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PPN :</label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2">
                                    <input 
                                        v-model="form.is_ppn"
                                        type="checkbox"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <span class="text-sm">Termasuk PPN (11%)</span>
                                </label>
                                <span v-if="form.is_ppn" class="text-sm font-semibold text-blue-600">
                                    {{ formatCurrency(ppnAmount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan :</label>
                        <textarea 
                            v-model="form.keterangan"
                            rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            placeholder="Keterangan tambahan (opsional)"
                        ></textarea>
                    </div>

                    <!-- Summary -->
                    <div v-if="form.barang_id && form.harga_beli > 0" class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Subtotal:</span>
                                <p class="font-semibold text-lg">{{ formatCurrency(form.harga_beli * form.jumlah) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">PPN (11%):</span>
                                <p class="font-semibold text-lg">{{ formatCurrency(ppnAmount) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Total:</span>
                                <p class="font-bold text-blue-600 text-xl">{{ formatCurrency(totalAmount) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button 
                            type="button"
                            @click="closeModal"
                            class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>