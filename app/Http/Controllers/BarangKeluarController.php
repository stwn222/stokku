<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\Barang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BarangKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        // Query dengan relasi dan search
        $barangKeluars = BarangKeluar::with('barang.jenisBarang', 'barang.satuan')
            ->when($search, function ($query, $search) {
                return $query->where('kode_transaksi', 'like', "%{$search}%")
                           ->orWhereHas('barang', function($q) use ($search) {
                               $q->where('nama_barang', 'like', "%{$search}%")
                                 ->orWhere('id_barang', 'like', "%{$search}%");
                           });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Ambil semua barang untuk dropdown
        $barangs = Barang::with(['jenisBarang', 'satuan'])->get();

        return Inertia::render('Transaksi/BarangKeluar', [
            'barangKeluars' => $barangKeluars,
            'barangs' => $barangs,
            'nextKodeTransaksi' => BarangKeluar::generateKodeTransaksi(),
            'tanggalHariIni' => Carbon::now()->format('Y-m-d'),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'is_ppn' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        // Ambil data barang
        $barang = Barang::findOrFail($validated['barang_id']);
        
        // Cek stok
        if ($barang->stok < $validated['jumlah']) {
            return back()->withErrors(['jumlah' => 'Stok tidak mencukupi. Stok tersedia: ' . $barang->stok]);
        }

        // Generate kode transaksi otomatis
        $validated['kode_transaksi'] = BarangKeluar::generateKodeTransaksi();

        // Ambil harga jual dari barang
        $validated['harga_jual'] = $barang->harga_jual;

        // Hitung PPN (11%)
        $validated['ppn'] = 0;
        if (isset($validated['is_ppn']) && $validated['is_ppn']) {
            $validated['ppn'] = $validated['harga_jual'] * $validated['jumlah'] * 0.11;
        }

        // Hitung total
        $subtotal = $validated['harga_jual'] * $validated['jumlah'];
        $validated['total'] = $subtotal + $validated['ppn'];

        // Simpan transaksi
        BarangKeluar::create($validated);

        // Update stok barang (kurangi)
        $barang->stok -= $validated['jumlah'];
        $barang->save();

        return redirect()->route('barang-keluar.index')->with('success', 'Barang keluar berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BarangKeluar $barangKeluar)
    {
        // Validasi input
        $validated = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'is_ppn' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        // Kembalikan stok lama
        $barangLama = Barang::findOrFail($barangKeluar->barang_id);
        $barangLama->stok += $barangKeluar->jumlah;
        $barangLama->save();

        // Ambil data barang baru
        $barangBaru = Barang::findOrFail($validated['barang_id']);
        
        // Cek stok baru
        if ($barangBaru->stok < $validated['jumlah']) {
            // Kembalikan stok lama jika gagal
            $barangLama->stok -= $barangKeluar->jumlah;
            $barangLama->save();
            return back()->withErrors(['jumlah' => 'Stok tidak mencukupi. Stok tersedia: ' . $barangBaru->stok]);
        }

        // Ambil harga jual dari barang
        $validated['harga_jual'] = $barangBaru->harga_jual;

        // Hitung PPN (11%)
        $validated['ppn'] = 0;
        if (isset($validated['is_ppn']) && $validated['is_ppn']) {
            $validated['ppn'] = $validated['harga_jual'] * $validated['jumlah'] * 0.11;
        }

        // Hitung total
        $subtotal = $validated['harga_jual'] * $validated['jumlah'];
        $validated['total'] = $subtotal + $validated['ppn'];

        // Update transaksi
        $barangKeluar->update($validated);

        // Update stok barang baru (kurangi)
        $barangBaru->stok -= $validated['jumlah'];
        $barangBaru->save();

        return redirect()->route('barang-keluar.index')->with('success', 'Barang keluar berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BarangKeluar $barangKeluar)
    {
        // Kembalikan stok
        $barang = Barang::findOrFail($barangKeluar->barang_id);
        $barang->stok += $barangKeluar->jumlah;
        $barang->save();

        // Hapus transaksi
        $barangKeluar->delete();

        return redirect()->route('barang-keluar.index')->with('success', 'Barang keluar berhasil dihapus');
    }
}