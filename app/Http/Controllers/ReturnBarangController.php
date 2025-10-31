<?php

namespace App\Http\Controllers;

use App\Models\ReturnBarang;
use App\Models\Invoice;
use App\Models\Barang;
use App\Models\InvoiceDetail;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturnBarangController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'alasan' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Cek invoice detail
            $invoiceDetail = InvoiceDetail::where('invoice_id', $validated['invoice_id'])
                ->where('barang_id', $validated['barang_id'])
                ->first();

            if (!$invoiceDetail) {
                throw new \Exception("Barang tidak ditemukan dalam invoice ini");
            }

            // Hitung total yang sudah di-return
            $totalReturned = ReturnBarang::where('invoice_id', $validated['invoice_id'])
                ->where('barang_id', $validated['barang_id'])
                ->sum('jumlah');

            $availableToReturn = $invoiceDetail->qty - $totalReturned;

            if ($validated['jumlah'] > $availableToReturn) {
                throw new \Exception("Jumlah return melebihi jumlah yang tersedia. Maksimal yang bisa di-return: {$availableToReturn}");
            }

            // Buat record return
            ReturnBarang::create([
                'invoice_id' => $validated['invoice_id'],
                'barang_id' => $validated['barang_id'],
                'jumlah' => $validated['jumlah'],
                'alasan' => $validated['alasan'],
                'tanggal_return' => Carbon::now(),
                'user_id' => Auth::id(),
            ]);

            // Update stok barang (tambah stok)
            $barang = Barang::findOrFail($validated['barang_id']);
            $barang->increment('stok', $validated['jumlah']);

            // Kurangi BarangKeluar atau buat record balik
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            
            // Cari barang keluar yang terkait dengan invoice ini
            $barangKeluar = BarangKeluar::where('barang_id', $validated['barang_id'])
                ->where('keterangan', 'like', "%Invoice: {$invoice->nomor_invoice}%")
                ->first();

            if ($barangKeluar) {
                // Update jumlah barang keluar
                $newJumlah = $barangKeluar->jumlah - $validated['jumlah'];
                
                if ($newJumlah <= 0) {
                    // Hapus record jika semua barang di-return
                    $barangKeluar->delete();
                } else {
                    // Update jumlah
                    $barangKeluar->update([
                        'jumlah' => $newJumlah,
                        'total' => $newJumlah * $barangKeluar->harga_jual,
                        'ppn' => $barangKeluar->is_ppn ? ($newJumlah * $barangKeluar->harga_jual * 0.11) : 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('success', 'Return barang berhasil diproses');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}