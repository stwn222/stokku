<?php

namespace App\Http\Controllers;

use App\Models\JenisBarang;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JenisBarangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $jenisBarangs = JenisBarang::withCount('barangs')
            ->when($search, function ($query, $search) {
                return $query->where('kode_jenis', 'like', "%{$search}%")
                           ->orWhere('nama_jenis', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('JenisBarang/Index', [
            'jenisBarangs' => $jenisBarangs,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jenis' => 'required|string|max:10|unique:jenis_barangs,kode_jenis',
            'nama_jenis' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Convert kode_jenis to uppercase
        $validated['kode_jenis'] = strtoupper($validated['kode_jenis']);

        JenisBarang::create($validated);

        return redirect()->route('jenis-barang.index')->with('success', 'Jenis barang berhasil ditambahkan');
    }

    public function update(Request $request, JenisBarang $jenisBarang)
    {
        $validated = $request->validate([
            'kode_jenis' => 'required|string|max:10|unique:jenis_barangs,kode_jenis,' . $jenisBarang->id,
            'nama_jenis' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Convert kode_jenis to uppercase
        $validated['kode_jenis'] = strtoupper($validated['kode_jenis']);

        $jenisBarang->update($validated);

        return redirect()->route('jenis-barang.index')->with('success', 'Jenis barang berhasil diupdate');
    }

    public function destroy(JenisBarang $jenisBarang)
    {
        if ($jenisBarang->barangs()->count() > 0) {
            return redirect()->route('jenis-barang.index')->with('error', 'Tidak dapat menghapus jenis barang yang masih memiliki data barang');
        }

        $jenisBarang->delete();

        return redirect()->route('jenis-barang.index')->with('success', 'Jenis barang berhasil dihapus');
    }

    public function getNextId(JenisBarang $jenisBarang)
    {
        return response()->json([
            'next_id' => $jenisBarang->getNextIdBarang()
        ]);
    }
}