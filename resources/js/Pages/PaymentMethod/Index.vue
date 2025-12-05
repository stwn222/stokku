<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Home, ChevronRight, Pencil, Trash2, Plus, CreditCard } from 'lucide-vue-next';

const props = defineProps({
    paymentMethods: Object,
    filters: Object,
});

const perPage = ref(props.filters.per_page || 10);
const search = ref(props.filters.search || '');
const showModal = ref(false);
const modalMode = ref('create');
const form = ref({
    id: null,
    nama_metode: '',
    atas_nama: '',
    nomor_rekening: '',
    keterangan: '',
    is_active: true,
});

watch([perPage, search], () => {
    router.get(route('payment-method.index'), {
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
        nama_metode: '',
        atas_nama: '',
        nomor_rekening: '',
        keterangan: '',
        is_active: true,
    };
    showModal.value = true;
};

const openEditModal = (paymentMethod) => {
    modalMode.value = 'edit';
    form.value = {
        id: paymentMethod.id,
        nama_metode: paymentMethod.nama_metode,
        atas_nama: paymentMethod.atas_nama || '',
        nomor_rekening: paymentMethod.nomor_rekening || '',
        keterangan: paymentMethod.keterangan || '',
        is_active: paymentMethod.is_active,
    };
    showModal.value = true;
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        router.post(route('payment-method.store'), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        router.put(route('payment-method.update', form.value.id), form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deletePaymentMethod = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('payment-method.destroy', id));
    }
};
</script>

<template>
    <Head title="Metode Pembayaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-white">
                <Home :size="20" />
                <ChevronRight :size="16" />
                <span class="text-sm md:text-base">Master</span>
                <ChevronRight :size="16" />
                <span class="font-semibold text-sm md:text-base">Metode Pembayaran</span>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-4 md:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <CreditCard :size="18" class="text-blue-600 md:w-5 md:h-5" />
                        </div>
                        <h2 class="text-lg md:text-xl font-bold text-gray-800">Metode Pembayaran</h2>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-3 md:px-4 py-2 rounded-lg transition text-sm md:text-base w-full sm:w-auto justify-center"
                    >
                        <Plus :size="18" />
                        Tambah Metode
                    </button>
                </div>
            </div>

            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <label class="text-xs md:text-sm text-gray-700 whitespace-nowrap">Tampilkan</label>
                        <select 
                            v-model="perPage"
                            class="border border-gray-300 rounded px-2 py-1 text-xs md:text-sm focus:ring-blue-500 focus:border-blue-500"
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
                            class="border border-gray-300 rounded px-3 py-1 text-xs md:text-sm w-full md:w-64 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Cari metode pembayaran..."
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">No</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama Metode</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Atas Nama</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nomor Rekening</th>
                            <th class="px-2 md:px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Keterangan</th>
                            <th class="px-2 md:px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Status</th>
                            <th class="px-2 md:px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(paymentMethod, index) in paymentMethods.data" 
                            :key="paymentMethod.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">
                                {{ paymentMethods.from + index }}
                            </td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">{{ paymentMethod.nama_metode }}</td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">{{ paymentMethod.atas_nama || '-' }}</td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">{{ paymentMethod.nomor_rekening || '-' }}</td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm text-gray-700 border">{{ paymentMethod.keterangan || '-' }}</td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm border text-center">
                                <span 
                                    :class="[
                                        'px-2 md:px-3 py-1 rounded-full text-xs font-semibold',
                                        paymentMethod.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                    ]"
                                >
                                    {{ paymentMethod.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-2 md:px-4 py-3 text-xs md:text-sm border">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(paymentMethod)"
                                        class="p-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full transition"
                                        title="Edit"
                                    >
                                        <Pencil :size="14" />
                                    </button>
                                    <button 
                                        @click="deletePaymentMethod(paymentMethod.id)"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition"
                                        title="Hapus"
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paymentMethods.data.length === 0">
                            <td colspan="7" class="px-2 md:px-4 py-8 text-center text-gray-500 border text-xs md:text-sm">
                                Tidak ada data metode pembayaran
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div 
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-4 md:px-6 py-4">
                    <h3 class="text-base md:text-lg font-bold text-gray-800">
                        {{ modalMode === 'create' ? 'Tambah Metode Pembayaran' : 'Edit Metode Pembayaran' }}
                    </h3>
                </div>

                <form @submit.prevent="submitForm" class="p-4 md:p-6 space-y-4">
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                            Nama Metode <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.nama_metode"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: Cash, Transfer BCA, Transfer Mandiri"
                        />
                    </div>

                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                            Atas Nama
                        </label>
                        <input 
                            v-model="form.atas_nama"
                            type="text"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama pemilik rekening (opsional)"
                        />
                    </div>

                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                            Nomor Rekening
                        </label>
                        <input 
                            v-model="form.nomor_rekening"
                            type="text"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nomor rekening (opsional)"
                        />
                    </div>

                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea 
                            v-model="form.keterangan"
                            rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-xs md:text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Keterangan tambahan (opsional)"
                        ></textarea>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input 
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-xs md:text-sm font-medium text-gray-700">Aktif</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-2 pt-4 border-t">
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