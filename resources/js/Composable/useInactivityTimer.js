// resources/js/Composables/useInactivityTimer.js

import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export function useInactivityTimer() {
    // --- KONFIGURASI ---
    const TIMEOUT_MINUTES = 15; // Durasi Inactivity sebelum peringatan muncul (dalam menit)
    const WARNING_SECONDS = 60; // Durasi hitung mundur pada peringatan (dalam detik)
    // -------------------

    const IDLE_TIMEOUT = TIMEOUT_MINUTES * 60 * 1000; // Konversi menit ke milidetik
    
    const isIdle = ref(false);
    const warningCountdown = ref(WARNING_SECONDS);
    
    let inactivityTimer = null;
    let warningTimer = null;

    const events = ['mousemove', 'keydown', 'click', 'scroll'];

    const resetTimer = () => {
        // Hapus timer lama
        clearTimeout(inactivityTimer);
        clearInterval(warningTimer);

        // Reset state
        isIdle.value = false;
        warningCountdown.value = WARNING_SECONDS;
        
        // Mulai timer baru
        inactivityTimer = setTimeout(showWarning, IDLE_TIMEOUT);
    };

    const showWarning = () => {
        isIdle.value = true;
        
        // Mulai hitung mundur peringatan
        warningTimer = setInterval(() => {
            warningCountdown.value--;
            if (warningCountdown.value === 0) {
                // Jika waktu habis, logout
                logout();
            }
        }, 1000);
    };
    
    const stay = () => {
        // Pengguna memilih untuk tetap login
        resetTimer();
    };

    const logout = () => {
        clearInterval(warningTimer);
        // Gunakan router Inertia untuk logout
        router.post(route('logout'));
    };

    onMounted(() => {
        // Tambahkan event listener saat komponen dimuat
        events.forEach(event => window.addEventListener(event, resetTimer));
        resetTimer(); // Mulai timer saat pertama kali halaman dimuat
    });

    onUnmounted(() => {
        // Hapus semua timer dan event listener saat komponen dihancurkan
        clearTimeout(inactivityTimer);
        clearInterval(warningTimer);
        events.forEach(event => window.removeEventListener(event, resetTimer));
    });

    return { isIdle, warningCountdown, stay };
}