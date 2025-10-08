<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\BarangKeluarController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Barang Routes -
    Route::get('/barang/next-id/{jenisBarangId}', [BarangController::class, 'getNextId'])->name('barang.next-id');
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::put('/barang/{barang}', [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy');

    // Jenis Barang Routes
    Route::get('/jenis-barang', [JenisBarangController::class, 'index'])->name('jenis-barang.index');
    Route::post('/jenis-barang', [JenisBarangController::class, 'store'])->name('jenis-barang.store');
    Route::put('/jenis-barang/{jenisBarang}', [JenisBarangController::class, 'update'])->name('jenis-barang.update');
    Route::delete('/jenis-barang/{jenisBarang}', [JenisBarangController::class, 'destroy'])->name('jenis-barang.destroy');

    // Satuan Routes
    Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index');
    Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store');
    Route::put('/satuan/{satuan}', [SatuanController::class, 'update'])->name('satuan.update');
    Route::delete('/satuan/{satuan}', [SatuanController::class, 'destroy'])->name('satuan.destroy');

    // Barang Masuk Routes
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
    Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
    Route::put('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'update'])->name('barang-masuk.update');
    Route::delete('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');

    // Barang Keluar Routes
    Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');
    Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');
    Route::put('/barang-keluar/{barangKeluar}', [BarangKeluarController::class, 'update'])->name('barang-keluar.update');
    Route::delete('/barang-keluar/{barangKeluar}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy');

    // Laporan Stok Routes
    Route::get('/laporan-stok/export-excel', [LaporanStokController::class, 'exportExcel'])->name('laporan-stok.export-excel');
    Route::get('/laporan-stok', [LaporanStokController::class, 'index'])->name('laporan-stok.index');
});

require __DIR__.'/auth.php';