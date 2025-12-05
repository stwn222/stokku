<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Pencil, Trash2, Plus, Package } from 'lucide-vue-next';

const props = defineProps({
    jenisBarangs: Object,
    filters: Object,
});

const perPage = ref(props.filters.per_page || 10);
const search = ref(props.filters.search || '');
const showModal = ref(false);
const modalMode = ref('create');
const form = ref({
    id: null,
    kode_jenis: '',
    nama_jenis: '',
    keterangan: '',
});

watch([perPage, search], () => {
    router.get(route('jenis-barang.index'), {
        per_page: perPage.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const openCreateModal = () => {
    modalMode.value = 'create';
    form.value = {
        id: null,
        kode_jenis: '',
        nama_jenis: '',
        keterangan: '',
    };
    showModal.value = true;
};

const openEditModal = (jenisBarang) => {
    modalMode.value = 'edit';
    form.value = {
        id: jenisBarang.id,
        kode_jenis: jenisBarang.kode_jenis,
        nama_jenis: jenisBarang.nama_jenis,
        keterangan: jenisBarang.keterangan || '',
    };
    showModal.value = true;
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        router.post(route('jenis-barang.store'), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        router.put(route('jenis-barang.update', form.value.id), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deleteJenisBarang = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jenis barang ini?')) {
        router.delete(route('jenis-barang.destroy', id));
    }
};
</script>

<template>
    <Head title="Jenis Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span class="text-sm md:text-base">Barang</span>
                <ChevronRight :size="16" />
                <span class="font-semibold text-sm md:text-base">Jenis Barang</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <!-- Header Card -->
            <div class="border-b border-gray-200 p-4 md:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800">Jenis Barang</h2>
                    <button 
                        @click="openCreateModal"
                        class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-3 md:px-4 py-2 rounded-lg transition text-sm md:text-base w-full sm:w-auto justify-center"
                    >
                        <Plus :size="18" />
                        Tambah Jenis
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <label class="text-xs md:text-sm text-gray-700 whitespace-nowrap">Tampilkan</label>
                        <select 
                            v-model="perPage"
                            class="border border-gray-300 rounded px-2 py-1 text-xs md:text-sm"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs md:text-sm text-gray-700">data</span>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <label class="text-xs md:text-sm text-gray-700 whitespace-nowrap">Cari:</label>
                        <input 
                            v-model="search"
                            type="text"
                            class="border border-gray-300 rounded px-3 py-1 text-xs md:text-sm w-full md:w-64"
                            placeholder="Cari jenis barang..."
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Kode Jenis</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Jenis</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Keterangan</th>
                            <th class="px-2 md:px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Jumlah Barang</th>
                            <th class="px-2 md:px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(jenis, index) in jenisBarangs.data" 
                            :key="jenis.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">
                                {{ jenisBarangs.from + index }}
                            </td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm border">
                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded font-mono font-semibold text-xs">
                                    {{ jenis.kode_jenis }}
                                </span>
                            </td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">{{ jenis.nama_jenis }}</td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-600 border">{{ jenis.keterangan || '-' }}</td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-center border">
                                <span class="inline-flex items-center justify-center w-7 h-7 md:w-8 md:h-8 bg-green-100 text-green-800 rounded-full font-semibold text-xs">
                                    {{ jenis.barangs_count }}
                                </span>
                            </td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm border">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(jenis)"
                                        class="p-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full transition"
                                        title="Edit"
                                    >
                                        <Pencil :size="14" />
                                    </button>
                                    <button 
                                        @click="deleteJenisBarang(jenis.id)"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition"
                                        title="Hapus"
                                        :disabled="jenis.barangs_count > 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': jenis.barangs_count > 0 }"
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="jenisBarangs.data.length === 0">
                            <td colspan="6" class="px-2 md:px-4 py-8 text-center text-gray-500 border text-xs md:text-sm">
                                Tidak ada data jenis barang
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 md:p-6 border-t border-gray-200">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-xs md:text-sm text-gray-700 text-center md:text-left">
                        Menampilkan {{ jenisBarangs.from }} sampai {{ jenisBarangs.to }} dari {{ jenisBarangs.total }} data
                    </div>

                    <div class="flex items-center gap-1 md:gap-2 flex-wrap justify-center">
                        <button 
                            @click="router.get(jenisBarangs.prev_page_url)"
                            :disabled="!jenisBarangs.prev_page_url"
                            class="px-2 md:px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-xs md:text-sm"
                        >
                            &lt;
                        </button>

                        <template v-for="link in jenisBarangs.links.slice(1, -1)" :key="link.label">
                            <button 
                                @click="link.url ? router.get(link.url) : null"
                                :class="[
                                    'px-2 md:px-3 py-1 border rounded text-xs md:text-sm',
                                    link.active 
                                        ? 'bg-blue-500 text-white border-blue-500' 
                                        : 'border-gray-300 hover:bg-gray-100'
                                ]"
                            >
                                {{ link.label }}
                            </button>
                        </template>

                        <button 
                            @click="router.get(jenisBarangs.next_page_url)"
                            :disabled="!jenisBarangs.next_page_url"
                            class="px-2 md:px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-xs md:text-sm"
                        >
                            &gt;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div 
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-4 md:p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-base md:text-lg font-bold text-gray-800 mb-4">
                    {{ modalMode === 'create' ? 'Tambah Jenis Barang' : 'Edit Jenis Barang' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                            Kode Jenis
                            <span class="text-xs text-gray-500">(Max 10 karakter, akan otomatis uppercase)</span>
                        </label>
                        <input 
                            v-model="form.kode_jenis"
                            type="text"
                            required
                            maxlength="10"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm uppercase"
                            placeholder="Contoh: ELK, FRN, ATK"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Kode ini akan digunakan sebagai prefix ID Barang (Contoh: ELK001, ELK002)
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Nama Jenis</label>
                        <input 
                            v-model="form.nama_jenis"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm"
                            placeholder="Contoh: Elektronik, Furniture, Alat Tulis Kantor"
                        />
                    </div>

                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea 
                            v-model="form.keterangan"
                            rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm"
                            placeholder="Keterangan tambahan (opsional)"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-4">
                        <button 
                            type="submit"
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition text-xs md:text-sm"
                        >
                            {{ modalMode === 'create' ? 'Simpan' : 'Update' }}
                        </button>
                        <button 
                            type="button"
                            @click="showModal = false"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded transition text-xs md:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>