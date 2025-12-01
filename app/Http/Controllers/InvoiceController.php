<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\PaymentMethod;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        
        $query = Invoice::with(['user', 'details.barang', 'paymentMethod', 'returns'])->latest();
        
        if ($request->filled('tipe_invoice')) {
            $query->where('tipe_invoice', $request->tipe_invoice);
        }
        
        $invoices = $query->paginate($perPage)->withQueryString();
        
        $barangList = Barang::select('id', 'id_barang', 'nama_barang', 'harga_jual', 'stok')
            ->where('stok', '>', 0)
            ->orderBy('nama_barang', 'asc')
            ->get();
        
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('nama_metode', 'asc')
            ->get();
        
        return Inertia::render('Invoice/Index', [
            'invoices' => $invoices,
            'filters' => [
                'tipe_invoice' => $request->tipe_invoice,
                'per_page' => $perPage,
            ],
            'barangList' => $barangList,
            'paymentMethods' => $paymentMethods,
            'nextMJUNumber' => $this->getNextInvoiceNumber('MJU'),
            'nextBIPNumber' => $this->getNextInvoiceNumber('BIP'),
        ]);
    }

    public function create()
    {
        $barangList = Barang::select('id', 'id_barang', 'nama_barang', 'harga_jual', 'stok')
            ->where('stok', '>', 0)
            ->orderBy('nama_barang', 'asc')
            ->get();
        
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('nama_metode', 'asc')
            ->get();
        
        return Inertia::render('Invoice/Create', [
            'barangList' => $barangList,
            'paymentMethods' => $paymentMethods,
            'nextMJUNumber' => $this->getNextInvoiceNumber('MJU'),
            'nextBIPNumber' => $this->getNextInvoiceNumber('BIP'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_invoice' => 'required|in:MJU,BIP',
            'nama_client' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'alamat_client' => 'required|string',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'ppn' => 'nullable|boolean',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.qty' => 'required|numeric|min:1|integer',
            'details.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['details'] as $detail) {
                $barang = Barang::findOrFail($detail['barang_id']);
                if ($barang->stok < $detail['qty']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok}, diminta: {$detail['qty']}");
                }
            }

            $nomorInvoice = $this->generateInvoiceNumber(
                $validated['tipe_invoice'], 
                $validated['tanggal']
            );

            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'tipe_invoice' => $validated['tipe_invoice'],
                'nomor_invoice' => $nomorInvoice,
                'nama_client' => $validated['nama_client'],
                'tanggal' => $validated['tanggal'],
                'alamat_client' => $validated['alamat_client'],
                'diskon' => $validated['diskon'] ?? 0,
                'ppn' => $validated['ppn'] ?? false,
                'payment_method_id' => $validated['payment_method_id'],
            ]);

            foreach ($validated['details'] as $detail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'barang_id' => $detail['barang_id'],
                    'qty' => $detail['qty'],
                    'harga' => $detail['harga'],
                ]);

                $barang = Barang::findOrFail($detail['barang_id']);
                $barang->decrement('stok', $detail['qty']);

                $kodeTransaksi = BarangKeluar::generateKodeTransaksi();
                BarangKeluar::create([
                    'kode_transaksi' => $kodeTransaksi,
                    'barang_id' => $detail['barang_id'],
                    'tanggal' => $validated['tanggal'],
                    'jumlah' => $detail['qty'],
                    'harga_jual' => $detail['harga'],
                    'ppn' => $validated['ppn'] ? ($detail['harga'] * $detail['qty'] * 0.11) : 0,
                    'total' => $detail['harga'] * $detail['qty'],
                    'is_ppn' => $validated['ppn'] ?? false,
                    'keterangan' => "Penjualan Invoice: {$nomorInvoice}",
                    'user_id' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('success', 'Invoice ' . $nomorInvoice . ' berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Invoice $invoice)
    {
        $barangList = Barang::select('id', 'id_barang', 'nama_barang', 'harga_jual', 'stok')
            ->orderBy('nama_barang', 'asc')
            ->get();
        
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('nama_metode', 'asc')
            ->get();
        
        return Inertia::render('Invoice/Edit', [
            'invoice' => $invoice->load('details.barang', 'paymentMethod'),
            'barangList' => $barangList,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'nama_client' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'alamat_client' => 'required|string',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'ppn' => 'nullable|boolean',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.qty' => 'required|numeric|min:1|integer',
            'details.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($invoice->details as $oldDetail) {
                $barang = Barang::findOrFail($oldDetail->barang_id);
                $barang->increment('stok', $oldDetail->qty);
            }

            BarangKeluar::where('keterangan', 'like', "%Invoice: {$invoice->nomor_invoice}%")->delete();

            foreach ($validated['details'] as $detail) {
                $barang = Barang::findOrFail($detail['barang_id']);
                if ($barang->stok < $detail['qty']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok}, diminta: {$detail['qty']}");
                }
            }

            $invoice->update([
                'nama_client' => $validated['nama_client'],
                'tanggal' => $validated['tanggal'],
                'alamat_client' => $validated['alamat_client'],
                'diskon' => $validated['diskon'] ?? 0,
                'ppn' => $validated['ppn'] ?? false,
                'payment_method_id' => $validated['payment_method_id'],
            ]);

            $invoice->details()->delete();

            foreach ($validated['details'] as $detail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'barang_id' => $detail['barang_id'],
                    'qty' => $detail['qty'],
                    'harga' => $detail['harga'],
                ]);

                $barang = Barang::findOrFail($detail['barang_id']);
                $barang->decrement('stok', $detail['qty']);

                $kodeTransaksi = BarangKeluar::generateKodeTransaksi();
                BarangKeluar::create([
                    'kode_transaksi' => $kodeTransaksi,
                    'barang_id' => $detail['barang_id'],
                    'tanggal' => $validated['tanggal'],
                    'jumlah' => $detail['qty'],
                    'harga_jual' => $detail['harga'],
                    'ppn' => $validated['ppn'] ? ($detail['harga'] * $detail['qty'] * 0.11) : 0,
                    'total' => $detail['harga'] * $detail['qty'],
                    'is_ppn' => $validated['ppn'] ?? false,
                    'keterangan' => "Penjualan Invoice: {$invoice->nomor_invoice}",
                    'user_id' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('success', 'Invoice berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            DB::beginTransaction();
            
            foreach ($invoice->details as $detail) {
                $barang = Barang::findOrFail($detail->barang_id);
                $barang->increment('stok', $detail->qty);
            }

            BarangKeluar::where('keterangan', 'like', "%Invoice: {$invoice->nomor_invoice}%")->delete();
            
            $nomorInvoice = $invoice->nomor_invoice;
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('success', 'Invoice ' . $nomorInvoice . ' berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function print(Request $request)
    {
        $query = Invoice::with(['user', 'details.barang', 'paymentMethod'])->latest();
        
        if ($request->filled('tipe_invoice')) {
            $query->where('tipe_invoice', $request->tipe_invoice);
        }
        
        $invoices = $query->get();
        
        return Inertia::render('Invoice/Print', [
            'invoices' => $invoices,
        ]);
    }

    public function printSingle($id)
    {
        // PENTING: Eager load payment_method dengan nama yang benar
        $invoice = Invoice::with([
            'user', 
            'details.barang', 
            'paymentMethod'  // Pastikan nama relasi sesuai dengan yang di Model
        ])->findOrFail($id);
        
        // Convert ke array untuk memastikan relasi ter-load
        $invoiceData = $invoice->toArray();
        
        return Inertia::render('Invoice/PrintSingle', [
            'invoice' => $invoiceData,
        ]);
    }

    public function printSingleA5($id)
    {
        // PENTING: Eager load payment_method dengan nama yang benar
        $invoice = Invoice::with([
            'user', 
            'details.barang', 
            'paymentMethod'  // Pastikan nama relasi sesuai dengan yang di Model
        ])->findOrFail($id);
        
        // Convert ke array untuk memastikan relasi ter-load
        $invoiceData = $invoice->toArray();
        
        return Inertia::render('Invoice/PrintSingleA5', [
            'invoice' => $invoiceData,
        ]);
    }

    private function generateInvoiceNumber($tipe, $tanggal = null)
    {
        $tanggal = $tanggal ? Carbon::parse($tanggal) : Carbon::now();

        $lastInvoice = Invoice::where('tipe_invoice', $tipe)
            ->latest('id')
            ->first();
        
        $nextNumber = 1;
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->nomor_invoice);
            if (isset($parts[1])) {
                $nomorPart = explode('.', $parts[1]);
                $nextNumber = intval($nomorPart[0]) + 1;
            }
        }
        
        $formattedDate = $tanggal->format('d.m.Y');
        
        return "{$tipe}/{$nextNumber}.{$formattedDate}";
    }

    private function getNextInvoiceNumber($tipe)
    {
        $lastInvoice = Invoice::where('tipe_invoice', $tipe)
            ->latest('id')
            ->first();
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->nomor_invoice);
            if (isset($parts[1])) {
                $nomorPart = explode('.', $parts[1]);
                return intval($nomorPart[0]) + 1;
            }
        }
        
        return 1;
    }
}