<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $paymentMethods = PaymentMethod::when($search, function ($query, $search) {
                return $query->where('nama_metode', 'like', "%{$search}%")
                           ->orWhere('atas_nama', 'like', "%{$search}%")
                           ->orWhere('nomor_rekening', 'like', "%{$search}%")
                           ->orWhere('keterangan', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('PaymentMethod/Index', [
            'paymentMethods' => $paymentMethods,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_metode' => 'required|string|max:255',
            'atas_nama' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        PaymentMethod::create($validated);

        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil ditambahkan');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'nama_metode' => 'required|string|max:255',
            'atas_nama' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $paymentMethod->update($validated);

        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil diperbarui');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->invoices()->count() > 0) {
            return back()->with('error', 'Metode pembayaran tidak dapat dihapus karena masih digunakan dalam invoice');
        }

        $paymentMethod->delete();

        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil dihapus');
    }
}