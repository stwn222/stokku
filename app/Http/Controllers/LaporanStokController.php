<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? Carbon::now()->format('Y-m-d');
        $filterStok = $request->filter_stok ?? 'all';
        $perPage = $request->per_page ?? 10;

        $query = Barang::with(['jenisBarang', 'satuan']);

        if ($filterStok === 'minimum') {
            $query->whereRaw('stok < stok_minimum');
        }

        $barangs = $query->paginate($perPage)->withQueryString();

        $barangs->getCollection()->transform(function ($barang) use ($tanggalAwal, $tanggalAkhir) {
            $jumlahPembelian = BarangMasuk::where('barang_id', $barang->id)
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                ->sum('jumlah');

            $jumlahPenjualan = InvoiceDetail::where('barang_id', $barang->id)
                ->whereHas('invoice', function($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                })
                ->sum('qty');

            $stokAwal = $barang->stok - $jumlahPembelian + $jumlahPenjualan;

            $barang->stok_awal = max(0, $stokAwal);
            $barang->jumlah_pembelian = $jumlahPembelian;
            $barang->jumlah_penjualan = $jumlahPenjualan;
            $barang->stok_akhir = $barang->stok;

            return $barang;
        });

        return Inertia::render('LaporanStok/Index', [
            'barangs' => $barangs,
            'filters' => [
                'per_page' => $perPage,
                'filter_stok' => $filterStok,
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? Carbon::now()->format('Y-m-d');
        $filterStok = $request->filter_stok ?? 'all';

        $query = Barang::with(['jenisBarang', 'satuan']);

        if ($filterStok === 'minimum') {
            $query->whereRaw('stok < stok_minimum');
        }

        $barangs = $query->get();

        $data = $barangs->map(function ($barang) use ($tanggalAwal, $tanggalAkhir) {
            $jumlahPembelian = BarangMasuk::where('barang_id', $barang->id)
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                ->sum('jumlah');

            $jumlahPenjualan = InvoiceDetail::where('barang_id', $barang->id)
                ->whereHas('invoice', function($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                })
                ->sum('qty');

            $stokAwal = $barang->stok - $jumlahPembelian + $jumlahPenjualan;

            return [
                'Kode Barang' => $barang->id_barang,
                'Nama Barang' => $barang->nama_barang,
                'Jenis Barang' => $barang->jenisBarang->nama_jenis,
                'Stok Awal' => max(0, $stokAwal),
                'Pembelian' => $jumlahPembelian,
                'Penjualan' => $jumlahPenjualan,
                'Stok Akhir' => $barang->stok,
                'Satuan' => $barang->satuan->nama_satuan,
            ];
        });

        return response()->json(['data' => $data]);
    }
}