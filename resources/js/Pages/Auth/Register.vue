<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import logoBip from '@/assets/images/logo-bip.png';
import logoMju from '@/assets/images/logo-mju.png';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-6xl w-full grid md:grid-cols-2">
            <div class="p-12 flex flex-col justify-center">
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-2 h-6 bg-blue-600 rounded"></div>
                        <span class="text-lg font-semibold text-gray-800">Stokku</span>
                    </div>
                    
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        Daftar Akun
                    </h1>
                    <p class="text-gray-500 text-sm">
                        Buat akun baru untuk mengakses sistem pengelola stock barang
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama -->
                    <div>
                        <input
                            id="name"
                            type="text"
                            placeholder="Nama Lengkap"
                            v-model="form.name"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Username -->
                    <div>
                        <input
                            id="username"
                            type="text"
                            placeholder="Username"
                            v-model="form.username"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autocomplete="username"
                        />
                        <div v-if="form.errors.username" class="mt-2 text-sm text-red-600">
                            {{ form.errors.username }}
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <input
                            id="email"
                            type="email"
                            placeholder="Email"
                            v-model="form.email"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autocomplete="email"
                        />
                        <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <input
                            id="password"
                            type="password"
                            placeholder="Password"
                            v-model="form.password"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <input
                            id="password_confirmation"
                            type="password"
                            placeholder="Konfirmasi Password"
                            v-model="form.password_confirmation"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 transition"
                            required
                            autocomplete="new-password"
                        />
                        <div v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg disabled:opacity-50"
                    >
                        Daftar
                    </button>
                </form>

                <p class="text-center text-sm text-gray-600 mt-8">
                    Sudah Punya Akun? 
                    <Link :href="route('login')" class="text-blue-600 hover:underline font-semibold">
                        Login Disini
                    </Link>
                </p>
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