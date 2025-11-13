<script setup>
// --- [MODIFIKASI] --- Impor 'ref', 'onMounted', dan 'onUnmounted' dari Vue
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue'; 
import logoBip from '@/assets/images/logo-bip.png';
import logoMju from '@/assets/images/logo-mju.png';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// --- [MODIFIKASI] --- Ubah dari email ke username
const form = useForm({
    username: '',
    password: '',
    remember: false,
});

// --- [DITAMBAHKAN] --- State untuk notifikasi sesi berakhir
const sessionExpired = ref(false);
let sessionTimer = null;

// --- [DITAMBAHKAN] --- Fungsi untuk memulai timer saat komponen dimuat
onMounted(() => {
    // Atur timer untuk 1 menit (60000 milidetik)
    sessionTimer = setTimeout(() => {
        sessionExpired.value = true;
    }, 60000); 
});

// --- [DITAMBAHKAN] --- Hapus timer saat komponen dihancurkan (misal, pindah halaman)
onUnmounted(() => {
    clearTimeout(sessionTimer);
});

// --- [DITAMBAHKAN] --- Fungsi untuk memuat ulang halaman
const reloadPage = () => {
    window.location.reload();
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-6xl w-full grid md:grid-cols-2">
            <div class="p-12 flex flex-col justify-center">
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-2 h-6 bg-blue-600 rounded"></div>
                        <span class="text-lg font-semibold text-gray-800">Stokku</span>
                    </div>
                    
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        Hallo,<br />Selamat Datang
                    </h1>
                    <p class="text-gray-500 text-sm">
                        Hey, Selamat Datang Di Website Pengelola Stock Barang!
                    </p>
                </div>

                <div v-if="sessionExpired" class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-sm">
                    Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan muat ulang halaman untuk melanjutkan.
                    <button @click="reloadPage" class="font-bold underline ml-2">Muat Ulang</button>
                </div>
                
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Username Field (Ganti dari Email) -->
                    <div>
                        <input
                            id="username"
                            type="text"
                            placeholder="Username"
                            v-model="form.username"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <div v-if="form.errors.username" class="mt-2 text-sm text-red-600">
                            {{ form.errors.username }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <input
                            id="password"
                            type="password"
                            placeholder="Password"
                            v-model="form.password"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autocomplete="current-password"
                        />
                        <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="text-gray-600">Ingat Saya</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-blue-600 hover:underline"
                        >
                            Lupa Password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || sessionExpired"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg disabled:opacity-50"
                    >
                        Login
                    </button>
                </form>

            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-12 flex flex-col items-center justify-center gap-8">
                <div class="bg-white rounded-3xl p-8 shadow-xl w-80 h-64 flex items-center justify-center">
                    <img :src="logoBip" alt="BiP Logo" class="w-56 h-auto object-contain" />
                </div>

                <div class="bg-white rounded-3xl p-8 shadow-xl w-80 h-64 flex items-center justify-center">
                    <img :src="logoMju" alt="MJU Logo" class="w-56 h-auto object-contain" />
                </div>
            </div>
        </div>
    </div>
</template>