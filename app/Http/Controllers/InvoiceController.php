<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Barang;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'details.barang'])->latest();
        
        // Filter berdasarkan tipe invoice
        if ($request->filled('tipe_invoice')) {
            $query->where('tipe_invoice', $request->tipe_invoice);
        }
        
        $invoices = $query->paginate(10);
        
        return Inertia::render('Invoice/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['tipe_invoice']),
        ]);
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $barangList = Barang::select('id', 'id_barang', 'nama_barang', 'harga_jual')->get();
        
        return Inertia::render('Invoice/Create', [
            'barangList' => $barangList,
            'nextMJUNumber' => $this->getNextInvoiceNumber('MJU'),
            'nextBIPNumber' => $this->getNextInvoiceNumber('BIP'),
        ]);
    }

    /**
     * Store a newly created invoice in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_invoice' => 'required|in:MJU,BIP',
            'nama_client' => 'required|string|max:255',
            'nomor_client' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'alamat_client' => 'required|string',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'ppn' => 'nullable|boolean',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.qty' => 'required|numeric|min:1|integer',
            'details.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $nomorInvoice = $this->generateInvoiceNumber(
                $validated['tipe_invoice'], 
                $validated['tanggal']
            );

            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'tipe_invoice' => $validated['tipe_invoice'],
                'nomor_invoice' => $nomorInvoice,
                'nama_client' => $validated['nama_client'],
                'nomor_client' => $validated['nomor_client'],
                'tanggal' => $validated['tanggal'],
                'alamat_client' => $validated['alamat_client'],
                'diskon' => $validated['diskon'] ?? 0,
                'ppn' => $validated['ppn'] ?? false,
            ]);

            foreach ($validated['details'] as $detail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'barang_id' => $detail['barang_id'],
                    'qty' => $detail['qty'],
                    'harga' => $detail['harga'],
                ]);
            }

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('success', 'Invoice ' . $nomorInvoice . ' berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice)
    {
        $barangList = Barang::select('id', 'id_barang', 'nama_barang', 'harga_jual')->get();
        
        return Inertia::render('Invoice/Edit', [
            'invoice' => $invoice->load('details.barang'),
            'barangList' => $barangList,
        ]);
    }

    /**
     * Update the specified invoice in storage
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'nama_client' => 'required|string|max:255',
            'nomor_client' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'alamat_client' => 'required|string',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'ppn' => 'nullable|boolean',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.qty' => 'required|numeric|min:1|integer',
            'details.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice->update([
                'nama_client' => $validated['nama_client'],
                'nomor_client' => $validated['nomor_client'],
                'tanggal' => $validated['tanggal'],
                'alamat_client' => $validated['alamat_client'],
                'diskon' => $validated['diskon'] ?? 0,
                'ppn' => $validated['ppn'] ?? false,
            ]);

            $invoice->details()->delete();

            foreach ($validated['details'] as $detail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'barang_id' => $detail['barang_id'],
                    'qty' => $detail['qty'],
                    'harga' => $detail['harga'],
                ]);
            }

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('success', 'Invoice berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified invoice from storage
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $nomorInvoice = $invoice->nomor_invoice;
            $invoice->delete();

            return redirect()->route('invoice.index')
                ->with('success', 'Invoice ' . $nomorInvoice . ' berhasil dihapus');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the print preview page for invoices
     */
    public function print(Request $request)
    {
        $query = Invoice::with(['user', 'details.barang'])->latest();
        
        if ($request->filled('tipe_invoice')) {
            $query->where('tipe_invoice', $request->tipe_invoice);
        }
        
        $invoices = $query->get();
        
        return Inertia::render('Invoice/Print', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Generate nomor invoice dengan format: TIPE/NOMOR.DD.MM.YYYY
     */
    private function generateInvoiceNumber($tipe, $tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = Carbon::now();
        } else {
            $tanggal = Carbon::parse($tanggal);
        }

        $lastInvoice = Invoice::where('tipe_invoice', $tipe)
            ->latest('id')
            ->first();
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->nomor_invoice);
            $nomorPart = explode('.', $parts[1]);
            $nextNumber = intval($nomorPart[0]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        $formattedDate = $tanggal->format('d.m.Y');
        
        return "{$tipe}/{$nextNumber}.{$formattedDate}";
    }

    /**
     * Get next invoice number untuk ditampilkan di form
     */
    private function getNextInvoiceNumber($tipe)
    {
        $lastInvoice = Invoice::where('tipe_invoice', $tipe)
            ->latest('id')
            ->first();
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->nomor_invoice);
            $nomorPart = explode('.', $parts[1]);
            return intval($nomorPart[0]) + 1;
        }
        
        return 1;
    }
}