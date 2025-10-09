<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class LaporanBarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $tanggalAwal = $request->get('tanggal_awal', '');
        $tanggalAkhir = $request->get('tanggal_akhir', '');

        $query = BarangKeluar::with('barang.jenisBarang', 'barang.satuan');

        // Filter berdasarkan tanggal
        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        }

        // Filter berdasarkan pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhere('tujuan', 'like', "%{$search}%")
                  ->orWhereHas('barang', function($subQ) use ($search) {
                      $subQ->where('nama_barang', 'like', "%{$search}%")
                           ->orWhere('id_barang', 'like', "%{$search}%");
                  });
            });
        }

        $barangKeluars = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Laporan/BarangKeluar', [
            'barangKeluars' => $barangKeluars,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
            ],
        ]);
    }
}