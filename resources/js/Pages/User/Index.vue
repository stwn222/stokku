<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Users, Home, ChevronRight, Plus, Pencil, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
    users: Object,
    filters: Object,
});

const showModal = ref(false);
const isEdit = ref(false);
const search = ref(props.filters?.search || '');

const form = useForm({
    id: null,
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
    hak_akses: '',
});

const openCreateModal = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (user) => {
    isEdit.value = true;
    form.id = user.id;
    form.name = user.name;
    form.username = user.username;
    form.password = '';
    form.password_confirmation = '';
    form.hak_akses = user.hak_akses;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(route('user-management.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post(route('user-management.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Apakah Anda yakin ingin menghapus user "${user.name}"?`)) {
        router.delete(route('user-management.destroy', user.id), {
            preserveScroll: true,
        });
    }
};

const handleSearch = () => {
    router.get(route('user-management.index'),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};

watch(search, (value) => {
    if (value === '') {
        handleSearch();
    }
});
</script>

<template>
    <Head title="Management User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 sm:gap-3 text-white">
                <Users :size="24" class="sm:w-7 sm:h-7" />
                <div>
                    <h2 class="text-lg sm:text-2xl font-bold">Management User</h2>
                    <div class="flex items-center gap-2 text-xs sm:text-sm mt-1 opacity-90">
                        <Home :size="12" class="sm:w-3.5 sm:h-3.5" />
                        <ChevronRight :size="12" class="sm:w-3.5 sm:h-3.5" />
                        <span>User</span>
                        <ChevronRight :size="12" class="sm:w-3.5 sm:h-3.5" />
                        <span>Data</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
            <h3 class="text-base sm:text-xl font-bold text-gray-800 mb-4 sm:mb-6">Data User</h3>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-4">
                <div class="flex items-center gap-2 text-xs sm:text-sm">
                    <label class="text-gray-700">Tampilkan</label>
                    <select class="border border-gray-300 rounded px-2 py-1 text-xs sm:text-sm">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                    <span class="text-gray-700">data</span>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4 w-full sm:w-auto">
                    <div class="flex items-center gap-2">
                        <label class="text-gray-700 text-xs sm:text-sm whitespace-nowrap">Cari:</label>
                        <input
                            v-model="search"
                            @keyup.enter="handleSearch"
                            type="text"
                            class="border border-gray-300 rounded px-3 py-1 flex-1 sm:w-64 text-xs sm:text-sm"
                            placeholder="Cari user..."
                        />
                    </div>

                    <button
                        @click="openCreateModal"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition text-xs sm:text-sm"
                    >
                        <Plus :size="18" />
                        Entri Data
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 min-w-[600px]">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-gray-700 font-semibold text-xs sm:text-sm">No</th>
                            <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-gray-700 font-semibold text-xs sm:text-sm">Nama User</th>
                            <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-gray-700 font-semibold text-xs sm:text-sm">Username</th>
                            <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-gray-700 font-semibold text-xs sm:text-sm">Hak Akses</th>
                            <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-center text-gray-700 font-semibold text-xs sm:text-sm">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(user, index) in users.data" :key="user.id" class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                {{ users.from + index }}
                            </td>
                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ user.name }}</td>
                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ user.username }}</td>
                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3">
                                <span
                                    :class="user.hak_akses === 'Administrator' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                    class="px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-medium inline-block"
                                >
                                    {{ user.hak_akses }}
                                </span>
                            </td>
                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-center">
                                <div class="flex items-center justify-center gap-1 sm:gap-2">
                                    <button
                                        @click="openEditModal(user)"
                                        class="bg-green-500 hover:bg-green-600 text-white p-1.5 sm:p-2 rounded transition"
                                        title="Edit"
                                    >
                                        <Pencil :size="14" class="sm:w-4 sm:h-4" />
                                    </button>
                                    <button
                                        @click="deleteUser(user)"
                                        class="bg-red-500 hover:bg-red-600 text-white p-1.5 sm:p-2 rounded transition"
                                        title="Hapus"
                                    >
                                        <Trash2 :size="14" class="sm:w-4 sm:h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="border border-gray-300 px-4 py-6 sm:py-8 text-center text-gray-500 text-xs sm:text-sm">
                                Tidak ada data user
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-4 sm:mt-6 gap-3 sm:gap-4">
                <div class="text-gray-600 text-xs sm:text-sm text-center sm:text-left">
                    Menampilkan {{ users.from || 0 }} sampai {{ users.to || 0 }} dari {{ users.total }} data
                </div>

                <div class="flex gap-1 sm:gap-2 flex-wrap justify-center">
                    <template v-for="(link, index) in users.links" :key="index">
                        <span
                            v-if="!link.url"
                            class="px-2 sm:px-4 py-1 sm:py-2 border rounded bg-white text-gray-700 border-gray-300 opacity-50 cursor-not-allowed text-xs sm:text-sm"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            :class="[
                                'px-2 sm:px-4 py-1 sm:py-2 border rounded transition text-xs sm:text-sm',
                                link.active
                                    ? 'bg-blue-500 text-white border-blue-500'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center rounded-t-lg sticky top-0">
                        <h3 class="text-base sm:text-xl font-bold">
                            {{ isEdit ? 'Edit Data User' : 'Entri Data User' }}
                        </h3>
                        <button @click="closeModal" class="hover:bg-blue-600 p-1 rounded transition">
                            <X :size="20" class="sm:w-6 sm:h-6" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2 text-xs sm:text-sm">
                                    Nama User <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Masukan Nama User..."
                                    class="w-full border border-gray-300 rounded px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2 text-xs sm:text-sm">
                                    Hak Akses <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.hak_akses"
                                    class="w-full border border-gray-300 rounded px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.hak_akses }"
                                >
                                    <option value="">-- Pilih --</option>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Karyawan">Karyawan</option>
                                </select>
                                <p v-if="form.errors.hak_akses" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.hak_akses }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2 text-xs sm:text-sm">
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    placeholder="Masukan Username..."
                                    class="w-full border border-gray-300 rounded px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.username }"
                                />
                                <p v-if="form.errors.username" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.username }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2 text-xs sm:text-sm">
                                    Password
                                    <span v-if="!isEdit" class="text-red-500">*</span>
                                    <span v-else class="text-gray-500 text-xs">(Kosongkan jika tidak diubah)</span>
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Masukan Password..."
                                    class="w-full border border-gray-300 rounded px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.password }"
                                />
                                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2 text-xs sm:text-sm">
                                    Konfirmasi Password
                                    <span v-if="!isEdit" class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ulangi Password..."
                                    class="w-full border border-gray-300 rounded px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.password_confirmation }"
                                />
                                <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.password_confirmation }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 mt-6 sm:mt-8">
                            <button
                                type="button"
                                @click="closeModal"
                                :disabled="form.processing"
                                class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition disabled:opacity-50 text-xs sm:text-sm"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>