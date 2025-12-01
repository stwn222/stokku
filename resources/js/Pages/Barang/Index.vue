<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Pencil, Trash2, Plus, Check, ChevronsUpDown, ChevronLeft } from 'lucide-vue-next';
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from '@headlessui/vue';

const props = defineProps({
    barangs: Object,
    jenisBarangs: Array,
    satuans: Array,
    filters: Object,
});

const perPage = ref(props.filters.per_page || 10);
const search = ref(props.filters.search || '');
const showModal = ref(false);
const modalMode = ref('create');
const form = ref({
    id: null,
    jenis_barang_id: '',
    nama_barang: '',
    stok: 0,
    stok_minimum: 0,
    stok_maksimum: 0,
    satuan_id: '',
    harga_jual: 0,
});

const formattedHargaJual = computed({
    get() {
        if (form.value.harga_jual === null || form.value.harga_jual === '') {
            return '';
        }
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(form.value.harga_jual);
    },
    set(newValue) {
        const numericValue = parseInt(newValue.replace(/[^0-9]/g, ''), 10);
        form.value.harga_jual = isNaN(numericValue) ? 0 : numericValue;
    }
});

const previewIdBarang = ref('');
const jenisBarangQuery = ref('');
const filteredJenisBarangs = computed(() =>
    jenisBarangQuery.value === ''
        ? props.jenisBarangs
        : props.jenisBarangs.filter((jenis) =>
            jenis.nama_jenis
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(jenisBarangQuery.value.toLowerCase().replace(/\s+/g, ''))
        )
);

const comboboxButtonRef = ref(null);

const handleComboboxFocus = () => {
    if (comboboxButtonRef.value) {
        comboboxButtonRef.value.click();
    }
};

watch([perPage, search], () => {
    router.get(route('barang.index'), {
        per_page: perPage.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

watch(() => form.value.jenis_barang_id, async (newVal) => {
    if (newVal && modalMode.value === 'create') {
        const jenisBarang = props.jenisBarangs.find(jb => jb.id == newVal);
        if (jenisBarang) {
            try {
                const response = await fetch(route('barang.next-id', newVal));
                const data = await response.json();
                previewIdBarang.value = data.next_id;
            } catch (error) {
                console.error('Error fetching next ID:', error);
            }
        }
    } else if (!newVal) {
        previewIdBarang.value = '';
    }
});

const openCreateModal = () => {
    modalMode.value = 'create';
    form.value = {
        id: null,
        jenis_barang_id: '',
        nama_barang: '',
        stok: 0,
        stok_minimum: 0,
        stok_maksimum: 0,
        satuan_id: '',
        harga_jual: 0,
    };
    previewIdBarang.value = '';
    jenisBarangQuery.value = '';
    showModal.value = true;
};

const openEditModal = (barang) => {
    modalMode.value = 'edit';
    form.value = {
        id: barang.id,
        jenis_barang_id: barang.jenis_barang_id,
        nama_barang: barang.nama_barang,
        stok: barang.stok,
        stok_minimum: barang.stok_minimum || 0,
        stok_maksimum: barang.stok_maksimum || 0,
        satuan_id: barang.satuan_id,
        harga_jual: barang.harga_jual,
    };
    previewIdBarang.value = barang.id_barang;
    jenisBarangQuery.value = '';
    showModal.value = true;
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        router.post(route('barang.store'), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        router.put(route('barang.update', form.value.id), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deleteBarang = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('barang.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const getJenisBarangNama = (barang) => {
    return barang?.jenis_barang?.nama_jenis || '-';
};

const getSatuanNama = (barang) => {
    return barang?.satuan?.nama_satuan || '-';
};

const isLowStock = (barang) => {
    return barang.stok < (barang.stok_minimum || 10);
};

const changePage = (url) => {
    if (url) {
        router.get(url, {
            per_page: perPage.value,
            search: search.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Data Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span>Barang</span>
                <ChevronRight :size="16" />
                <span class="font-semibold">Data</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Data Barang</h2>
                    <button 
                        @click="openCreateModal"
                        class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition"
                    >
                        <Plus :size="18" />
                        Entri Data
                    </button>
                </div>
            </div>

            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700">Tampilkan</label>
                        <select 
                            v-model="perPage"
                            class="border border-gray-300 rounded px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500"
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
                            class="border border-gray-300 rounded px-3 py-1 text-sm w-64 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Cari barang..."
                        />
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                        <span class="text-gray-600">Stok di Bawah Minimum</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">ID Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Stok</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Harga Jual</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(barang, index) in barangs.data" 
                            :key="barang.id"
                            :class="[
                                'hover:bg-gray-50',
                                isLowStock(barang) ? 'bg-red-100' : ''
                            ]"
                        >
                            <td class="px-4 py-3 text-sm text-gray-700 border">
                                {{ barangs.from + index }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ barang.id_barang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ barang.nama_barang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ getJenisBarangNama(barang) }}</td>
                            <td class="px-4 py-3 text-sm border">
                                <span :class="[
                                    'font-semibold',
                                    isLowStock(barang) ? 'text-red-600' : 'text-gray-700'
                                ]">
                                    {{ barang.stok }}
                                </span>
                                <span v-if="isLowStock(barang)" class="ml-2 text-xs bg-red-500 text-white px-2 py-0.5 rounded-full">
                                    Stok Rendah
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ getSatuanNama(barang) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border">{{ formatCurrency(barang.harga_jual) }}</td>
                            <td class="px-4 py-3 text-sm border">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(barang)"
                                        class="p-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full transition"
                                        title="Edit"
                                    >
                                        <Pencil :size="14" />
                                    </button>
                                    <button 
                                        @click="deleteBarang(barang.id)"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition"
                                        title="Hapus"
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="barangs.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 border">
                                Tidak ada data barang
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            <div class="p-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ barangs.from }} sampai {{ barangs.to }} dari {{ barangs.total }} data
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Previous Button -->
                        <button
                            @click="changePage(barangs.prev_page_url)"
                            :disabled="!barangs.prev_page_url"
                            :class="[
                                'px-3 py-2 rounded transition flex items-center gap-1',
                                barangs.prev_page_url
                                    ? 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700'
                                    : 'bg-gray-100 border border-gray-200 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            <ChevronLeft :size="16" />
                        </button>

                        <!-- Page Numbers -->
                        <template v-for="(link, index) in barangs.links" :key="index">
                            <button
                                v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                                @click="changePage(link.url)"
                                :disabled="!link.url"
                                :class="[
                                    'px-4 py-2 rounded transition min-w-[40px]',
                                    link.active
                                        ? 'bg-blue-500 text-white font-semibold'
                                        : link.url
                                            ? 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700'
                                            : 'bg-white border border-gray-300 text-gray-400 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            >
                            </button>
                        </template>

                        <!-- Next Button -->
                        <button
                            @click="changePage(barangs.next_page_url)"
                            :disabled="!barangs.next_page_url"
                            :class="[
                                'px-3 py-2 rounded transition flex items-center gap-1',
                                barangs.next_page_url
                                    ? 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700'
                                    : 'bg-gray-100 border border-gray-200 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            <ChevronRight :size="16" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div 
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    {{ modalMode === 'create' ? 'Tambah Data Barang' : 'Edit Data Barang' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Barang</label>
                        <Combobox v-model="form.jenis_barang_id" as="div" class="relative">
                            <div class="relative">
                                <ComboboxInput
                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    @change="jenisBarangQuery = $event.target.value"
                                    :display-value="(jenisId) => jenisBarangs.find(j => j.id === jenisId)?.nama_jenis ?? ''"
                                    placeholder="Pilih atau cari jenis barang"
                                    required
                                    @focus="handleComboboxFocus"
                                    autocomplete="off"
                                />
                                <ComboboxButton ref="comboboxButtonRef" class="absolute inset-y-0 right-0 flex items-center pr-2">
                                    <ChevronsUpDown class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </ComboboxButton>
                            </div>
                            <TransitionRoot
                                leave="transition ease-in duration-100"
                                leave-from="opacity-100"
                                leave-to="opacity-0"
                                @after-leave="jenisBarangQuery = ''"
                            >
                                <ComboboxOptions class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                    <div v-if="filteredJenisBarangs.length === 0 && jenisBarangQuery !== ''" class="relative cursor-default select-none py-2 px-4 text-gray-700">
                                        Tidak ditemukan.
                                    </div>
                                    <ComboboxOption
                                        v-for="jenis in filteredJenisBarangs"
                                        :key="jenis.id"
                                        :value="jenis.id"
                                        v-slot="{ selected, active }"
                                        as="template"
                                    >
                                        <li :class="['relative cursor-default select-none py-2 pl-10 pr-4', active ? 'bg-blue-500 text-white' : 'text-gray-900']">
                                            <span :class="['block truncate', selected ? 'font-medium' : 'font-normal']">
                                                {{ jenis.nama_jenis }} ({{ jenis.kode_jenis }})
                                            </span>
                                            <span v-if="selected" :class="['absolute inset-y-0 left-0 flex items-center pl-3', active ? 'text-white' : 'text-blue-600']">
                                                <Check class="h-5 w-5" aria-hidden="true" />
                                            </span>
                                        </li>
                                    </ComboboxOption>
                                </ComboboxOptions>
                            </TransitionRoot>
                        </Combobox>
                    </div>

                    <div v-if="previewIdBarang" class="bg-blue-50 border border-blue-200 rounded p-3">
                        <p class="text-xs text-gray-600 mb-1">ID Barang yang akan digunakan:</p>
                        <p class="text-lg font-bold text-blue-600">{{ previewIdBarang }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                        <input 
                            v-model="form.nama_barang"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama barang"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                            <select 
                                v-model="form.satuan_id"
                                required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Pilih Satuan</option>
                                <option v-for="satuan in satuans" :key="satuan.id" :value="satuan.id">
                                    {{ satuan.nama_satuan }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                            <input 
                                v-model="form.stok"
                                type="number"
                                required
                                min="0"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0"
                                :disabled="modalMode === 'edit'"
                                :title="modalMode === 'edit' ? 'Stok awal tidak bisa diubah. Gunakan modul penyesuaian stok.' : ''"
                                :class="{ 'bg-gray-100' : modalMode === 'edit' }"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum</label>
                            <input 
                                v-model="form.stok_minimum"
                                type="number"
                                min="0"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Maksimum</label>
                            <input 
                                v-model="form.stok_maksimum"
                                type="number"
                                min="0"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                        <input 
                            v-model="formattedHargaJual"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Rp 0"
                        />
                    </div>

                    <div class="flex items-center gap-2 pt-4">
                        <button 
                            type="submit"
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition"
                        >
                            {{ modalMode === 'create' ? 'Simpan' : 'Update' }}
                        </button>
                        <button 
                            type="button"
                            @click="showModal = false"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded transition"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>