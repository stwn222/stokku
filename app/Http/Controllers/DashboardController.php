<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\JenisBarang;
use App\Models\Satuan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalBarang' => Barang::count(),
            'totalBarangMasuk' => BarangMasuk::count(),
            'totalBarangKeluar' => BarangKeluar::count(),
            'totalJenisBarang' => JenisBarang::count(),
            'totalSatuan' => Satuan::count(),
            'totalUser' => User::count(),
        ];

        // Ambil data terbaru dengan created_at untuk timestamp yang akurat
        $recentBarangMasuk = BarangMasuk::with(['barang.satuan', 'user'])
            ->latest('created_at') // Gunakan created_at untuk sorting
            ->take(4)
            ->get()
            ->map(function ($item) {
                // Tambahkan field waktu_transaksi untuk ditampilkan
                $item->waktu_transaksi = $item->created_at;
                return $item;
            });

        $recentBarangKeluar = BarangKeluar::with(['barang.satuan', 'user'])
            ->latest('created_at') // Gunakan created_at untuk sorting
            ->take(4)
            ->get()
            ->map(function ($item) {
                // Tambahkan field waktu_transaksi untuk ditampilkan
                $item->waktu_transaksi = $item->created_at;
                return $item;
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentBarangMasuk' => $recentBarangMasuk,
            'recentBarangKeluar' => $recentBarangKeluar,
        ]);
    }
}