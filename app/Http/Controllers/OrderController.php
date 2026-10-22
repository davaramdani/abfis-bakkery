<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function history()
    {
        // Kode ini sudah benar dan tidak perlu diubah karena menggunakan relasi Eloquent
        $orders = auth()->user()->orders()->with('products')->get();
        return view('user.orders.history', compact('orders'));
    }

    public function status()
    {
        // Kode ini juga sudah benar dan tidak perlu diubah
        $pendingOrders = auth()->user()->orders()
            ->where('payment_status', 'pending')
            ->with('products')
            ->get();
        return view('user.orders.status', compact('pendingOrders'));
    }

    public function uploadProof(Request $request, $id)
    {
        Log::info('=== UPLOAD PROOF START ===');
        Log::info('Order ID received: ' . $id);
        Log::info('User ID: ' . auth()->id());
        Log::info('Request method: ' . $request->method());
        Log::info('Has file: ' . ($request->hasFile('payment_proof') ? 'YES' : 'NO'));

        try {
            $validated = $request->validate([
                'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);
            Log::info('Validation passed');
        } catch (\Exception $e) {
            Log::error('Validation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Validasi gagal: ' . $e->getMessage());
        }

        try {
            // PENYESUAIAN: Mencari order berdasarkan primary key 'id_order'
            $order = Order::findOrFail($id); 
            Log::info('Order found - ID: ' . $order->id_order . ', Current status: ' . $order->payment_status);
        } catch (\Exception $e) {
            Log::error('Order not found: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }
        
        // PENYESUAIAN: Cek otorisasi menggunakan kolom 'id_user'
        if ($order->id_user !== auth()->id()) {
            Log::warning('Unauthorized access - Order id_user: ' . $order->id_user . ', Auth user_id: ' . auth()->id());
            return redirect()->back()->with('error', 'Aksi tidak diizinkan.');
        }

        Log::info('Authorization passed');

        try {
            DB::beginTransaction();

            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                Log::info('File info: ' . $file->getClientOriginalName() . ', Size: ' . $file->getSize());

                if ($order->payment_proof) {
                    $oldPath = 'public/proofs/' . $order->payment_proof;
                    if (Storage::exists($oldPath)) {
                        Storage::delete($oldPath);
                        Log::info('Old file deleted: ' . $oldPath);
                    }
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                Log::info('New filename: ' . $filename);

                if (!Storage::exists('public/proofs')) {
                    Storage::makeDirectory('public/proofs');
                    Log::info('Directory created: public/proofs');
                }

                $path = $file->storeAs('public/proofs', $filename);
                if (!$path) {
                    throw new \Exception('Failed to store file');
                }
                Log::info('File stored at: ' . $path);
                
                // PENYESUAIAN: Update menggunakan primary key 'id_order'
                $affected = DB::table('orders')
                    ->where('id_order', $id) 
                    ->update([
                        'payment_proof' => $filename,
                        'payment_status' => 'pending',
                        'updated_at' => now()
                    ]);

                Log::info('DB update affected rows: ' . $affected);

                $updatedOrder = DB::table('orders')->where('id_order', $id)->first(); // PENYESUAIAN
                Log::info('After update - Status: ' . $updatedOrder->payment_status . ', Proof: ' . $updatedOrder->payment_proof);

                DB::commit();
                Log::info('Transaction committed');
                Log::info('=== UPLOAD PROOF SUCCESS ===');

                return redirect()->route('orders.status')->with('success', 'Bukti pembayaran berhasil diunggah! Menunggu persetujuan admin.');
            } else {
                Log::error('No file in request');
                DB::rollback();
                return redirect()->back()->with('error', 'File tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Upload failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::info('=== UPLOAD PROOF FAILED ===');
            return redirect()->back()->with('error', 'Gagal mengunggah bukti pembayaran: ' . $e->getMessage());
        }
    }

    public function debugOrder($id)
    {
        $order = Order::find($id); 
        if (!$order) {
            return response()->json(['error' => 'Order not found']);
        }

        return response()->json([
            'order_id' => $order->id_order,      
            'payment_status' => $order->payment_status,
            'payment_proof' => $order->payment_proof,
            'user_id' => $order->id_user,         
            'updated_at' => $order->updated_at,
            'raw_query' => DB::table('orders')->where('id_order', $id)->first() 
        ]);
    }
}