<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $filterStok = $request->get('filter_stok', 'all'); // all, minimum, maximum

        $query = Barang::with(['jenisBarang', 'satuan']);

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id_barang', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        // Apply stock filter
        if ($filterStok === 'minimum') {
            $query->whereColumn('stok', '<=', 'stok_minimum');
        }
        // Jika maximum, tampilkan semua data (tidak ada filter)
        // elseif ($filterStok === 'maximum') - tidak perlu kondisi ini

        $barangs = $query->orderBy('id_barang', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('LaporanStok/Index', [
            'barangs' => $barangs,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'filter_stok' => $filterStok,
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $search = $request->get('search', '');
        $filterStok = $request->get('filter_stok', 'all');

        $query = Barang::with(['jenisBarang', 'satuan']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id_barang', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        if ($filterStok === 'minimum') {
            $query->whereColumn('stok', '<=', 'stok_minimum');
        }
        // Jika maximum, tampilkan semua data (tidak ada filter)

        $barangs = $query->orderBy('id_barang', 'asc')->get();

        return response()->json([
            'data' => $barangs->map(function($barang) {
                return [
                    'Kode Barang' => $barang->id_barang,
                    'Nama Barang' => $barang->nama_barang,
                    'Jenis Barang' => $barang->jenisBarang->nama_jenis ?? '',
                    'Stok' => $barang->stok,
                    'Satuan' => $barang->satuan->nama_satuan ?? '',
                    'Stok Minimum' => $barang->stok_minimum ?? 0,
                    'Stok Maksimum' => $barang->stok_maksimum ?? 0,
                ];
            })
        ]);
    }
}