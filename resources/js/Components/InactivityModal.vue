<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    countdown: {
        type: Number,
        required: true,
    }
});

const emit = defineEmits(['stay']);
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center transition-opacity">
        <div class="bg-white rounded-lg shadow-xl p-6 md:p-8 w-11/12 max-w-md text-center">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4">
                Apakah Anda Masih di Sini?
            </h2>
            <p class="text-gray-600 mb-6">
                Sesi Anda akan berakhir karena tidak ada aktivitas. Anda akan otomatis logout dalam 
                <span class="font-bold text-red-500">{{ countdown }}</span> 
                detik.
            </p>
            <button
                @click="emit('stay')"
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Ya, Saya Masih di Sini
            </button>
        </div>
    </div>
</template>