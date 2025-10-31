<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanStokController; // PERBAIKAN: Tambahkan namespace lengkap
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\ReturnBarangController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\LaporanReturnController; // TAMBAHAN: Controller untuk laporan return
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\LaporanBarangMasukController;
use App\Http\Controllers\LaporanBarangKeluarController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Barang Routes
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

    // Laporan Stok
    Route::get('/laporan-stok', [LaporanStokController::class, 'index'])->name('laporan-stok.index');
    Route::get('/laporan-stok/print', [LaporanStokController::class, 'print'])->name('laporan-stok.print');
    Route::get('/laporan-stok/export-excel', [LaporanStokController::class, 'exportExcel'])->name('laporan-stok.export-excel');

    // Laporan Barang Masuk Routes
    Route::get('/laporan-barang-masuk', [LaporanBarangMasukController::class, 'index'])->name('laporan-barang-masuk.index');
    
    // Laporan Barang Keluar Routes
    Route::get('/laporan-barang-keluar', [LaporanBarangKeluarController::class, 'index'])->name('laporan-barang-keluar.index');

    Route::get('/laporan-return', [LaporanReturnController::class, 'index'])->name('laporan-return.index');
    
    // Invoice Routes
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');
    Route::post('/invoice', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::get('/invoice/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoice.edit');
    Route::put('/invoice/{invoice}', [InvoiceController::class, 'update'])->name('invoice.update');
    Route::delete('/invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::get('/invoice/{id}/print-single', [InvoiceController::class, 'printSingle'])->name('invoice.print-single');
    Route::get('/invoice/{id}/print-single-a5', [InvoiceController::class, 'printSingleA5'])->name('invoice.print-single-a5');

    // Payment Method Routes
    Route::get('/payment-method', [PaymentMethodController::class, 'index'])->name('payment-method.index');
    Route::post('/payment-method', [PaymentMethodController::class, 'store'])->name('payment-method.store');
    Route::put('/payment-method/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-method.update');
    Route::delete('/payment-method/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-method.destroy');
    
    // Return Barang Routes
    Route::post('/return-barang', [ReturnBarangController::class, 'store'])->name('return-barang.store');
    
    // API Invoice Next Number
    Route::get('/api/invoice-next-number/{tipe}', function($tipe) {
        $lastInvoice = \App\Models\Invoice::where('tipe_invoice', $tipe)->latest('id')->first();
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->nomor_invoice);
            $nomorPart = explode('.', $parts[1]);
            $nextNumber = intval($nomorPart[0]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        return response()->json(['next_number' => $nextNumber]);
    })->middleware('auth');

    // User Management Routes
    Route::get('/user-management', [UserManagementController::class, 'index'])->name('user-management.index');
    Route::post('/user-management', [UserManagementController::class, 'store'])->name('user-management.store');
    Route::put('/user-management/{user}', [UserManagementController::class, 'update'])->name('user-management.update');
    Route::delete('/user-management/{user}', [UserManagementController::class, 'destroy'])->name('user-management.destroy');
});

require __DIR__.'/auth.php';