<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { PlusIcon, MinusIcon, Trash2Icon } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    barangList: Array,
    nextMJUNumber: Number,
    nextBIPNumber: Number,
});

// Multi-step form state
const currentStep = ref(1);

const form = useForm({
    tipe_invoice: 'MJU',
    nama_client: '',
    nomor_client: '',
    tanggal: new Date().toISOString().split('T')[0],
    alamat_client: '',
    diskon: 0,
    ppn: false,
    details: [],
});

const selectedBarang = ref('');
const selectedQty = ref(1);

// Computed properties untuk kalkulasi
const currentInvoiceNumber = computed(() => {
    const number = form.tipe_invoice === 'MJU' ? props.nextMJUNumber : props.nextBIPNumber;
    const date = new Date(form.tanggal);
    const formattedDate = `${date.getDate().toString().padStart(2, '0')}.${(date.getMonth() + 1).toString().padStart(2, '0')}.${date.getFullYear()}`;
    return `${form.tipe_invoice}/${number}.${formattedDate}`;
});

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

// Methods
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

const removeBarang = (index) => {
    form.details.splice(index, 1);
};

const updateQty = (index, newQty) => {
    if (newQty > 0) {
        form.details[index].qty = newQty;
    }
};

const goToStep = (step) => {
    currentStep.value = step;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (!form.nama_client || !form.nomor_client || !form.alamat_client) {
            alert('Harap lengkapi data client terlebih dahulu');
            return;
        }
    }

    if (currentStep.value === 2) {
        if (form.details.length === 0) {
            alert('Tambahkan minimal satu barang');
            return;
        }
    }

    if (currentStep.value < 3) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    if (form.details.length === 0) {
        alert('Tambahkan minimal satu barang');
        return;
    }

    form.post(route('invoice.store'), {
        onSuccess: () => {
            alert('Invoice berhasil dibuat');
        },
    });
};

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

        <div class="flex items-center gap-2 text-white mb-6">
            <span class="hover:text-blue-100 cursor-pointer" @click="() => $inertia.visit(route('dashboard'))">Home</span>
            <span>></span>
            <span class="hover:text-blue-100 cursor-pointer" @click="() => $inertia.visit(route('invoice.index'))">Invoice</span>
            <span>></span>
            <span>Entri Invoice</span>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div v-show="currentStep === 1" class="bg-white rounded-lg p-8 shadow-md">
                <h2 class="text-xl font-semibold mb-6 text-gray-800">Entri Invoice</h2>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Nama Client :</label>
                        <input
                            v-model="form.nama_client"
                            type="text"
                            placeholder="Masukan Nama Client..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Nomor Client :</label>
                        <input
                            v-model="form.nomor_client"
                            type="text"
                            placeholder="Masukan Nomor Client..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Tanggal :</label>
                        <input
                            v-model="form.tanggal"
                            type="date"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Nomor Invoice :</label>
                        <input
                            :value="currentInvoiceNumber"
                            type="text"
                            readonly
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-900 mb-2">Alamat Client :</label>
                    <input
                        v-model="form.alamat_client"
                        type="text"
                        placeholder="Masukan Alamat Client..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Diskon (%) :</label>
                        <div class="flex items-center">
                            <input
                                v-model.number="form.diskon"
                                type="number"
                                placeholder="(Opsional)"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">PPN :</label>
                        <select
                            v-model="form.ppn"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white"
                        >
                            <option :value="false">Tidak</option>
                            <option :value="true">Ya (11%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Tipe Invoice</label>
                        <select
                            v-model="form.tipe_invoice"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white"
                        >
                            <option value="MJU">MJU</option>
                            <option value="BIP">BIP</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button
                        type="button"
                        @click="nextStep"
                        class="px-8 py-2.5 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                    >
                        Selanjutnya
                    </button>
                    <button
                        type="button"
                        @click="handleCancel"
                        class="px-8 py-2.5 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium"
                    >
                        Batal
                    </button>
                </div>
            </div>

            <div v-show="currentStep === 2" class="bg-white rounded-lg p-8 shadow-md">
                <h2 class="text-xl font-semibold mb-6 text-gray-800">Entri Invoice</h2>

                <div class="space-y-4 mb-6">
                    <div v-for="(detail, index) in form.details" :key="index" class="grid grid-cols-2 gap-6 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Nama Barang :</label>
                            <input
                                :value="detail.nama_barang"
                                type="text"
                                disabled
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50"
                            />
                        </div>
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Qty :</label>
                                <input
                                    :value="detail.qty"
                                    type="text"
                                    disabled
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50"
                                />
                            </div>
                            <button
                                type="button"
                                @click="updateQty(index, detail.qty + 1)"
                                class="w-10 h-10 flex items-center justify-center border-2 border-gray-900 rounded-full hover:bg-gray-100 transition"
                            >
                                <PlusIcon :size="20" />
                            </button>
                            <button
                                type="button"
                                @click="updateQty(index, detail.qty - 1)"
                                class="w-10 h-10 flex items-center justify-center border-2 border-gray-900 rounded-full hover:bg-gray-100 transition"
                            >
                                <MinusIcon :size="20" />
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 items-end pt-4 border-t">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Nama Barang :</label>
                            <select
                                v-model="selectedBarang"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white"
                            >
                                <option value="">Masukan Nama Barang...</option>
                                <option v-for="barang in barangList" :key="barang.id" :value="barang.id">
                                    {{ barang.nama_barang }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Qty :</label>
                                <input
                                    v-model.number="selectedQty"
                                    type="number"
                                    min="1"
                                    placeholder="-- Pilih --"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                            <button
                                type="button"
                                @click="addBarang"
                                class="w-10 h-10 flex items-center justify-center border-2 border-gray-900 rounded-full hover:bg-gray-100 transition"
                            >
                                <PlusIcon :size="20" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button
                        type="button"
                        @click="nextStep"
                        class="px-8 py-2.5 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                    >
                        Simpan
                    </button>
                    <button
                        type="button"
                        @click="prevStep"
                        class="px-8 py-2.5 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium"
                    >
                        Batal
                    </button>
                </div>
            </div>

            <div v-show="currentStep === 3" class="bg-white rounded-lg p-8 shadow-md">
                <h2 class="text-xl font-semibold mb-6 text-gray-800">Summary Invoice</h2>

                <div class="space-y-3 mb-6 pb-6 border-b">
                    <div class="flex justify-between text-gray-700">
                        <span class="font-medium">Subtotal:</span>
                        <span class="font-semibold">Rp {{ subtotal.toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span class="font-medium">Diskon ({{ form.diskon }}%):</span>
                        <span class="font-semibold text-red-600">- Rp {{ diskonAmount.toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span class="font-medium">Setelah Diskon:</span>
                        <span class="font-semibold">Rp {{ afterDiskon.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="form.ppn" class="flex justify-between text-gray-700">
                        <span class="font-medium">PPN (11%):</span>
                        <span class="font-semibold text-orange-600">+ Rp {{ ppnAmount.toLocaleString('id-ID') }}</span>
                    </div>
                </div>

                <div class="flex justify-between text-xl font-bold text-gray-900 mb-8">
                    <span>TOTAL:</span>
                    <span class="text-blue-600">Rp {{ total.toLocaleString('id-ID') }}</span>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        class="px-8 py-2.5 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition font-medium"
                    >
                        Simpan
                    </button>
                    <button
                        type="button"
                        @click="prevStep"
                        class="px-8 py-2.5 bg-gray-400 text-white rounded-full hover:bg-gray-500 transition font-medium"
                    >
                        Kembali
                    </button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>