<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Barang;
use App\Models\Invoice;

Route::middleware(['web', 'auth'])->group(function () {
    
    // Endpoint untuk mengambil daftar barang
    Route::get('/barang', function () {
        try {
            $barang = Barang::select('id', 'id_barang', 'nama_barang', 'harga_jual', 'stok')
                ->orderBy('nama_barang', 'asc')
                ->get();
            
            return response()->json($barang);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch barang',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // Endpoint untuk cek stok barang
    Route::get('/barang/{id}/stok', function ($id) {
        try {
            $barang = Barang::findOrFail($id);
            
            return response()->json([
                'id' => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'stok' => $barang->stok
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Barang not found',
                'message' => $e->getMessage()
            ], 404);
        }
    });

    // Endpoint untuk mendapatkan nomor invoice berikutnya
    Route::get('/invoice-next-number/{tipe}', function ($tipe) {
        try {
            $lastInvoice = Invoice::where('tipe_invoice', $tipe)
                ->latest('id')
                ->first();
            
            $nextNumber = 1;
            
            if ($lastInvoice) {
                $parts = explode('/', $lastInvoice->nomor_invoice);
                $nomorPart = explode('.', $parts[1]);
                $nextNumber = intval($nomorPart[0]) + 1;
            }
            
            return response()->json([
                'next_number' => $nextNumber
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch next number',
                'message' => $e->getMessage()
            ], 500);
        }
    });
});