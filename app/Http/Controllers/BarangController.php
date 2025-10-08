<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\JenisBarang;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $barangs = Barang::with(['jenisBarang', 'satuan']) // PERBAIKAN: Tambahkan eager loading
            ->when($search, function ($query, $search) {
                return $query->where('id_barang', 'like', "%{$search}%")
                           ->orWhere('nama_barang', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $jenisBarangs = JenisBarang::all();
        $satuans = Satuan::all();

        return Inertia::render('Barang/Index', [
            'barangs' => $barangs,
            'jenisBarangs' => $jenisBarangs,
            'satuans' => $satuans,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_barang_id' => 'required|exists:jenis_barangs,id',
            'nama_barang' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'stok_maksimum' => 'nullable|integer|min:0',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        // Set default values for stok_minimum and stok_maksimum if not provided
        $validated['stok_minimum'] = $validated['stok_minimum'] ?? 0;
        $validated['stok_maksimum'] = $validated['stok_maksimum'] ?? 0;

        // Generate ID Barang otomatis
        $jenisBarang = JenisBarang::findOrFail($validated['jenis_barang_id']);
        $validated['id_barang'] = $jenisBarang->getNextIdBarang();

        Barang::create($validated);

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil ditambahkan');
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'jenis_barang_id' => 'required|exists:jenis_barangs,id',
            'nama_barang' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'stok_maksimum' => 'nullable|integer|min:0',
            'satuan_id' => 'required|exists:satuans,id',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        // Set default values for stok_minimum and stok_maksimum if not provided
        $validated['stok_minimum'] = $validated['stok_minimum'] ?? 0;
        $validated['stok_maksimum'] = $validated['stok_maksimum'] ?? 0;

        // Update ID Barang jika jenis barang berubah
        if ($barang->jenis_barang_id != $validated['jenis_barang_id']) {
            $jenisBarang = JenisBarang::findOrFail($validated['jenis_barang_id']);
            $validated['id_barang'] = $jenisBarang->getNextIdBarang();
        }

        $barang->update($validated);

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diupdate');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus');
    }

    public function getNextId($jenisBarangId)
    {
        $jenisBarang = JenisBarang::findOrFail($jenisBarangId);
        return response()->json([
            'next_id' => $jenisBarang->getNextIdBarang()
        ]);
    }
}