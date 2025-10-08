<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Pencil, Trash2, Plus } from 'lucide-vue-next';

const props = defineProps({
    satuans: Object,
    filters: Object,
});

const perPage = ref(props.filters.per_page || 10);
const search = ref(props.filters.search || '');
const showModal = ref(false);
const modalMode = ref('create');
const form = ref({
    id: null,
    nama_satuan: '',
    keterangan: '',
});

watch([perPage, search], () => {
    router.get(route('satuan.index'), {
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
        nama_satuan: '',
        keterangan: '',
    };
    showModal.value = true;
};

const openEditModal = (satuan) => {
    modalMode.value = 'edit';
    form.value = {
        id: satuan.id,
        nama_satuan: satuan.nama_satuan,
        keterangan: satuan.keterangan || '',
    };
    showModal.value = true;
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        router.post(route('satuan.store'), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        router.put(route('satuan.update', form.value.id), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deleteSatuan = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus satuan ini?')) {
        router.delete(route('satuan.destroy', id));
    }
};
</script>

<template>
    <Head title="Satuan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span>Barang</span>
                <ChevronRight :size="16" />
                <span class="font-semibold">Satuan</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <!-- Header Card -->
            <div class="border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Satuan</h2>
                    <button 
                        @click="openCreateModal"
                        class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition"
                    >
                        <Plus :size="18" />
                        Tambah Satuan
                    </button>
                </div>
            </div>

            <!-- Filters -->
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
                            placeholder="Cari satuan..."
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Keterangan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Digunakan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(satuan, index) in satuans.data" 
                            :key="satuan.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-sm text-gray-700 border">
                                {{ satuans.from + index }}
                            </td>
                            <td class="px-4 py-3 text-sm border">
                                <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 rounded font-semibold">
                                    {{ satuan.nama_satuan }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 border">{{ satuan.keterangan || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center border">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 text-green-800 rounded-full font-semibold">
                                    {{ satuan.barangs_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm border">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(satuan)"
                                        class="p-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full transition"
                                        title="Edit"
                                    >
                                        <Pencil :size="14" />
                                    </button>
                                    <button 
                                        @click="deleteSatuan(satuan.id)"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition"
                                        title="Hapus"
                                        :disabled="satuan.barangs_count > 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': satuan.barangs_count > 0 }"
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="satuans.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 border">
                                Tidak ada data satuan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ satuans.from }} sampai {{ satuans.to }} dari {{ satuans.total }} data
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        @click="router.get(satuans.prev_page_url)"
                        :disabled="!satuans.prev_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &lt;
                    </button>

                    <template v-for="link in satuans.links.slice(1, -1)" :key="link.label">
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
                        @click="router.get(satuans.next_page_url)"
                        :disabled="!satuans.next_page_url"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div 
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    {{ modalMode === 'create' ? 'Tambah Satuan' : 'Edit Satuan' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Satuan
                            <span class="text-xs text-gray-500">(Akan otomatis uppercase)</span>
                        </label>
                        <input 
                            v-model="form.nama_satuan"
                            type="text"
                            required
                            maxlength="50"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm uppercase"
                            placeholder="Contoh: PCS, UNIT, KG, LITER, BOX"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea 
                            v-model="form.keterangan"
                            rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            placeholder="Keterangan tambahan (opsional)"
                        ></textarea>
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