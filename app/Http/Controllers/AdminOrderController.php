<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['products', 'user'])->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('id_order', 'like', "%{$search}%") // Disesuaikan
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->query('status')) {
            $query->where('payment_status', $status);
        }

        $orders = $query->paginate(10)->appends($request->query());

        $statusCounts = Order::selectRaw('payment_status, COUNT(*) as aggregate')->groupBy('payment_status')->pluck('aggregate', 'payment_status');
        $allOrdersCount = $statusCounts->sum();
        return view('admin.orders.index', compact('orders', 'statusCounts', 'allOrdersCount'));
    }

    public function create()
    {
        $products = Product::select('id_product as id', 'name', 'price', 'stock')->get();
        $users = User::select('id_user as id', 'name')->get();
        return view('admin.orders.create', compact('products', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'customer_name' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
            'address' => 'required|string|max:500',
            'payment_method' => 'required|string|in:cash,bank_transfer,credit_card,e_wallet,cod',
            'order_date' => 'required|date',
            'payment_status' => 'required|in:pending,approved,rejected,completed',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|distinct|exists:products,id_product',
            'products.*.quantity' => 'required|integer|min:1',
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, tanda-tanda plus, minus, atau spasi.',
            'products.*.quantity.min' => 'Kuantitas produk harus minimal 1.',
        ]);

        $productsInput = $request->input('products', []);
        foreach ($productsInput as $index => $productData) {
            $product = Product::findOrFail($productData['id']);
            if ($productData['quantity'] > $product->stock) {
                return back()->withErrors([
                    "products.$index.quantity" => "Stok tidak cukup untuk {$product->name}. Tersedia: {$product->stock}",
                ])->withInput();
            }
        }

        return DB::transaction(function () use ($request, $validated, $productsInput) {
            $orderData = Arr::except($validated, ['products', 'payment_proof']);
            $orderData['total_price'] = collect($productsInput)->sum(function ($item) {
                return Product::findOrFail($item['id'])->price * $item['quantity'];
            });

            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('proofs', $filename, 'public');
                $orderData['payment_proof'] = $filename;
            }

            $order = Order::create($orderData);

            $productDataSync = [];
            foreach ($productsInput as $productDataInput) {
                $product = Product::findOrFail($productDataInput['id']);
                $quantity = $productDataInput['quantity'];
                $productDataSync[$product->id_product] = [ 
                    'quantity' => $quantity,
                    'price_at_order' => $product->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $product->decrement('stock', $quantity);
            }

            $order->products()->sync($productDataSync);

            return redirect()->route('admin.orders.index')
                ->with('success', 'Pesanan berhasil ditambahkan.');
        });
    }

    public function edit(Order $order)
    {
        $products = Product::select('id_product as id', 'name', 'price', 'stock')->get();
        $users = User::select('id_user as id', 'name')->get();
        return view('admin.orders.edit', compact('order', 'products', 'users'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'customer_name' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
            'address' => 'required|string|max:500',
            'payment_method' => 'required|string|in:cash,bank_transfer,credit_card,e_wallet,cod',
            'order_date' => 'required|date',
            'payment_status' => 'required|in:pending,approved,rejected,completed',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:0',
            'new_product_id' => 'nullable|exists:products,id_product', // Disesuaikan
            'new_quantity' => 'nullable|integer|min:1',
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, tanda plus, minus, atau spasi.',
            'quantities.*.min' => 'Kuantitas tidak boleh negatif.',
            'new_quantity.min' => 'Kuantitas produk baru harus minimal 1.',
        ]);

        return DB::transaction(function () use ($request, $order, $validated) {
            $orderData = Arr::except($validated, [
                'quantities', 'payment_proof', 'new_product_id', 'new_quantity'
            ]);

            if ($request->hasFile('payment_proof')) {
                if ($order->payment_proof && Storage::disk('public')->exists('proofs/' . $order->payment_proof)) {
                    Storage::disk('public')->delete('proofs/' . $order->payment_proof);
                }
                $file = $request->file('payment_proof');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('proofs', $filename, 'public');
                $orderData['payment_proof'] = $filename;
            }

            $order->update($orderData);

            $quantities = $request->input('quantities', []);
            $currentProducts = $order->products->pluck('pivot.quantity', 'id_product')->toArray(); // Disesuaikan

            $allQuantities = $quantities;
            if ($request->filled('new_product_id')) {
                $allQuantities[$request->input('new_product_id')] = $request->input('new_quantity');
            }
            if (empty(array_filter($allQuantities))) {
                 throw ValidationException::withMessages(['quantities' => 'Pesanan harus memiliki setidaknya satu produk.']);
            }

            foreach ($quantities as $productId => $newQuantity) {
                $productId = (int) $productId;
                if (!array_key_exists($productId, $currentProducts)) continue;

                $product = Product::findOrFail($productId);
                $currentQuantity = $currentProducts[$productId];
                $availableStock = $product->stock + $currentQuantity;

                if ($newQuantity > $availableStock) {
                    throw ValidationException::withMessages(["quantities.$productId" => "Stok tidak cukup. Tersedia: $availableStock"]);
                }

                if ($newQuantity > 0) {
                    $order->products()->updateExistingPivot($productId, ['quantity' => $newQuantity]);
                    $product->stock = $availableStock - $newQuantity;
                    $product->save();
                } else {
                    $order->products()->detach($productId);
                    $product->increment('stock', $currentQuantity);
                }
            }

            if ($request->input('action') === 'add_product' && $request->filled('new_product_id') && $request->filled('new_quantity')) {
                $productId = $request->input('new_product_id');
                $quantity = $request->input('new_quantity');

                if ($order->products()->where('product_id', $productId)->exists()) {
                    throw ValidationException::withMessages(['new_product_id' => 'Produk sudah ada dalam pesanan.']);
                }

                $product = Product::findOrFail($productId);

                if ($quantity <= $product->stock) {
                    $order->products()->attach($productId, ['quantity' => $quantity, 'price_at_order' => $product->price]);
                    $product->decrement('stock', $quantity);
                } else {
                    throw ValidationException::withMessages(['new_quantity' => "Stok tidak cukup. Tersedia: {$product->stock}"]);
                }
            }

            $order->load('products');
            $order->update(['total_price' => $order->products->sum(fn ($product) => $product->pivot->quantity * $product->pivot->price_at_order)]);
            return redirect()->route('admin.orders.index')->with('success', 'Pesanan berhasil diperbarui.');
        });
    }

    public function destroy(Order $order)
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->products as $product) {
                $product->increment('stock', $product->pivot->quantity);
            }

            if ($order->payment_proof) {
                Storage::disk('public')->delete('proofs/' . $order->payment_proof);
            }

            $order->products()->detach();
            $order->delete();

            return redirect()->route('admin.orders.index')->with('success', 'Pesanan berhasil dihapus.');
        });
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['payment_status' => 'required|in:pending,approved,rejected,completed']);
        $newStatus = $request->input('payment_status');
        

        if ($newStatus === 'rejected' && $order->payment_proof) {
            Storage::disk('public')->delete('proofs/' . $order->payment_proof);
            $order->payment_proof = null;
        }

        $order->payment_status = $newStatus;
        $order->save();

        return redirect()->route('admin.orders.index')->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function removeProduct(Order $order, Product $product)
    {
        return DB::transaction(function () use ($order, $product) {
          
            $orderProduct = $order->products()->where('product_id', $product->id_product)->first();
            
            if ($orderProduct) {
                $quantity = $orderProduct->pivot->quantity;
                $order->products()->detach($product->id_product);
                $product->increment('stock', $quantity);
                $order->load('products');
                $order->update(['total_price' => $order->products->sum(fn ($item) => $item->pivot->quantity * $item->pivot->price_at_order)]);
                
                return redirect()->back()->with('success', "Produk {$product->name} berhasil dihapus dari pesanan.");
            }
            
            return redirect()->back()->with('error', "Produk {$product->name} tidak ditemukan dalam pesanan.");
        });
    }

    public function show(Order $order)
    {
        $order->load(['products', 'user']);
        return view('admin.orders.show', ['orderInstance' => $order]);
    }
}
