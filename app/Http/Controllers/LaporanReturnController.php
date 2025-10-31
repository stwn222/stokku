<?php

namespace App\Http\Controllers;

use App\Models\ReturnBarang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class LaporanReturnController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $query = ReturnBarang::with(['invoice', 'barang.jenisBarang', 'user'])
            ->whereBetween('tanggal_return', [$startDate, $endDate])
            ->orderBy('tanggal_return', 'desc');

        $returns = $query->paginate(10)->withQueryString();

        return Inertia::render('LaporanReturn/Index', [
            'returns' => $returns,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function print(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $returns = ReturnBarang::with(['invoice', 'barang.jenisBarang', 'user'])
            ->whereBetween('tanggal_return', [$startDate, $endDate])
            ->orderBy('tanggal_return', 'desc')
            ->get();

        return Inertia::render('LaporanReturn/Print', [
            'returns' => $returns,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $returns = ReturnBarang::with(['invoice', 'barang.jenisBarang', 'user'])
            ->whereBetween('tanggal_return', [$startDate, $endDate])
            ->orderBy('tanggal_return', 'desc')
            ->get();

        $data = $returns->map(function ($return) {
            return [
                'Tanggal Return' => Carbon::parse($return->tanggal_return)->format('d/m/Y'),
                'No. Invoice' => $return->invoice->nomor_invoice ?? '-',
                'Kode Barang' => $return->barang->id_barang ?? '-',
                'Nama Barang' => $return->barang->nama_barang ?? '-',
                'Jenis Barang' => $return->barang->jenisBarang->nama_jenis ?? '-',
                'Jumlah' => $return->jumlah,
                'Alasan' => $return->alasan ?? '-',
                'User' => $return->user->name ?? '-',
            ];
        });

        return response()->json(['data' => $data]);
    }
}