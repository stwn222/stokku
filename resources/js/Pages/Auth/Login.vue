<script setup>
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

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const sessionExpired = ref(false);
let sessionTimer = null;

onMounted(() => {
    sessionTimer = setTimeout(() => {
        sessionExpired.value = true;
    }, 60000); 
});

onUnmounted(() => {
    clearTimeout(sessionTimer);
});

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
            <!-- Form Section -->
            <div class="p-6 sm:p-12 flex flex-col justify-center">
                <!-- Logo Mobile Only -->
                <div class="md:hidden flex justify-center mb-6">
                    <div class="flex gap-4">
                        <div class="bg-white rounded-2xl p-3 shadow-lg">
                            <img :src="logoBip" alt="BiP Logo" class="w-16 h-16 object-contain" />
                        </div>
                        <div class="bg-white rounded-2xl p-3 shadow-lg">
                            <img :src="logoMju" alt="MJU Logo" class="w-16 h-16 object-contain" />
                        </div>
                    </div>
                </div>

                <div class="mb-6 sm:mb-8">
                    <div class="flex items-center gap-2 mb-4 sm:mb-8">
                        <div class="w-2 h-6 bg-blue-600 rounded"></div>
                        <span class="text-lg font-semibold text-gray-800">Stokku</span>
                    </div>
                    
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">
                        Hallo,<br />Selamat Datang
                    </h1>
                    <p class="text-gray-500 text-xs sm:text-sm">
                        Hey, Selamat Datang Di Website Pengelola Stock Barang!
                    </p>
                </div>

                <div v-if="sessionExpired" class="mb-4 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-xs">
                    Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan muat ulang halaman untuk melanjutkan.
                    <button @click="reloadPage" class="font-bold underline ml-2">Muat Ulang</button>
                </div>
                
                <div v-if="status" class="mb-4 text-xs sm:text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-3 sm:space-y-4">
                    <div>
                        <input
                            id="username"
                            type="text"
                            placeholder="Username"
                            v-model="form.username"
                            class="w-full px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition text-sm"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <div v-if="form.errors.username" class="mt-1 text-xs text-red-600">
                            {{ form.errors.username }}
                        </div>
                    </div>

                    <div>
                        <input
                            id="password"
                            type="password"
                            placeholder="Password"
                            v-model="form.password"
                            class="w-full px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition text-sm"
                            required
                            autocomplete="current-password"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
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
                        class="w-full bg-blue-600 text-white py-2.5 sm:py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg disabled:opacity-50 text-sm sm:text-base"
                    >
                        Login
                    </button>
                </form>

            </div>

            <!-- Image Section - Desktop Only -->
            <div class="hidden md:flex bg-gradient-to-br from-blue-500 to-blue-600 p-12 flex-col items-center justify-center gap-8">
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