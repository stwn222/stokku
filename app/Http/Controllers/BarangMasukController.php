<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\BarangMasuk;
use App\Models\Barang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $barangMasuks = BarangMasuk::with('barang.jenisBarang', 'barang.satuan')
            ->when($search, function ($query, $search) {
                return $query->where('kode_transaksi', 'like', "%{$search}%")
                           ->orWhere('vendor', 'like', "%{$search}%")
                           ->orWhereHas('barang', function($q) use ($search) {
                               $q->where('nama_barang', 'like', "%{$search}%")
                                 ->orWhere('id_barang', 'like', "%{$search}%");
                           });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $barangs = Barang::with(['jenisBarang', 'satuan'])->get();

        return Inertia::render('Transaksi/BarangMasuk', [
            'barangMasuks' => $barangMasuks,
            'barangs' => $barangs,
            'nextKodeTransaksi' => BarangMasuk::generateKodeTransaksi(),
            'tanggalHariIni' => Carbon::now()->format('Y-m-d'),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'harga_beli' => 'required|numeric|min:0',
            'is_ppn' => 'boolean',
            'vendor' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Generate kode transaksi
        $validated['kode_transaksi'] = BarangMasuk::generateKodeTransaksi();

        // Hitung PPN jika ada
        $validated['ppn'] = 0;
        if ($validated['is_ppn']) {
            $validated['ppn'] = $validated['harga_beli'] * $validated['jumlah'] * 0.11;
        }

        // Hitung total
        $subtotal = $validated['harga_beli'] * $validated['jumlah'];
        $validated['total'] = $subtotal + $validated['ppn'];

        // Tambahkan user_id dari user yang sedang login
         $validated['user_id'] = Auth::id();

        // Simpan transaksi
        BarangMasuk::create($validated);

        // Update stok barang
        $barang = Barang::findOrFail($validated['barang_id']);
        $barang->stok += $validated['jumlah'];
        $barang->save();

        return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk berhasil ditambahkan');
    }

    public function update(Request $request, BarangMasuk $barangMasuk)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'harga_beli' => 'required|numeric|min:0',
            'is_ppn' => 'boolean',
            'vendor' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Kembalikan stok lama
        $barang = Barang::findOrFail($barangMasuk->barang_id);
        $barang->stok -= $barangMasuk->jumlah;
        $barang->save();

        // Hitung PPN jika ada
        $validated['ppn'] = 0;
        if ($validated['is_ppn']) {
            $validated['ppn'] = $validated['harga_beli'] * $validated['jumlah'] * 0.11;
        }

        // Hitung total
        $subtotal = $validated['harga_beli'] * $validated['jumlah'];
        $validated['total'] = $subtotal + $validated['ppn'];

        // Update transaksi
        $barangMasuk->update($validated);

        // Update stok baru
        $barangBaru = Barang::findOrFail($validated['barang_id']);
        $barangBaru->stok += $validated['jumlah'];
        $barangBaru->save();

        return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk berhasil diupdate');
    }

    public function destroy(BarangMasuk $barangMasuk)
    {
        // Kembalikan stok
        $barang = Barang::findOrFail($barangMasuk->barang_id);
        $barang->stok -= $barangMasuk->jumlah;
        $barang->save();

        $barangMasuk->delete();

        return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk berhasil dihapus');
    }
}