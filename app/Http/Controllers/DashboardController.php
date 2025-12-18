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
            'totalBarangMasuk' => BarangMasuk::sum('jumlah'),
            'totalBarangKeluar' => BarangKeluar::sum('jumlah'),
            'totalJenisBarang' => JenisBarang::count(),
            'totalSatuan' => Satuan::count(),
            'totalUser' => User::count(),
        ];

        $recentBarangMasuk = BarangMasuk::with(['barang.satuan', 'user'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentBarangKeluar = BarangKeluar::with(['barang.satuan', 'user'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentBarangMasuk' => $recentBarangMasuk,
            'recentBarangKeluar' => $recentBarangKeluar,
        ]);
    }
}