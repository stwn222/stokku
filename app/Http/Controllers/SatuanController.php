<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $satuans = Satuan::withCount('barangs')
            ->when($search, function ($query, $search) {
                return $query->where('nama_satuan', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Satuan/Index', [
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
            'nama_satuan' => 'required|string|max:50|unique:satuans,nama_satuan',
            'keterangan' => 'nullable|string',
        ]);

        // Convert nama_satuan to uppercase
        $validated['nama_satuan'] = strtoupper($validated['nama_satuan']);

        Satuan::create($validated);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan');
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:50|unique:satuans,nama_satuan,' . $satuan->id,
            'keterangan' => 'nullable|string',
        ]);

        // Convert nama_satuan to uppercase
        $validated['nama_satuan'] = strtoupper($validated['nama_satuan']);

        $satuan->update($validated);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diupdate');
    }

    public function destroy(Satuan $satuan)
    {
        if ($satuan->barangs()->count() > 0) {
            return redirect()->route('satuan.index')->with('error', 'Tidak dapat menghapus satuan yang masih digunakan');
        }

        $satuan->delete();

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus');
    }
}