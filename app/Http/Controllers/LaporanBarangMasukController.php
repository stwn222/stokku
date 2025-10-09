<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class LaporanBarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $tanggalAwal = $request->get('tanggal_awal', '');
        $tanggalAkhir = $request->get('tanggal_akhir', '');

        $query = BarangMasuk::with('barang.jenisBarang', 'barang.satuan');

        // Filter berdasarkan tanggal
        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        }

        // Filter berdasarkan pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhereHas('barang', function($subQ) use ($search) {
                      $subQ->where('nama_barang', 'like', "%{$search}%")
                           ->orWhere('id_barang', 'like', "%{$search}%");
                  });
            });
        }

        $barangMasuks = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Laporan/BarangMasuk', [
            'barangMasuks' => $barangMasuks,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
            ],
        ]);
    }
}